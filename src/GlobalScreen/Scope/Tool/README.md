Scope Tools
===========
`Tools` are context-dependent entries in the `MainBar` that offer a specific function at a specific location in ILIAS. Many use cases are already known in ILIAS and are now only implemented as `Tools`. For example, all trees displayed on the left in ILIAS < 6.0 are now visible as `Tools` at the respective position.

The mechanism how `Tools` are provided to the GlobalScreen is exactly the same as for the other contexts `MainBar` and `MetaBar`. As `DynamicToolProvider` you get entries that you can get from the `ToolsFactory`, for example:

```php
class ilMediaPoolGSToolProvider extends AbstractDynamicToolProvider
{

...
    /**
     * @inheritDoc
     */
    public function getToolsForContextStack(CalledContexts $called_contexts) : array
    {
        $tools = [];
        $iff = function ($id) { return $this->identification_provider->identifier($id); };
        $l = function (string $content) { return $this->dic->ui()->factory()->legacy($content); };

        $additional_data = $called_contexts->getLast()->getAdditionalData();
        if ($additional_data->exists(self::SHOW_FOLDERS_TOOL) && $additional_data->get(self::SHOW_FOLDERS_TOOL) === true) {
            $tools[] = $this->factory->tool($iff("tree"))
                ->withTitle("Folders")
                ->withContent($l($this->getTree()));
        }

        return $tools;
    }
...
}
```

The Scope Tool is a context-sensitive tool, more information about this under [src/GlobalScreen/ScreenContext/README.md](../../ScreenContext/README.md)
