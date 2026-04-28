Static URL Service
==================

ILIAS had static URLs (goto URLs) for repository objects (and some other exceptions) for many versions.

For ILIAS 9 this mechanism has been reworked so that all services, modules and plugins can offer such URLs. In addition, the form of the URLs has been simplified.

so the old URL

`http://ilias.sr.solutions/goto.php?target=file_default_89_download`

becomes

`http://ilias.sr.solutions/go/file/89/download`

# How to use
In ILIAS 9, the old `ilLink` class is rebuilt internally so that the links are all rewritten directly. In addition, a `Handler` (more on this below) was written, which ensures the behavior of the old `goto.php`. This means that in ILIAS 9 no adjustment to the modules is necessary.

You can also generate the link directly yourself as follows:

```php
use ILIAS\StaticURL\Services;
global $DIC;
/** @var Services $static_url */
$static_url = $DIC['static_url'];

$uri = $static_url->builder()->build(
    'wiki', // namespace
    123, // ref_id
    ['additional', 5, 'parameters'], // additional parameters
);
```

To handle Links for a namespace, you have to register a `Handler` for it. Implement a `\ILIAS\StaticURL\Handler\Handler` for a class in your scope, e.g.:

```php
class ilFileStaticURLHandler extends BaseHandler implements Handler
{
    public const DOWNLOAD = 'download';
    public const VERSIONS = 'versions';

    public function getNamespace(): string
    {
        return 'file';
    }

    public function handle(Request $request, Context $context, Factory $response_factory): Response
    {
        $ref_id = $request->getReferenceId()?->toInt() ?? 0;
        $additional_params = $request->getAdditionalParameters()[0] ?? null;
        $context->ctrl()->setParameterByClass(ilObjFileGUI::class, 'ref_id', $ref_id);

        $uri = match ($additional_params) {
            self::DOWNLOAD => $context->ctrl()->getLinkTargetByClass(
                [ilRepositoryGUI::class, ilObjFileGUI::class],
                ilObjFileGUI::CMD_SEND_FILE
            ),
            self::VERSIONS => $context->ctrl()->getLinkTargetByClass(
                [ilRepositoryGUI::class, ilObjFileGUI::class, ilFileVersionsGUI::class]
            ),
            default => $context->ctrl()->getLinkTargetByClass([ilRepositoryGUI::class, ilObjFileGUI::class]),
        };

        return $response_factory->can($uri);
    }

}
```

The `Request` holds the Infos which have been used while `build` the URI (see above). The `Context` holds the ILIAS context (e.g. the `ilCtrlInterface`), the `Factory` is a factory to create `Response` objects. The `Response` is the result of the `Handler` and will be used to redirect the user to the correct URL.

# Response Types

A `Handler::handle()` must always return a `Response`. Pick the type that matches the situation — the `HandlerService` dispatches on it. Use `\ILIAS\StaticURL\Response\Factory` to produce them.

| Factory method | Response | When to return it | What the HandlerService does |
|----------------|----------|-------------------|------------------------------|
| `can(string $uri_path, bool $shift = false)` | `CanHandleWithURIPath` | Target exists and the current user is allowed to reach it. You have a concrete URI to redirect to. | Redirects the user to the given URI (optionally shifting one segment off the base URI first). |
| `loginFirst()` | `MaybeCanHandlerAfterLogin` or `CannotReach` | Convenience: current user cannot reach the target. The Factory branches automatically on login state. | See the two rows below. |
| (anonymous branch of `loginFirst()`) | `MaybeCanHandlerAfterLogin` | User is anonymous and login might resolve the situation. | Redirects to `login.php` with the original target preserved as `?target=...`. After login, the user is forwarded to the target. |
| `cannotReach()` (or logged-in branch of `loginFirst()`) | `CannotReach` | User is logged in but has no permission on the target. | If the `Request` carries a `ReferenceId`, walks up the repository tree and redirects to the first parent the user can read. `pending_goto` is stored in the session so the course/group registration flow shows `reg_goto_parent_membership_info` and offers the join action. If no readable parent is found (or no `ReferenceId` is present), redirects to the user's Starting Point / Dashboard with a `permission_denied` message. |
| `cannot()` | `CannotHandle` | The Handler cannot process this `Request` at all (malformed request, unknown sub-target, …). This is a contract-level signal and should NOT be used to express "user lacks permission". | Responds with HTTP 404. |

## Decision guide

- Target resolved → `Factory::can($uri)`.
- Current user cannot reach the target → `Factory::loginFirst()` (handles both anon and logged-in branches). Use `Factory::cannotReach()` directly if you know the user is logged in.
- Request shape is wrong / Handler does not understand it → `Factory::cannot()`.

# Architecture Notes

The service is wired in `ILIAS\StaticURL\Init`. Relevant keys on the DIC:

- `static_url.context` — `Context`, a thin wrapper around the ILIAS `Container` exposing only what Handlers need (http, ctrl, access, repositoryTree, ...). Handlers receive it as a parameter and should use it instead of `global $DIC`.
- `static_url.session_store` — `ILIAS\StaticURL\Session\SessionStore`, an abstraction over `\ilSession` used internally by the `HandlerService` (e.g. to set `pending_goto`). Default implementation: `ILIASSessionStore`. Injected into `HandlerService` via constructor so tests can swap it.
- `static_url.handler` — the `HandlerService` that resolves a `Request` via the registered `Handler`s and turns the resulting `Response` into an HTTP redirect or a 404.
- `static_url.uri_builder` / `static_url.config` — URI building for outbound links (see `builder()->build()` above).

The `Context` exposes, among other things, `findFirstAccessibleParentRefId(int $ref_id, string $permission = 'read')`, which is used by the `CannotReach` fallback to locate the nearest parent the current user can read.
