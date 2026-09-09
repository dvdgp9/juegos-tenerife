<?php

declare(strict_types=1);

namespace JuegosTenerife\Services;

use JuegosTenerife\Core\Database;
use PDO;
use Throwable;

/**
 * Registro de acciones administrativas en `audit_logs`.
 *
 * El registro nunca debe romper la operación que lo origina: si falla la
 * escritura se anota en el log de errores de PHP y la petición continúa.
 */
final class AuditLogger
{
    /**
     * @param array<string, mixed> $metadata
     */
    public static function log(
        string $action,
        string $subjectType,
        ?int $subjectId = null,
        array $metadata = [],
        ?int $userId = null
    ): void {
        try {
            $statement = Database::connection()->prepare(
                'INSERT INTO audit_logs (user_id, action, subject_type, subject_id, metadata, ip_address, user_agent)
                 VALUES (:user_id, :action, :subject_type, :subject_id, :metadata, :ip_address, :user_agent)'
            );

            $statement->execute([
                'user_id' => $userId,
                'action' => substr($action, 0, 80),
                'subject_type' => substr($subjectType, 0, 80),
                'subject_id' => $subjectId,
                'metadata' => $metadata === [] ? null : json_encode($metadata, JSON_UNESCAPED_UNICODE),
                'ip_address' => self::clientIp(),
                'user_agent' => self::userAgent(),
            ]);
        } catch (Throwable $throwable) {
            error_log('[audit] No se pudo registrar "' . $action . '": ' . $throwable->getMessage());
        }
    }

    /**
     * Últimas acciones registradas, para la pantalla de actividad reciente.
     *
     * @return list<array<string, mixed>>
     */
    public static function recent(int $limit = 12): array
    {
        try {
            $statement = Database::connection()->prepare(
                'SELECT a.id, a.action, a.subject_type, a.subject_id, a.metadata, a.created_at,
                        u.full_name, u.username, u.email
                 FROM audit_logs a
                 LEFT JOIN users u ON u.id = a.user_id
                 ORDER BY a.id DESC
                 LIMIT :limit'
            );
            $statement->bindValue('limit', max(1, $limit), PDO::PARAM_INT);
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $throwable) {
            error_log('[audit] No se pudo leer la actividad reciente: ' . $throwable->getMessage());

            return [];
        }
    }

    private static function clientIp(): ?string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        return is_string($ip) && $ip !== '' ? substr($ip, 0, 45) : null;
    }

    private static function userAgent(): ?string
    {
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        return is_string($agent) && $agent !== '' ? substr($agent, 0, 255) : null;
    }
}
