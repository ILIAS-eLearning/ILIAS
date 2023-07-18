# Using Taxonomies

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
        include_once("./Services/Taxonomy/classes/class.ilTaxSelectInputGUI.php");
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