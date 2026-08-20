<?php
/**
 * The module's own welcome email — deliberately plain.
 *
 * It says the one thing that always needs saying (here is your link, it
 * expires) and nothing a particular community would want said differently.
 * A deployment with more to tell people swaps this view through
 * {@see \humhub\modules\welcomeMail\events\MailEvent} rather than editing it.
 *
 * @var \humhub\modules\user\models\User $user
 * @var string $claimUrl
 * @var int $ttlDays
 */

use yii\helpers\Html;

$name = trim((string) ($user->profile->firstname ?? '')) ?: $user->displayName;
?>
<p><?= Yii::t('WelcomeMailModule.base', 'Hello {name},', ['name' => Html::encode($name)]) ?></p>

<p>
    <?= Yii::t('WelcomeMailModule.base', 'An account has been created for you on {appName}. To start using it, choose a password.', [
        'appName' => Html::encode(Yii::$app->name),
    ]) ?>
</p>

<p style="margin:28px 0">
    <a href="<?= Html::encode($claimUrl) ?>"
       style="background:#4a8f7b;color:#fff;padding:12px 22px;border-radius:4px;text-decoration:none;display:inline-block">
        <?= Yii::t('WelcomeMailModule.base', 'Choose my password') ?>
    </a>
</p>

<p style="color:#777;font-size:13px">
    <?= Yii::t('WelcomeMailModule.base', 'This link works once and expires in {n,plural,=1{# day}other{# days}}.', ['n' => $ttlDays]) ?><br>
    <?= Yii::t('WelcomeMailModule.base', 'If the button does not work, paste this address into your browser:') ?><br>
    <span style="word-break:break-all"><?= Html::encode($claimUrl) ?></span>
</p>
