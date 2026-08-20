<?php

use humhub\assets\AppAsset;
use humhub\helpers\Html;
use humhub\modules\user\helpers\LoginBackgroundImageHelper;
use humhub\widgets\FooterMenu;
use yii\web\View;

/* @var $this View */
/* @var $content string */

/**
 * Gabarit des pages de réclamation de compte.
 *
 * Copie de `@humhub/modules/user/views/layouts/main` à une chose près : le
 * **« Powered by HumHub » du pied de page n'est pas rendu**. Cette page est
 * le tout premier écran d'un membre invité ; elle doit se lire comme la
 * communauté elle-même,
 * et le nom du logiciel qui la sert n'apprend rien à personne ici.
 *
 * ⚠️ Ce que ça retire au passage : `FooterMenu` (emplacement `login`) porte
 * DEUX choses — le « Powered by » **et** les liens que des modules ou
 * l'administration y ajoutent (mentions légales, conditions…). Sur cette
 * install la liste d'entrées est vide, donc on ne perd que la mention. **Si
 * des mentions légales sont ajoutées un jour, il faudra les re-rendre ici**
 * (cf. `footerNavigation_login.php` du cœur : `$entries` d'un côté,
 * `PoweredBy::widget()` de l'autre) — sans quoi elles manqueraient sur
 * cette page seulement.
 *
 * Les autres pages invité du Hub (connexion, récupération de mot de passe)
 * gardent le gabarit du cœur, et donc la mention.
 */

AppAsset::register($this);

if (LoginBackgroundImageHelper::hasImage()) {
    $this->registerCss(
        '.login-container { background-image: url("' . LoginBackgroundImageHelper::getUrl() . '"); }'
    );
}

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="<?= LoginBackgroundImageHelper::hasImage() ? 'login-layout-background' : '' ?>">
<head>
    <title><?= Html::encode($this->pageTitle) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <?php $this->head() ?>
    <?= $this->render('@humhub/views/layouts/head'); ?>
    <meta charset="<?= Yii::$app->charset ?>">
</head>

<body class="login-container">
<?php $this->beginBody() ?>
<?= $content; ?>
<br/>
<?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>
