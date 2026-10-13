<?php

declare(strict_types=1);

/** Integración HTTP con SQLite temporal: no lee .env ni modifica una base real. */
use JuegosTenerife\Core\Database;
use JuegosTenerife\Services\MediaUploadService;

require dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__);
$work = sys_get_temp_dir() . '/modality-editor-' . bin2hex(random_bytes(6));
mkdir($work);
$pdo = new PDO('sqlite:' . $work . '/test.sqlite', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('CREATE TABLE modalities (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, slug TEXT UNIQUE, short_description TEXT, full_description TEXT, extra_info TEXT, icon_path TEXT, is_featured INTEGER, sort_order INTEGER)');
// La misma migración que se entregará para MySQL, adaptando únicamente AFTER.
$migration = file_get_contents($root . '/database/009_add_modality_page_content.sql');
$migration = preg_replace('/--[^\n]*\n|SET NAMES utf8mb4;/u', '', $migration);
foreach (['page_lead', 'page_sections'] as $column) {
    preg_match('/ADD COLUMN ' . $column . ' (\w+) NULL/', $migration, $match);
    $pdo->exec('ALTER TABLE modalities ADD COLUMN ' . $column . ' ' . $match[1] . ' NULL');
}
$pdo->exec('CREATE TABLE media_files (id INTEGER PRIMARY KEY, owner_type TEXT, owner_id INTEGER, media_type TEXT, original_name TEXT, file_path TEXT, mime_type TEXT, file_size INTEGER, alt_text TEXT, sort_order INTEGER)');
$pdo->exec('CREATE TABLE audit_logs (user_id INTEGER, action TEXT, subject_type TEXT, subject_id INTEGER, metadata TEXT, ip_address TEXT, user_agent TEXT)');
$id = random_int(10000000, 99999999);
$pdo->prepare('INSERT INTO modalities (id, name, slug, short_description, icon_path) VALUES (?, ?, ?, ?, ?)')
    ->execute([$id, 'Lucha Canaria', 'lucha-canaria', 'Resumen del censo', '/assets/images/pictogramas/LUCHA_CANARIA_2.png']);

$router = <<<'ROUTER'
<?php
require __ROOT__ . '/vendor/autoload.php';
$pdo = new PDO('sqlite:' . __DB__, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
(new ReflectionProperty(JuegosTenerife\Core\Database::class, 'connection'))->setValue(null, $pdo);
JuegosTenerife\Core\Session::put('user', ['id' => 1, 'full_name' => 'Prueba']);
JuegosTenerife\Core\Session::put('_csrf', 'test-token');
$router = new JuegosTenerife\Core\Router();
require __ROOT__ . '/routes/web.php';
$response = $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
http_response_code($response->statusCode);
foreach ($response->headers as $key => $value) header($key . ': ' . $value);
echo $response->body;
ROUTER;
file_put_contents($work . '/router.php', str_replace(['__ROOT__', '__DB__'], [var_export($root, true), var_export($work . '/test.sqlite', true)], $router));
$socket = stream_socket_server('tcp://127.0.0.1:0');
$address = stream_socket_get_name($socket, false);
fclose($socket);
$server = proc_open([PHP_BINARY, '-S', $address, $work . '/router.php'], [0 => ['pipe', 'r'], 1 => ['file', $work . '/server.log', 'a'], 2 => ['file', $work . '/server.log', 'a']], $pipes, $root);
$checks = 0;

function check(string $description, bool $condition): void
{
    global $checks;
    if (!$condition) throw new RuntimeException($description);
    $checks++;
    echo "OK $description\n";
}
function request(string $path, ?array $data = null): array
{
    global $address;
    $curl = curl_init('http://' . $address . $path);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10]);
    if ($data !== null) curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $body = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);
    return [$status, (string) $body];
}
function row(): array
{
    global $pdo, $id;
    return $pdo->query('SELECT * FROM modalities WHERE id = ' . $id)->fetch(PDO::FETCH_ASSOC);
}
function uploadFiles(): array
{
    global $root, $id;
    return glob($root . '/public/uploads/modalities/' . $id . '/icons/*') ?: [];
}

