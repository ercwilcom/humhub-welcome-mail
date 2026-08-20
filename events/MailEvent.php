<?php

namespace humhub\modules\welcomeMail\events;

use humhub\modules\user\models\User;
use yii\base\Event;

/**
 * Raised just before the welcome email is composed.
 *
 * The module ships a plain, serviceable email: here is your link, set a
 * password. A deployment whose welcome has more to say — which of several
 * applications a person is really joining, what they will find there — swaps
 * the views here instead of forking the module.
 *
 * ```php
 * // in your module's config.php
 * ['class' => Module::class, 'event' => Module::EVENT_COMPOSE_MAIL,
 *  'callback' => [Events::class, 'onWelcomeCompose']],
 * ```
 *
 * All four fields are pre-filled with the module's own answers; change only
 * what you mean to change. View values are Yii aliases, so a handler points
 * them at its own module's `views/`.
 */
class MailEvent extends Event
{
    /** The person being welcomed. */
    public User $user;

    /** Alias of the HTML body view. */
    public string $htmlView = '';

    /** Alias of the plain-text body view. */
    public string $textView = '';

    /** Alias of the layout wrapping the HTML body. */
    public string $layout = '';

    /** The subject line, already translated. */
    public string $subject = '';
}
