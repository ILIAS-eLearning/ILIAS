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

# ToolContexts

What you get in `getToolsForContextStack()` is a stack of `ToolContexts`. Such a `ToolContext` contains a unique name as well as additional information and data which can be given to the `ToolContext` at runtime. More about this below.

As `Tools` are context-related, you have to tell the GlobalScreen service which contexts you are interested in. Only for these you will be asked effectively. These are announced as follows:

```php
public function isInterestedInContexts() : ContextCollection
{
    return $this->context_collection->main();
}
```

If you are interested in more than one, you can easily add them:

```php
return $this->context_collection->main()->repository()->administration();
```

In any case, a ContextCollection is returned.

Currently the following ToolContexts are available:

- Main (this context exists 'always' as soon as you move in ILIAS, no matter if you are logged in or not)
- Desktop (actually everything that is not in the repository or the administration)
- administration
- Internal (logged in)
- External (logged out, public area, login, ...)

> New ToolContexts MUST be discussed with the Maintainer and in JourFixe!

## Claiming
ToolContexts are always beeing claimed, this can only be done once per context. The currently available context is all already claimed like e.g. 

```php
class ilRepositoryGUI 
{
    ...
    function __construct()
    {
        ...
        $this->tool_context = $DIC->globalScreen()->tool()->context();
        ...
    }
    ...
    function executeCommand() 
    { 
        ...
        $this->tool_context->claim()->repository();
        ...
    }
}
```

## Fill Data to the ToolContext
As mentioned above, additional data can be filled into the `ToolContext` to access this data when generating a `Tool` in your `DynamicToolProvider`. This allows you to finer decide whether to currently output a `Tool` or not. For example:

```php
class ilMediaPoolPresentationGUI
{
    ...
    public function __construct()
    {
        ...
        $DIC->globalScreen()->tool()->context()->current()->addAdditionalData(ilMediaPoolGSToolProvider::SHOW_FOLDERS_TOOL, true);
        ...
    }

```

This value can then be used when creating the tool:

```php
    public function getToolsForContextStack(CalledContexts $called_contexts) : array
    {
        ...
        $additional_data = $called_contexts->getLast()->getAdditionalData();
        if ($additional_data->exists(self::SHOW_FOLDERS_TOOL) && $additional_data->get(self::SHOW_FOLDERS_TOOL) === true) {
           ...
        }
        ...
    }
```