try {
    for ($attempt = 0; $attempt < 50; $attempt++) {
        $ready = @stream_socket_client('tcp://' . $address, $errno, $error, 0.1);
        if ($ready) { fclose($ready); break; }
        usleep(100000);
    }
    [$status, $page] = request('/modalidades/lucha-canaria');
    check('La ficha original se conserva antes de editar', $status === 200 && str_contains($page, 'Origen y evolución') && str_contains($page, '<strong>Lucha corrida:</strong>'));
    [$status, $form] = request('/admin/modalities/' . $id . '/edit');
    check('El editor carga la introducción y los apartados publicados', $status === 200 && str_contains($form, 'Definida como el deporte vernáculo') && str_contains($form, 'Origen y evolución') && str_contains($form, '**Lucha corrida:**'));
    check('La subida del icono es independiente de la foto', str_contains($form, 'name="icon" type="file"') && str_contains($form, 'name="main_image" type="file"'));
    $data = [
        '_csrf' => 'test-token', 'name' => 'Lucha Canaria', 'slug' => 'lucha-canaria',
        'short_description' => 'Resumen del censo', 'page_content_present' => '1',
        'page_lead' => 'Introducción actualizada',
        'page_sections[0][title]' => 'Historia actualizada',
        'page_sections[0][paragraphs]' => "Primer párrafo\n\nSegundo párrafo <script>alert(1)</script>",
        'page_sections[0][items]' => "**Técnica:** explicación\n<img src=x onerror=alert(1)>",
    ];
    [$status] = request('/admin/modalities/' . $id, $data);
    check('Guarda la ficha y mantiene el icono si no se sube uno', $status === 302 && row()['icon_path'] === '/assets/images/pictogramas/LUCHA_CANARIA_2.png');
    [$status, $page] = request('/modalidades/lucha-canaria');
    check('Publica la introducción y los nuevos apartados', $status === 200 && str_contains($page, 'Introducción actualizada') && str_contains($page, 'Historia actualizada') && !str_contains($page, 'Origen y evolución'));
    check('Escapa HTML y conserva la negrita de las listas', !str_contains($page, '<script>') && !str_contains($page, '<img src=x') && str_contains($page, '&lt;script&gt;') && str_contains($page, '<strong>Técnica:</strong>'));
    [$status, $form] = request('/admin/modalities/' . $id . '/edit');
    check('Al reabrir el editor aparecen los cambios', $status === 200 && str_contains($form, 'Historia actualizada') && str_contains($form, "Primer párrafo\n\nSegundo párrafo"));

    $png = $work . '/icon.png';
    $image = new Imagick();
    $image->newImage(32, 32, new ImagickPixel('transparent'), 'png');
    $image->writeImage($png);
    $image->clear();
    [$status] = request('/admin/modalities/' . $id, $data + ['icon' => new CURLFile($png, 'image/png', 'icon.png')]);
    $firstIcon = row()['icon_path'];
    check('Sube y registra el icono', $status === 302 && str_starts_with($firstIcon, '/uploads/modalities/' . $id . '/icons/') && is_file($root . '/public' . $firstIcon));
    $uploaded = new Imagick($root . '/public' . $firstIcon);
    check('Conserva la transparencia del icono y no genera miniatura', $uploaded->getImageAlphaChannel() && !is_file($root . '/public' . MediaUploadService::thumbnailPath($firstIcon)));
    $uploaded->clear();
    request('/admin/modalities/' . $id, $data + ['icon' => new CURLFile($png, 'image/png', 'icon.png'), 'main_image' => new CURLFile($png, 'image/png', 'foto.png'), 'main_image_alt' => 'Foto nueva']);
    $secondIcon = row()['icon_path'];
    check('Sustituye el icono y elimina el anterior', $secondIcon !== $firstIcon && !is_file($root . '/public' . $firstIcon) && count(uploadFiles()) === 1);
    [$status, $page] = request('/modalidades/lucha-canaria');
    check('Sigue publicando la foto principal y su texto alternativo', $status === 200 && str_contains($page, 'alt="Foto nueva"') && str_contains($page, '/uploads/modalities/' . $id . '/'));

    file_put_contents($work . '/invalid.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
    $bad = array_replace($data, ['page_lead' => 'No debe guardarse', 'icon' => new CURLFile($png, 'image/png', 'icon.png'), 'main_image' => new CURLFile($work . '/invalid.svg', 'image/svg+xml', 'invalid.svg')]);
    [$status, $form] = request('/admin/modalities/' . $id, $bad);
    check('Si falla la foto, revierte los textos y el icono y limpia la subida', $status === 200 && str_contains($form, 'Formato no permitido') && row()['page_lead'] === 'Introducción actualizada' && row()['icon_path'] === $secondIcon && count(uploadFiles()) === 1);
    [$status, $form] = request('/admin/modalities/' . $id, array_replace($data, ['icon' => new CURLFile($work . '/invalid.svg', 'image/svg+xml', 'invalid.svg')]));
    check('Rechaza un icono SVG con contenido activo', $status === 200 && str_contains($form, 'Formato no permitido') && row()['icon_path'] === $secondIcon);
    request('/admin/modalities/' . $id, array_replace($data, ['_csrf' => 'incorrecto', 'page_lead' => 'No autorizado']));
    check('Un token CSRF incorrecto no modifica la modalidad', row()['page_lead'] === 'Introducción actualizada');
    [$status, $form] = request('/admin/modalities/' . $id, array_replace($data, ['page_sections[0][title]' => '']));
    check('Un apartado con texto requiere título', $status === 200 && str_contains($form, 'Cada apartado') && str_contains($form, 'Primer párrafo'));

    $empty = array_filter($data, static fn($key) => !str_starts_with($key, 'page_sections['), ARRAY_FILTER_USE_KEY);
    request('/admin/modalities/' . $id, array_replace($empty, ['page_lead' => '']));
    [$status, $page] = request('/modalidades/lucha-canaria');
    check('Permite borrar la introducción y todos los apartados', $status === 200 && row()['page_sections'] === '[]' && row()['page_lead'] === '' && !str_contains($page, 'Origen y evolución') && !str_contains($page, 'Historia actualizada'));
    [$status] = request('/admin/modalities', array_replace($empty, ['name' => 'Nueva modalidad', 'slug' => 'nueva-modalidad', 'page_lead' => 'Ficha nueva']));
    [$publicStatus, $page] = request('/modalidades/nueva-modalidad');
    check('Las modalidades nuevas también tienen ficha pública', $status === 302 && $publicStatus === 200 && str_contains($page, 'Ficha nueva') && str_contains($page, 'Tenerife Deportes'));
    [$status] = request('/modalidades/inexistente');
    check('Una modalidad inexistente devuelve 404', $status === 404);
    echo "$checks comprobaciones correctas.\n";
} finally {
    proc_terminate($server);
    proc_close($server);
    foreach ((array) glob($root . '/public/uploads/modalities/' . $id . '/icons/*') as $file) unlink($file);
    @rmdir($root . '/public/uploads/modalities/' . $id . '/icons');
    foreach ((array) glob($root . '/public/uploads/modalities/' . $id . '/*') as $file) if (is_file($file)) unlink($file);
    @rmdir($root . '/public/uploads/modalities/' . $id);
    foreach ((array) glob($work . '/*') as $file) unlink($file);
    rmdir($work);
}
