ScreenContext
=============
To add or modify context-sensitive elements to the GlobalScreen, the respective `ScreenContextAwareProviders` must be able to decide whether or not they want to contribute to the current location where a user is in ILIAs. Therefore all these providers provide a collection to `ScreenContext` in which they are generally interested:

The following example is interested in the two `ScreenContext` 'repository' and 'desktop'. In all other contexts the provider is not requested at all.

```php
class MyFancyToolProvider extends AbstractDynamicToolProvider implements ScreenContextAwareProvider
{

    ...

    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->main()->repository()->desktop();
    }
    ...

```

All available contexts can be seen here: `src/GlobalScreen/ScreenContext/ContextRepository.php`

## Claiming

`ScreenContext` are used at certain places in ILIAS, this can only be done once per context. All previously available `ScreenContext` are already claimed in ILIAS, e.g:

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

## Enter data into the ScreenContext

Since a `ScreenContextAwareProvider` cannot only decide on the basis of the currently set contexts in order to display a tool, for example, or not, `ScreenContext` can be provided with any additional data:

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

All currently claimed contexts with all additional data will then be provided by the provider when the content is retrieved and the provider can granularly decide whether something should be done on the GlobalScreen or not.

This value can then be used, for example, when creating a tool:

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
