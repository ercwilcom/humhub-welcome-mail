<?php

namespace humhub\modules\welcomeMail\events;

use humhub\modules\user\models\User;
use yii\base\Event;

/**
 * Raised to decide where someone lands right after setting their password.
 *
 * The module's own answer is the `landingUrl` setting, or the dashboard when
 * that is empty. A deployment that can work the destination out for itself —
 * a sister application whose address is already recorded somewhere — handles
 * this event instead of asking an administrator to keep a URL in sync.
 *
 * Leave `$url` alone to accept what came before you; handlers run in
 * registration order and the last non-null value wins.
 */
class LandingEvent extends Event
{
    /** The person who just claimed their account. */
    public ?User $user = null;

    /** Absolute URL to land on, or null to fall back to the dashboard. */
    public ?string $url = null;
}
