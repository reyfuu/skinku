<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Centralised audit trail writer. Every sensitive mutation should call log().
 * Never store plaintext passwords in before/after payloads.
 */
class AuditService
{
    public static function log(
        string $action,
        ?string $targetType = null,
        ?int $targetId = null,
        ?array $before = null,
        ?array $after = null,
        ?int $targetUserId = null,
        ?string $targetEmail = null,
    ): AuditLog {
        /** @var User|null $actor */
        $actor = Auth::user();
        $request = request();

        return AuditLog::create([
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'target_user_id' => $targetUserId,
            'target_email' => $targetEmail,
            'performed_by' => $actor?->id,
            'performed_by_email' => $actor?->email,
            'before_data' => self::sanitize($before),
            'after_data' => self::sanitize($after),
            'ip_address' => $request instanceof Request ? $request->ip() : null,
            'user_agent' => $request instanceof Request ? substr((string) $request->userAgent(), 0, 1000) : null,
            'created_at' => now(),
        ]);
    }

    /** Strip secrets from any audit payload. */
    private static function sanitize(?array $data): ?array
    {
        if ($data === null) {
            return null;
        }

        foreach (['password', 'password_confirmation', 'remember_token'] as $secret) {
            if (array_key_exists($secret, $data)) {
                $data[$secret] = '***';
            }
        }

        return $data;
    }
}
