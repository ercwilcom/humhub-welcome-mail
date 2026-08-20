# Welcome Mail for HumHub

Emails existing members a magic link to set their first password and sign in.

For communities whose accounts were created *for* people — by an import, or by
an administrator — rather than by the people themselves. Such an account has no
password its owner has ever known, and "reset your password" is a strange first
thing to ask of someone who never had one. This module sends a welcome instead.

## What happens

1. Something triggers the mail — a `POST` to the API below, from whatever
   already knows who should receive it.
2. The member receives a link carrying a single-use token, valid 7 days by
   default (`tokenTtlDays`).
3. They land on a guest-accessible page, choose a password, and are signed in.
4. They are redirected — to the dashboard, or wherever `landingUrl` points.

Only a hash of the token is stored; the raw value exists in the email and
nowhere else.

## The API

```
POST /welcome/send
```

Recipients, combined freely: `user_id`, `email`, `user_ids` (comma-separated),
`emails` (comma-separated). Returns
`{ ok: true, results: [ { user_id, email, sent, reason? } ] }`.

Two ways to authenticate:

- `Authorization: Bearer <apiSecret>` — a secret of this module's own. Set it in
  the module settings; empty means this path is closed.
- `Authorization: Basic <client_id:secret>` — a confidential client of the
  **OIDC Provider** module, when that module is installed alongside. Where both
  live together the calling application already holds credentials, and a second
  secret would only be a second thing to keep in sync.

## Making it yours

The email this module sends is deliberately plain: here is your link, choose a
password. Three seams let a deployment say more without forking it.

| Seam | What it changes |
|---|---|
| `claimNote` setting | HTML shown on the set-a-password page — say where people are about to land |
| `landingUrl` setting | Where to send them afterwards; empty means the dashboard |
| `Module::EVENT_COMPOSE_MAIL` | Swap the mail views, layout and subject wholesale |
| `Module::EVENT_RESOLVE_LANDING` | Work the destination out at runtime instead of storing a URL |

The two events are the interesting ones. A deployment whose destination is
already recorded somewhere — an OIDC client's redirect URI, say — should derive
it rather than have an administrator keep a second copy in step.

```php
// in your module's config.php
['class' => WelcomeMail::class, 'event' => WelcomeMail::EVENT_COMPOSE_MAIL,
 'callback' => [Events::class, 'onWelcomeCompose']],
```

## Licence

AGPL-3.0-or-later. See `LICENSE`.
