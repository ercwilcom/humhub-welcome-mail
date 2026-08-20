<?php

namespace humhub\modules\welcomeMail\models;

use humhub\modules\user\models\User;
use Yii;
use yii\db\ActiveRecord;

/**
 * A single-use magic-link token for the welcome / set-password flow.
 *
 * The raw token is generated once, handed out in the email and never stored;
 * only its sha256 hash lives in the DB. Lookups hash the incoming value and
 * compare, so a DB leak does not expose usable links.
 *
 * @property int $id
 * @property int $user_id
 * @property string $token_hash
 * @property string $created_at
 * @property string $expires_at
 * @property string|null $used_at
 */
class WelcomeToken extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'welcome_mail_token';
    }

    /**
     * Issue a fresh token for the user and return the RAW value to embed in the link.
     * Any earlier unused tokens for this user are invalidated so only the newest works.
     */
    public static function issue(User $user, int $ttlDays): string
    {
        // Invalidate prior pending links for this user.
        static::deleteAll(['user_id' => $user->id, 'used_at' => null]);

        $raw = bin2hex(random_bytes(32));

        $token = new static();
        $token->user_id = $user->id;
        $token->token_hash = hash('sha256', $raw);
        $token->created_at = date('Y-m-d H:i:s');
        $token->expires_at = date('Y-m-d H:i:s', strtotime('+' . max(1, $ttlDays) . ' days'));
        $token->save(false);

        return $raw;
    }

    /**
     * Find a usable token (exists, unused, not expired) for a raw value, or null.
     */
    public static function findUsable(?string $raw): ?self
    {
        if (!is_string($raw) || !preg_match('/^[0-9a-f]{64}$/', $raw)) {
            return null;
        }

        $token = static::findOne(['token_hash' => hash('sha256', $raw)]);
        if ($token === null || !$token->isUsable()) {
            return null;
        }

        return $token;
    }

    public function isUsable(): bool
    {
        return $this->used_at === null && strtotime($this->expires_at) > time();
    }

    public function markUsed(): void
    {
        $this->used_at = date('Y-m-d H:i:s');
        $this->save(false);
    }
}
