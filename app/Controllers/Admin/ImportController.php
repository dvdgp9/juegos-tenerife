<?php

declare(strict_types=1);

namespace JuegosTenerife\Controllers\Admin;

use JuegosTenerife\Core\Csrf;
use JuegosTenerife\Core\Response;
use JuegosTenerife\Core\Session;
use JuegosTenerife\Core\View;
use JuegosTenerife\Services\AuthService;
use JuegosTenerife\Services\Import\ExcelPreviewService;
use JuegosTenerife\Services\Import\EntityImportService;
use RuntimeException;

final class ImportController
{
    public function index(): Response
    {
        if (!(new AuthService())->check()) {
            return Response::redirect('/admin/login');
        }

        return View::render('admin/import', [
            'title' => 'Importar Excel',
            'csrf' => Csrf::token(),
            'preview' => null,
            'summary' => null,
            'error' => null,
        ]);
    }

    public function preview(): Response
    {
        if (!(new AuthService())->check()) {
            return Response::redirect('/admin/login');
        }

        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            return $this->renderWithError('La sesión ha caducado. Vuelve a intentarlo.');
        }

        if (!isset($_FILES['excel']) || !is_array($_FILES['excel'])) {
            return $this->renderWithError('Selecciona un archivo Excel.');
        }

        $file = $_FILES['excel'];

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return $this->renderWithError('No se pudo subir el archivo. Código de error: ' . (string) ($file['error'] ?? 'desconocido'));
        }

        $originalName = (string) ($file['name'] ?? '');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, ['xlsx'], true)) {
            return $this->renderWithError('Por ahora solo se admiten archivos .xlsx.');
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        $storedPath = dirname(__DIR__, 3) . '/storage/imports/preview-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.xlsx';

        if (!move_uploaded_file($tmpName, $storedPath)) {
            return $this->renderWithError('No se pudo guardar el archivo subido en storage/imports.');
        }

        try {
            $preview = (new ExcelPreviewService())->preview($storedPath);
        } catch (RuntimeException $exception) {
            return $this->renderWithError($exception->getMessage());
        }

        $preview['file'] = [
            'originalName' => $originalName,
            'storedPath' => $storedPath,
        ];
        Session::put('pending_import', $preview['file']);

        return View::render('admin/import', [
            'title' => 'Importar Excel',
            'csrf' => Csrf::token(),
            'preview' => $preview,
            'summary' => null,
            'error' => null,
        ]);
    }

    public function confirm(): Response
    {
        $auth = new AuthService();

        if (!$auth->check()) {
            return Response::redirect('/admin/login');
        }

        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            return $this->renderWithError('La sesión ha caducado. Vuelve a previsualizar el archivo.');
        }

        $pendingImport = Session::get('pending_import');
        if (!is_array($pendingImport) || empty($pendingImport['storedPath']) || empty($pendingImport['originalName'])) {
            return $this->renderWithError('No hay ningún Excel pendiente de confirmar.');
        }

        $user = $auth->user();

        try {
            $summary = (new EntityImportService())->import(
                (string) $pendingImport['storedPath'],
                (string) $pendingImport['originalName'],
                isset($user['id']) ? (int) $user['id'] : null
            );
        } catch (RuntimeException $exception) {
            return $this->renderWithError($exception->getMessage());
        }

        Session::forget('pending_import');

        return View::render('admin/import', [
            'title' => 'Importar Excel',
            'csrf' => Csrf::token(),
            'preview' => null,
            'summary' => $summary,
            'error' => null,
        ]);
    }

    private function renderWithError(string $error): Response
    {
        return View::render('admin/import', [
            'title' => 'Importar Excel',
            'csrf' => Csrf::token(),
            'preview' => null,
            'summary' => null,
            'error' => $error,
        ]);
    }
}
