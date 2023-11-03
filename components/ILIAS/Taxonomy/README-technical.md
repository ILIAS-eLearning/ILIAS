# Using Taxonomies

## Taxonomy Settings in Repository Objects (ILIAS 9)

The taxonomy service is available via the DIC.

```php
$taxonomy_service = $DIC->taxonomy();
```

### Activation Setting

The general taxonomy service should be activated as "Additional Feature" in the main settings screen by using `ilObjectServiceSettingsGUI` with the `ilObjectServiceSettingsGUI::TAXONOMIES` flag.

### Taxonomy Settings Subtab

You should add the taxonomy settings as a subtab under your main settings tab. This can be done by passing the object ID of your repository object to `addSettingsSubTab`. Note that the subtab will only appear if the general setting is activated for your object.

```php
$taxonomy_service->gui()->addSettingsSubTab($obj_id);
```

### Forwarding to the Taxonomy Settings

You need to forward to the `ilTaxonomySettingsGUI` class.

ilCtrl Declaration:

```php
/**
 * @ilCtrl_Calls ...: ilTaxonomySettingsGUI
 */
```

Forwarding in executeCommand:

```php
...
    case strtolower(ilTaxonomySettingsGUI::class):
        $tax_gui = $taxonomy_service->gui()->getSettingsGUI(
            $obj_id
        );
        $this->ctrl->forwardCommand($tax_gui);
        break;
...
```
The `getSettingsGUI` method provides four parameters, only the first one is mandatory:

- (int) Object ID of your repository object
- (string) An information text that is displayed on top of the settings screen
- (bool) If true, multiple taxonomies can be created for your repository object
- (\ILIAS\Taxonomy\Settings\ModifierGUIInterface) An object that implements the `ModifierGUIInterface` interface.

### Settings Modifier

The `ModifierGUIInterface` interface allows to add additional properties to the taxonomy items in the taxonomy list and to add additional actions for each taxonomy in the list. This way your context may introduce separate settings for each taxonomy, e.g. the Category module allows to activate presentations of taxonomies as side blocks in the presentation.


## Taxonomies as Table Filter

The input class is called `ilTaxSelectInputGUI`. Since this class makes subsequent requests that must be routed to an instance of the class, your table must be included in the ilCtrl control flow and forward commands to the form of the filter.

Your TableGUI class must...

- include a `@ilCtrl_Calls` comment for `ilFormPropertyDispatchGUI` and
- add a `ilTaXSelectInputGUI` filter item in initFilter.

```php
/**
 * ...
 * @ilCtrl_Calls ilPresentationListTableGUI: ilFormPropertyDispatchGUI
 */
class ilPresentationListTableGUI extends ilTable2GUI
{   
    ...
 
    /**
     * Init filter
     */
    function initFilter()
    {
        ...
        include_once("./components/ILIAS/Taxonomy/classes/class.ilTaxSelectInputGUI.php");
        $tax = new ilTaxSelectInputGUI($this->tax_id, "tax_node", true);
        $this->addFilterItem($tax);
        $tax->readFromSession();
        $this->filter["tax_node"] = $tax->getValue();
        ...
    }
}
```

The GUI class that outputs the table must...

- include a `@ilCtrl_Calls` comment for your TableGUI class,
- forward to your TableGUI class via `executeCommand` and
- get the HTML of your TableGUI class by using `$ilCtrl->getHTML($table);`, **not** `$table->getHTML();`.

```php
/**
 * ...
 * @ilCtrl_Calls ilGlossaryPresentationGUI: ilPresentationListTableGUI
 */
class ilGlossaryPresentationGUI
{
    ...
    /**
     * execute command
     */
    function executeCommand()
    {
        ...
        $next_class = $this->ctrl->getNextClass($this);
        ...
        switch($next_class)
        {
 
            case "ilpresentationlisttablegui":
                $prtab = $this->getPresentationTable();
                $ilCtrl->forwardCommand($prtab);
                break;


            default:
                ...
                break;
        }
        $this->tpl->show();
    }
 
    function getPresentationTable()
    {
        include_once("./Modules/Glossary/classes/class.ilPresentationListTableGUI.php");
        $table = new ilPresentationListTableGUI($this, "listTerms", $this->glossary,
            $this->offlineMode(), $this->tax_node, $this->glossary->getTaxonomyId());
        return $table;
    }
 
    function listTerms()
    {
        ...
        $table = $this->getPresentationTable();
        $tpl->setContent($ilCtrl->getHTML($table));
    }
}
```