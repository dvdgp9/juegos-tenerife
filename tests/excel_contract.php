<?php

declare(strict_types=1);

use JuegosTenerife\Services\Import\ExcelPreviewService;
use OpenSpout\Reader\XLSX\Reader;

require dirname(__DIR__) . '/vendor/autoload.php';

$filePath = $argv[1] ?? getenv('EXCEL_CONTRACT_FILE') ?: '/Users/dvdgp/Downloads/Plataforma Juegos Tenerife/Listado Entidades 20260608.xlsx';

$expectedHeaders = [
    'Tipo Entidad1',
    'Tipo Entidad2',
    'Modalidad1',
    'Modalidad2',
    'Modalidad3',
    'Modalidad4',
    'Nombre Entidad',
    'Domicilio',
    'Localidad',
    'Municipio',
    'Código Postal',
    'Teléfono1',
    'Teléfono2',
    'Email1',
    'Email2',
    'Web',
    'Facebook',
    'Instagram',
    'X',
    'TikTok',
    'Youtube',
    'Persona Contacto',
    'Cargo Contacto',
    'Teléfono1',
    'Teléfono2',
    'Email',
    'Directiva',
    'Miembros Directiva',
    'Directivos/Hombres',
    'Directivas/Mujeres',
    'Asambleas Anuales',
    'Socios/as',
    'Número Total Socios/as',
    'Socios/Hombres',
    'Socias/Mujeres',
    'Equipos',
    'Equipos por Género',
    'Equipos por Edad',
    'Total Deportistas/Practicantes',
    'Mujeres/Niñas',
    'Hombres/Niños',
    'Edades: De 0 a 5 años',
    'Edades: De 6 a 11 años',
    'Edades: De 12 a 17 años',
    'Edades: De 18 a 29 años',
    'Edades: De 30 a 45 años',
    'Edades: De 46 a 59 años',
    'Edades: 60 años y más',
    'Instalaciones1',
    'Instalaciones2',
    'Instalaciones3',
    'Instalaciones4',
    'Instalaciones5',
    'Instalaciones6',
    'Instalaciones7',
    'Instalaciones8',
    'Instalaciones9',
    'Entrenamientos/Prácticas',
    'Días',
    'Horarios',
    'Breve Historia',
    'Principios Corporativos',
    'Valores Deportivos',
    'Protocolo Igualdad',
    'Protocolo Violencia',
    'LOPIVI',
    'Necesidades Educativas',
    'Discapacidad',
    'Educar Entrenando',
];

$failures = [];

$assert = static function (bool $condition, string $message) use (&$failures): void {
    if (!$condition) {
        $failures[] = $message;
    }
};

$assert(is_file($filePath), 'No se encuentra el Excel final en: ' . $filePath);

if ($failures === []) {
    $previewService = new ExcelPreviewService();
    $preview = $previewService->preview($filePath, 3);

    $assert($preview['sheet'] === 'ENTIDADES', 'La hoja esperada es ENTIDADES; detectada: ' . (string) $preview['sheet']);
    $assert($preview['records'] === 41, 'El Excel final debe tener 41 registros; detectados: ' . (string) $preview['records']);
    $assert(count($preview['headers']) === 69, 'El Excel final debe tener 69 columnas; detectadas: ' . count($preview['headers']));
    $assert($preview['headers'] === $expectedHeaders, 'Las cabeceras no coinciden con la estructura vigente de Listado Entidades 20260608.xlsx.');
    $assert($preview['missingHeaders'] === [], 'No debería haber cabeceras esperadas ausentes: ' . json_encode($preview['missingHeaders'], JSON_UNESCAPED_UNICODE));
    $assert($preview['mapsCount'] === 35, 'El Excel final debe detectar 35 celdas con enlaces Maps; detectadas: ' . (string) $preview['mapsCount']);
    $assert(($preview['duplicateHeaders']['Teléfono1'] ?? []) === [12, 24], 'Teléfono1 debe estar duplicado en columnas 12 y 24.');
    $assert(($preview['duplicateHeaders']['Teléfono2'] ?? []) === [13, 25], 'Teléfono2 debe estar duplicado en columnas 13 y 25.');

    $reader = new Reader();
    $reader->open($filePath);

    try {
        $headers = [];
        $firstDataRow = [];
        $firstSubtypeRow = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $values = $previewService->normalizeRow($row->toArray());

                if ($previewService->isEmptyRow($values)) {
                    continue;
                }

                if ($headers === []) {
                    $headers = $values;
                    continue;
                }

                if ($firstDataRow === []) {
                    $firstDataRow = $values;
                }

                if (($values[1] ?? '') !== '') {
                    $firstSubtypeRow = $values;
                    break 2;
                }
            }
        }
    } finally {
        $reader->close();
    }

    $rowMap = $previewService->mapRow($headers, $firstDataRow);
    $assert(($rowMap['Tipo Entidad'] ?? null) === 'Club', 'El alias Tipo Entidad debe mapear desde Tipo Entidad1.');
    $assert(($rowMap['Teléfono Contacto1'] ?? null) === '677 401 451', 'Teléfono Contacto1 debe mapear desde el segundo bloque de Teléfono1.');
    $assert(array_key_exists('Teléfono Contacto2', $rowMap), 'Debe existir Teléfono Contacto2 aunque esté vacío.');

    $subtypeRowMap = $previewService->mapRow($headers, $firstSubtypeRow);
    $assert(($subtypeRowMap['Tipo Entidad Complementaria'] ?? null) === 'Escuela', 'Tipo Entidad Complementaria debe mapear desde Tipo Entidad2 cuando existe subtipo.');
}

if ($failures !== []) {
    fwrite(STDERR, "Contrato Excel final: ERROR\n");
    foreach ($failures as $failure) {
        fwrite(STDERR, '- ' . $failure . "\n");
    }
    exit(1);
}

echo "Contrato Excel final: OK\n";
echo 'Archivo: ' . $filePath . "\n";
echo "Hoja: ENTIDADES\n";
echo "Registros: 41\n";
echo "Columnas: 69\n";
echo "Enlaces Maps detectados: 35\n";
