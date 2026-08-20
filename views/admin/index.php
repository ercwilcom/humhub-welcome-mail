<?php
/** @var humhub\modules\welcomeMail\Module $module */

use yii\helpers\Html;

$s = $module->settings;
?>
<div class="panel panel-default">
    <div class="panel-heading"><?= Yii::t('WelcomeMailModule.base', '<strong>Welcome</strong> mail') ?></div>
    <div class="panel-body">
        <form method="post">
            <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

            <div class="form-group">
                <label><?= Yii::t('WelcomeMailModule.base', 'API secret') ?></label>
                <input type="text" class="form-control" name="apiSecret"
                       value="<?= Html::encode((string) $s->get('apiSecret', '')) ?>">
                <p class="help-block">
                    <?= Yii::t('WelcomeMailModule.base', 'Sent as <code>Authorization: Bearer …</code> to <code>POST /welcome/send</code>. Leave empty to close that route entirely.') ?>
                </p>
            </div>

            <div class="form-group">
                <label><?= Yii::t('WelcomeMailModule.base', 'Link lifetime (days)') ?></label>
                <input type="number" class="form-control" name="tokenTtlDays" min="1" max="90"
                       value="<?= (int) $s->get('tokenTtlDays', $module->tokenTtlDays) ?>">
            </div>

            <div class="form-group">
                <label><?= Yii::t('WelcomeMailModule.base', 'Landing URL') ?></label>
                <input type="text" class="form-control" name="landingUrl"
                       value="<?= Html::encode((string) $s->get('landingUrl', '')) ?>"
                       placeholder="https://app.example.org">
                <p class="help-block">
                    <?= Yii::t('WelcomeMailModule.base', 'Where people go after setting their password. Empty means the dashboard.') ?>
                </p>
            </div>

            <div class="form-group">
                <label><?= Yii::t('WelcomeMailModule.base', 'Note on the password page') ?></label>
                <textarea class="form-control" name="claimNote" rows="4"><?= Html::encode((string) $s->get('claimNote', '')) ?></textarea>
                <p class="help-block">
                    <?= Yii::t('WelcomeMailModule.base', 'HTML, shown above the password fields. Use it to say where people are about to land.') ?>
                </p>
            </div>

            <button type="submit" class="btn btn-primary"><?= Yii::t('WelcomeMailModule.base', 'Save') ?></button>
        </form>
    </div>
</div>
