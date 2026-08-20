<?php

namespace humhub\modules\welcomeMail\controllers;

use humhub\modules\oidcProvider\models\Client;
use humhub\modules\welcomeMail\Module;
use humhub\modules\user\models\User;
use Yii;
use yii\web\Controller;
use yii\web\Response;

/**
 * Server-to-server trigger for the welcome email.
 *
 * Authenticated either with a bearer token of this module's own, or with a
 * confidential OIDC client's id + secret when the OIDC provider module is
 * installed alongside. See `authenticate()`.
 *
 * POST /welcome/send
 *   Identify recipients with any of (combine freely):
 *     - user_id   single HumHub user id
 *     - email     single email
 *     - user_ids  comma-separated user ids
 *     - emails    comma-separated emails
 *
 * Returns JSON: { ok: true, results: [ { user_id, email, sent, reason? }, ... ] }
 */
class ApiController extends Controller
{
    public $enableCsrfValidation = false;
    public $layout = false;

    public function actionSend()
    {
        if (!$this->authenticate()) {
            return $this->status(401, ['error' => 'invalid_client']);
        }

        $req = Yii::$app->request;
        $userIds = $this->csv($req->post('user_ids', '')) ?: [];
        $emails = $this->csv($req->post('emails', '')) ?: [];

        if ($single = trim((string) $req->post('user_id', ''))) {
            $userIds[] = $single;
        }
        if ($single = trim((string) $req->post('email', ''))) {
            $emails[] = $single;
        }

        if (!$userIds && !$emails) {
            return $this->status(400, ['error' => 'no_recipients']);
        }

        /** @var Module $module */
        $module = Yii::$app->getModule('welcome-mail');

        $results = [];
        $seen = [];

        foreach ($this->resolveUsers($userIds, $emails) as $entry) {
            [$user, $label, $reason] = $entry;

            if ($user === null) {
                $results[] = ['identifier' => $label, 'sent' => false, 'reason' => $reason];
                continue;
            }
            if (isset($seen[$user->id])) {
                continue;
            }
            $seen[$user->id] = true;

            $sent = false;
            try {
                $sent = $module->sendWelcome($user);
            } catch (\Throwable $e) {
                Yii::error('Welcome mail failed for user #' . $user->id . ': ' . $e->getMessage(), 'welcome-mail');
            }

            $results[] = [
                'user_id' => $user->id,
                'email' => $user->email,
                'sent' => $sent,
            ] + ($sent ? [] : ['reason' => 'send_failed']);
        }

        return $this->status(200, ['ok' => true, 'results' => $results]);
    }

    /**
     * @return array<array{0: ?User, 1: string, 2: ?string}> [user|null, label, reason|null]
     */
    private function resolveUsers(array $userIds, array $emails): array
    {
        $out = [];

        foreach ($userIds as $id) {
            $user = User::findOne(['id' => (int) $id, 'status' => User::STATUS_ENABLED]);
            $out[] = [$user, 'user_id:' . $id, $user ? null : 'user_not_found'];
        }
        foreach ($emails as $email) {
            $user = User::findOne(['email' => $email, 'status' => User::STATUS_ENABLED]);
            $out[] = [$user, $email, $user ? null : 'user_not_found'];
        }

        return $out;
    }

    private function csv($value): array
    {
        if (!is_string($value) || $value === '') {
            return [];
        }
        return array_values(array_filter(array_map('trim', explode(',', $value)), 'strlen'));
    }

    /**
     * Verify HTTP Basic credentials against a confidential OIDC client.
     */
    /**
     * Two ways in, and the module works with either.
     *
     * ── 1. A BEARER TOKEN OF OUR OWN ────────────────────────────────────────
     * `Authorization: Bearer <apiSecret>`, where the secret is this module's
     * `apiSecret` setting. This is what makes the module stand on its own:
     * anyone can install it, paste a secret, and have their own application
     * trigger welcome mail.
     *
     * ── 2. A CONFIDENTIAL OIDC CLIENT ───────────────────────────────────────
     * `Authorization: Basic <id:secret>`, checked against the OIDC provider
     * module when that module is installed. Where both live side by side, the
     * calling application already holds credentials, and asking an
     * administrator to mint and sync a second secret would only add a thing
     * to get wrong.
     *
     * An empty `apiSecret` disables path 1 outright rather than matching the
     * empty string — the usual reading of "not configured yet".
     */
    private function authenticate(): bool
    {
        $header = (string) Yii::$app->request->headers->get('Authorization', '');

        if (stripos($header, 'bearer ') === 0) {
            $presented = trim(substr($header, 7));
            $expected = trim((string) Yii::$app->getModule('welcome-mail')->settings->get('apiSecret', ''));

            // hash_equals, not === : the comparison is against a secret, and a
            // timing difference is a slow way to read it out one byte at a time.
            return $expected !== '' && hash_equals($expected, $presented);
        }

        if (stripos($header, 'basic ') !== 0 || !class_exists(Client::class)) {
            return false;
        }
        $decoded = base64_decode(trim(substr($header, 6)), true);
        if ($decoded === false || !str_contains($decoded, ':')) {
            return false;
        }
        [$cid, $secret] = explode(':', $decoded, 2);
        $client = Client::findOne(['client_id' => $cid]);

        return $client && $client->is_confidential && $client->verifySecret($secret);
    }

    private function status(int $code, array $payload): Response
    {
        $r = Yii::$app->response;
        $r->statusCode = $code;
        $r->format = Response::FORMAT_JSON;
        if ($code === 401) {
            $r->headers->set('WWW-Authenticate', 'Bearer realm="welcome", Basic realm="welcome"');
        }
        $r->data = $payload;
        return $r;
    }
}
