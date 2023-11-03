# Using Tabs

Since 3.10 a simpler handling for tabbed menues has been introduced. One methode usually defines the main tabs in a GUI class, many times called `setTabs()`. The methods that execute a certain command activate the corresponding tab. Tabs are identified by ids. A global `$ilTabs` instance is used to add/activate tabs and subtabs.

```php

function __construct(ilTabsGUI $tabs)
{
    $this->tabs = $tabs;    // alternatively throug $DIC->tabs();
}

// view command
function view()
{
	[...]
	$this->tabs->activateTab("id_view");
	[...]
}
 
// edit command
function edit()
{
	[...]
	$this->tabs->activateTab("id_edit");
	[...]
}
 
 
// setting main tabs
function setTabs()
{
	global $ilAccess, $lng, $ilTabs, $ilCtrl;
 
	if ($this->access->checkAccess("read", "", $this->object->getRefId()))
	{
		// add main tab, id 
		$this->tabs->addTab("id_view",
			$this->lng->txt("view"),
			$this->ctrl->getLinkTarget($this, "view"));
	}
	if ($this->access->checkAccess("write", "", $this->object->getRefId()))
	{
		$this->tabs->addTab("id_edit",
			$this->lng->txt("edit"),
			$this->ctrl->getLinkTarget($this, "edit"));
	}
}
```
\
**Common methods that should be used:**

- Add a tab: `$tabs->addTab($a_id, $a_text, $a_link, $a_frame = "");`

- Activate a tab: `$tabs->activateTab($a_id);`

- Add a subtab: `$tabs->addSubTab($a_id, $a_text, $a_link, $a_frame = "");`

- Activate a subtab: `$tabs->activateSubTab($a_id);`

- Clear all tabs: `$tabs->clearTargets();`

- Clear subtabs: `$tabs->clearSubTabs();`

\
*Other methods that are rarely used:*

- Remove a tab: `$tabs->removeTab($a_id);`

- Replace a tab: `$tabs->replaceTab($a_old_id,$a_new_id,$a_text,$a_link,$a_frame = '');`

\
*For help with the correct ordering of tabs please read the [tabs guideline](http://www.ilias.de/docu/goto_docu_wiki_1357_Tabs_Guideline.html).*
