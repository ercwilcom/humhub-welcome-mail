<?php

use humhub\modules\welcomeMail\Module;

return [
    'id' => 'welcome-mail',
    'class' => Module::class,
    'namespace' => 'humhub\\modules\\welcomeMail',
    'urlManagerRules' => [
        // Server-to-server trigger. Authenticated by a bearer token of this
        // module's own, or by a confidential OIDC client where the provider
        // module is installed alongside — see `controllers/ApiController`.
        [
            'pattern' => 'welcome/send',
            'route' => 'welcome-mail/api/send',
            'verb' => 'POST',
        ],
        // Public magic-link landing / set-password page.
        [
            'pattern' => 'welcome/claim',
            'route' => 'welcome-mail/claim/index',
        ],
    ],
];
