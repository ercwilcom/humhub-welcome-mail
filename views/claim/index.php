<?php

use humhub\helpers\Html;
use humhub\widgets\form\ActiveForm;

/* @var $model \humhub\modules\user\models\Password */
/* @var $user \humhub\modules\user\models\User */

/**
 * The set-a-password form — the very first page an invited member sees.
 *
 * It should read as the community, not as "a HumHub": the logo carries no
 * link back to a dashboard the visitor cannot reach yet (see `_brand`), and
 * there is no arrival animation.
 *
 * The `claimNote` setting is where a deployment says what the module cannot
 * know — which application people are about to land in, and how to get back.
 * Rendered as HTML, and set by an administrator, like HumHub's own rich-text
 * settings.
 */

$this->pageTitle = Yii::t('WelcomeMailModule.base', 'Activate your account');

/**
 * The password requirement, in the visitor's language.
 *
 * HumHub's default hint is an untranslated English string from
 * `UserModule::getDefaultPasswordStrength()`. On the first page a member ever
 * sees, a stray English sentence is exactly what makes a site read as
 * somebody's software rather than as their own community.
 *
 * Replaced **only when the rule is the default one**: an administrator who
 * set their own requirement gets their own words back.
 */
$userModule = Yii::$app->getModule('user');
$passwordHint = $userModule->isCustomPasswordStrength()
    ? $userModule->getPasswordHint()
    : Yii::t('WelcomeMailModule.base', 'At least 5 characters.');

$claimNote = trim((string) Yii::$app->getModule('welcome-mail')->settings->get('claimNote', ''));
?>
<div id="welcome-mail-claim" class="container container-password">
    <?= $this->render('_brand') ?>

    <div id="welcome-claim-form" class="panel panel-default">
        <div class="panel-heading">
            <?= Yii::t('WelcomeMailModule.base', '<strong>Welcome</strong>, {name}', ['name' => Html::encode($user->displayName)]) ?>
        </div>
        <div class="panel-body">

            <p><?= Yii::t('WelcomeMailModule.base', 'Choose a password to activate your account.') ?></p>

            <?php if ($claimNote !== '') : ?>
                <?= $claimNote ?>
            <?php endif; ?>

            <?php $form = ActiveForm::begin(['enableClientValidation' => false]); ?>

            <?= $form->field($model, 'newPassword')
                ->label(Yii::t('WelcomeMailModule.base', 'Password'))
                ->hint($passwordHint)
                ->passwordInput(['class' => 'form-control', 'id' => 'new_password', 'maxlength' => 255, 'value' => '']) ?>

            <?= $form->field($model, 'newPasswordConfirm')
                ->label(Yii::t('WelcomeMailModule.base', 'Confirm the password'))
                ->passwordInput(['class' => 'form-control', 'maxlength' => 255, 'value' => '']) ?>

            <?= Html::submitButton(Yii::t('WelcomeMailModule.base', 'Create my password'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']); ?>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<script <?= Html::nonce() ?>>
    $(function () {
        $('#new_password').focus();
    });

    <?php if ($model->hasErrors()) { ?>
    // The one movement kept, because it SAYS something ("read the field again").
    <?php } ?>
    <?php if ($model->hasErrors()) { ?>
    $('#welcome-claim-form').addClass('shake');
    <?php } ?>
</script>
