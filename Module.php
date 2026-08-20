<?php

namespace humhub\modules\welcomeMail;

use humhub\modules\welcomeMail\events\MailEvent;
use humhub\modules\welcomeMail\models\WelcomeToken;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Event;
use yii\helpers\Url;

/**
 * Welcome mail with a single-use magic link.
 *
 * For communities whose accounts were created *for* people — by an import, or
 * by an administrator — rather than by the people themselves. Such an account
 * has no password its owner knows, and "reset your password" is a strange
 * first thing to ask of someone who never had one.
 *
 * The recipient lands on a guest-accessible page, sets a password, and is
 * signed in. Where the OIDC provider module is installed alongside, its
 * authorize endpoint reuses that session, so a sister application signs them
 * in too without asking again.
 */
class Module extends \humhub\components\Module
{
    /**
     * Raised to decide where someone lands after claiming their account.
     * See {@see \humhub\modules\welcomeMail\events\LandingEvent}.
     */
    public const EVENT_RESOLVE_LANDING = 'resolveLanding';

    /**
     * Raised before composing, so a deployment can supply its own views and
     * subject. See {@see \humhub\modules\welcomeMail\events\MailEvent}.
     */
    public const EVENT_COMPOSE_MAIL = 'composeMail';

    public $resourcesPath = 'resources';

    /**
     * How long a magic link stays valid, in days.
     * @var int
     */
    public int $tokenTtlDays = 7;

    /** Admin screen for this module. */
    public function getConfigUrl()
    {
        return Url::to(['/welcome-mail/admin']);
    }

    /** The configured lifetime, falling back to {@see $tokenTtlDays}. */
    public function getTokenTtl(): int
    {
        $days = (int) $this->settings->get('tokenTtlDays', 0);

        return ($days >= 1 && $days <= 90) ? $days : $this->tokenTtlDays;
    }

    /**
     * Build the absolute magic-link URL for a raw token.
     */
    public function claimUrl(string $rawToken): string
    {
        return Url::to(['/welcome-mail/claim/index', 'token' => $rawToken], true);
    }

    /**
     * Generate a fresh token for the user and send the welcome email.
     *
     * @return bool whether the email was accepted for delivery
     */
    public function sendWelcome(User $user): bool
    {
        $ttlDays = $this->getTokenTtl();
        $rawToken = WelcomeToken::issue($user, $ttlDays);

        /*
         * ── LES VUES SONT UN POINT D'EXTENSION, PAS UNE CONSTANTE ───────────
         * Le module envoie un courriel sobre et suffisant. Une installation
         * dont la bienvenue a davantage à dire échange les vues ici plutôt que
         * de forker : c'est ce qui permet à ce module d'être publiable tout en
         * portant, chez nous, un tout autre message.
         */
        $event = new MailEvent([
            'user' => $user,
            'htmlView' => '@welcome-mail/views/mails/Welcome',
            'textView' => '@welcome-mail/views/mails/plaintext/Welcome',
            'layout' => '@welcome-mail/views/mails/layout',
            'subject' => Yii::t('WelcomeMailModule.base', 'Welcome to {appName}', [
                'appName' => Yii::$app->name,
            ]),
        ]);
        Event::trigger(self::class, self::EVENT_COMPOSE_MAIL, $event);

        // Le layout se force le temps du compose puis se restaure : le corps
        // est rendu à la composition, pas à l'envoi, et le laisser en place
        // repeindrait tous les autres courriels du Hub.
        $mailer = Yii::$app->mailer;
        $previousLayout = $mailer->htmlLayout;
        $mailer->htmlLayout = $event->layout;
        try {
            $mail = $mailer->compose([
                'html' => $event->htmlView,
                'text' => $event->textView,
            ], [
                'user' => $user,
                'claimUrl' => $this->claimUrl($rawToken),
                'ttlDays' => $ttlDays,
            ]);
        } finally {
            $mailer->htmlLayout = $previousLayout;
        }
        $mail->setTo($user->email);
        $mail->setSubject($event->subject);

        return $mail->send();
    }
}
