<?php

namespace humhub\modules\welcomeMail\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\modules\welcomeMail\events\LandingEvent;
use humhub\modules\welcomeMail\Module;
use humhub\modules\welcomeMail\models\WelcomeToken;
use humhub\modules\user\models\Password;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Event;

/**
 * Public landing page for the welcome magic link.
 *
 * The recipient sets a password, which is saved as the user's current password
 * and logs them straight into HumHub. From there the OIDC authorize endpoint
 * (which reuses the HumHub session) signs them into a sister application on
 * the next visit without asking for credentials again.
 *
 * **La porte d'arrivée n'est pas forcément le tableau de bord du Hub.**
 * Une application sœur peut être l'outil de travail — le Hub étant la place
 * communautaire à côté. Quelqu'un qui vient de créer son mot de passe n'a
 * encore rien vu : le déposer sur le fil d'actualité, c'est le laisser
 * deviner qu'une autre app existe. On l'emmène donc à la racine de
 * l'application configurée, qui l'accueille à sa façon puis
 * le route. La session HumHub vient d'être ouverte, donc le SSO OIDC est
 * silencieux : aucun deuxième mot de passe à taper.
 */
class ClaimController extends Controller
{
    /**
     * Gabarit invité maison : celui du cœur (login / récupération de mot de
     * passe), moins le « Powered by HumHub » du pied. Cf. views/layouts/claim.
     */
    public $layout = '@welcome-mail/views/layouts/claim';

    /**
     * Allow guest access regardless of the site's guest-mode setting — the
     * recipient has no session yet, by definition.
     */
    public $access = ControllerAccess::class;

    public function actionIndex()
    {
        // Already signed in? The link's job is done — go where it would have led.
        if (!Yii::$app->user->isGuest) {
            return $this->redirect($this->landingUrl());
        }

        $token = WelcomeToken::findUsable(Yii::$app->request->get('token'));
        if ($token === null) {
            return $this->render('invalid');
        }

        $user = User::findOne(['id' => $token->user_id, 'status' => User::STATUS_ENABLED]);
        if ($user === null) {
            return $this->render('invalid');
        }

        $model = new Password();
        $model->scenario = 'registration';
        $model->user_id = $user->id;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->setPassword($model->newPassword);
            $model->save(false);

            // Clear any "must change password" flag the account may carry.
            if (method_exists($user, 'setMustChangePassword')) {
                $user->setMustChangePassword(false);
            }

            $token->markUsed();

            Yii::$app->user->login($user);

            $landing = $this->landingUrl();

            // Le flash n'a de sens que si on RESTE dans le Hub : il s'affiche au
            // rendu de la page suivante, or la page suivante peut être ailleurs,
            // qui ne lit pas les flashs de HumHub. Le poser quand même le
            // laisserait dormir en session et surgir des jours plus tard, hors
            // contexte, sur une page du Hub sans rapport. L'application d'arrivée,
            // accueille avec son propre écran « Bienvenue ».
            if (is_array($landing)) {
                Yii::$app->session->setFlash(
                    'success',
                    Yii::t('WelcomeMailModule.base', 'Your password has been set. Welcome!')
                );
            }

            return $this->redirect($landing);
        }

        return $this->render('index', [
            'model' => $model,
            'user' => $user,
        ]);
    }

    /**
     * Where to drop someone who has just claimed their account.
     *
     * Two sources, in order: the `landingUrl` setting, then any handler of
     * {@see LandingEvent} — which is how a deployment points people at a
     * sister application without an administrator having to keep a URL in
     * sync by hand.
     *
     * **Falls back to the Hub dashboard** whenever neither produces an
     * absolute URL. This is the worst moment in the whole journey to show an
     * error: the person has just created a password and has no idea yet what
     * they are signing up to. A plain page beats a broken one.
     *
     * @return string|array Absolute URL, or an internal HumHub route.
     */
    private function landingUrl()
    {
        $url = null;

        try {
            $configured = trim((string) Yii::$app->getModule('welcome-mail')->settings->get('landingUrl', ''));
            if ($configured !== '') {
                $url = rtrim($configured, '/');
            }

            // Both call sites run with a session in hand: one after login(),
            // the other behind an isGuest check.
            $event = new LandingEvent(['user' => Yii::$app->user->identity, 'url' => $url]);
            Event::trigger(Module::class, Module::EVENT_RESOLVE_LANDING, $event);
            $url = $event->url;
        } catch (\Throwable $e) {
            Yii::error(
                'Landing URL could not be resolved, falling back to the dashboard: ' . $e->getMessage(),
                'welcome-mail'
            );
            $url = null;
        }

        return $url ?? ['/dashboard/dashboard'];
    }
}
