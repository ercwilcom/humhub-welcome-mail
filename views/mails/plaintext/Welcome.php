<?php
/**
 * @var \humhub\modules\user\models\User $user
 * @var string $claimUrl
 * @var int $ttlDays
 */

$name = trim((string) ($user->profile->firstname ?? '')) ?: $user->displayName;

echo Yii::t('WelcomeMailModule.base', 'Hello {name},', ['name' => $name]) . "\n\n";
echo Yii::t('WelcomeMailModule.base', 'An account has been created for you on {appName}. To start using it, choose a password.', [
    'appName' => Yii::$app->name,
]) . "\n\n";
echo $claimUrl . "\n\n";
echo Yii::t('WelcomeMailModule.base', 'This link works once and expires in {n,plural,=1{# day}other{# days}}.', ['n' => $ttlDays]) . "\n";
