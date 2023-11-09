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

If you can handle the request, return a `Factory::cannot()` , otherwise return a `Factory::can()` with the desired target which you built using ilCtrl.
