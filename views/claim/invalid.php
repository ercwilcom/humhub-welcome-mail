<?php

use humhub\widgets\bootstrap\Button;
use yii\helpers\Url;

/**
 * The activation link has expired, or was already used.
 *
 * Both ways out lead somewhere USEFUL for someone with no password yet:
 * recovery, or the login page if they already set one (an already-consumed
 * link is the common case). A "Home" button would only send them to a feed
 * that bounces them back to a login screen.
 */

$this->pageTitle = Yii::t('WelcomeMailModule.base', 'Invalid link');
?>
<div id="welcome-mail-invalid" class="container container-password">
    <?= $this->render('_brand') ?>

    <div class="panel panel-default">
        <div class="panel-heading">
            <?= Yii::t('WelcomeMailModule.base', '<strong>Link</strong> expired or invalid') ?>
        </div>
        <div class="panel-body">
            <p><?= Yii::t('WelcomeMailModule.base', 'This activation link is no longer valid. It may already have been used, or it may have expired.') ?></p>
            <p><?= Yii::t('WelcomeMailModule.base', 'If you have already set your password, sign in. If not, have a new link sent to you — or ask an administrator.') ?></p>

            <?= Button::primary(Yii::t('WelcomeMailModule.base', 'Recover my password'))->link(Url::to(['/user/password-recovery']))->pjax(false) ?>
            <?= Button::light(Yii::t('WelcomeMailModule.base', 'Sign in'))->link(Url::to(['/user/auth/login']))->pjax(false) ?>
        </div>
    </div>
</div>
