<?php

declare(strict_types=1);

namespace JuegosTenerife\Controllers\Admin;

use JuegosTenerife\Core\Response;
use JuegosTenerife\Core\Session;
use JuegosTenerife\Services\AuditLogger;
use JuegosTenerife\Services\AuthService;

abstract class AdminController
{
    protected AuthService $auth;

    public function __construct()
    {
        $this->auth = new AuthService();
    }

    protected function requireAdmin(): ?Response
    {
        if ($this->auth->check()) {
            return null;
        }

        return Response::redirect('/admin/login');
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function user(): ?array
    {
        return $this->auth->user();
    }

    /**
     * Registra una acción administrativa atribuida a la usuaria en sesión.
     *
     * @param array<string, mixed> $metadata
     */
    protected function audit(string $action, string $subjectType, ?int $subjectId = null, array $metadata = []): void
    {
        $user = $this->user();

        AuditLogger::log(
            $action,
            $subjectType,
            $subjectId,
            $metadata,
            isset($user['id']) ? (int) $user['id'] : null
        );
    }

    /**
     * Detecta un envío que PHP ha descartado por superar post_max_size.
     *
     * Cuando eso ocurre, PHP vacía $_POST y $_FILES sin avisar. El síntoma visible es que
     * falla la verificación CSRF y se muestra "la sesión ha caducado", que no tiene nada que
     * ver con la causa real: normalmente son demasiadas fotos en un mismo guardado.
     */
    protected function postExceededLimit(): bool
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            return false;
        }

        $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);

        return $contentLength > 0 && $_POST === [] && $_FILES === [];
    }

    protected function uploadLimitMessage(): string
    {
        $limit = trim((string) ini_get('post_max_size'));

        return 'El envío es demasiado grande' . ($limit === '' ? '' : ' (máximo ' . $limit . ')')
            . ' y se ha descartado entero. No se ha guardado ningún cambio: vuelve a intentarlo'
            . ' subiendo menos fotos a la vez o con imágenes más ligeras.';
    }

    protected function flash(string $type, string $message): void
    {
        Session::put('flash', [
            'type' => $type,
            'message' => $message,
        ]);
    }

    /**
     * @return array{type:string,message:string}|null
     */
    protected function consumeFlash(): ?array
    {
        $flash = Session::get('flash');
        Session::forget('flash');

        if (!is_array($flash) || empty($flash['message'])) {
            return null;
        }

        return [
            'type' => (string) ($flash['type'] ?? 'info'),
            'message' => (string) $flash['message'],
        ];
    }
}
