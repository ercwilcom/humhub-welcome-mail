<?php
/**
 * A minimal mail shell.
 *
 * Deliberately not a design: table-based, one column, system fonts, no images
 * to fetch. It is what a module should ship when it cannot know the house
 * style — legible everywhere, and easy to replace wholesale through
 * {@see \humhub\modules\welcomeMail\events\MailEvent}.
 *
 * @var \yii\web\View $this
 * @var string $content
 */

use yii\helpers\Html;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode(Yii::$app->name) ?></title>
    <?php $this->head() ?>
</head>
<body style="margin:0;padding:0;background:#f4f4f4">
<?php $this->beginBody() ?>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:24px 0">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                   style="max-width:560px;background:#ffffff;border-radius:6px;padding:32px;
                          font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;
                          font-size:15px;line-height:1.55;color:#333">
                <tr>
                    <td>
                        <p style="margin:0 0 24px;font-size:18px;font-weight:600;color:#222">
                            <?= Html::encode(Yii::$app->name) ?>
                        </p>
                        <?= $content ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
