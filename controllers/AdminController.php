<?php

namespace humhub\modules\welcomeMail\controllers;

use humhub\modules\admin\components\Controller;
use humhub\modules\welcomeMail\Module;
use Yii;

/**
 * The module's settings screen.
 *
 * Small, but not optional: without it `apiSecret` could only be set by hand in
 * the database, which would make the module unusable to anyone who installs it
 * from the marketplace.
 */
class AdminController extends Controller
{
    public function actionIndex()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('welcome-mail');

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();

            // Un secret vide EFFACE la porte au lieu de l'ouvrir à tous : c'est
            // `authenticate()` qui refuse alors le chemin « bearer » en entier.
            $module->settings->set('apiSecret', trim((string) ($post['apiSecret'] ?? '')));
            $module->settings->set('landingUrl', rtrim(trim((string) ($post['landingUrl'] ?? '')), '/'));
            $module->settings->set('claimNote', trim((string) ($post['claimNote'] ?? '')));

            $days = (int) ($post['tokenTtlDays'] ?? 0);
            if ($days >= 1 && $days <= 90) {
                $module->settings->set('tokenTtlDays', $days);
            }

            $this->view->saved();

            return $this->redirect(['index']);
        }

        return $this->render('index', ['module' => $module]);
    }
}
