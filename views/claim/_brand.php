<?php

use humhub\helpers\Html;
use humhub\libs\LogoImage;

/**
 * The site logo at the top of the claim pages — **with no link**.
 *
 * `SiteLogo::widget(['place' => PLACE_LOGIN])` renders the same image but
 * always wraps it in `<a href="{homeUrl}">`, i.e. the dashboard. Someone
 * reading this page has no password yet: the only useful thing here is the
 * form, and a clickable logo offers them nothing but an exit to a page that
 * will send them straight back to a login screen.
 *
 * Hence rendering {@see LogoImage} directly instead of the widget.
 */
?>
<?php if (LogoImage::hasImage()): ?>
    <?= Html::img(LogoImage::getUrl(500, 250), [
        'id' => 'img-logo',
        'class' => 'rounded',
        'alt' => Html::encode(Yii::$app->name),
    ]) ?>
<?php else: ?>
    <h1 id="app-title"><?= Html::encode(Yii::$app->name) ?></h1>
<?php endif; ?>
<br>
