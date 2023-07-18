# Handle Tabbed Menues

Since 3.10 a simpler handling for tabbed menues has been introduced. One methode usually defines the main tabs in a GUI class, many times called `setTabs()`. The methods that execute a certain command activate the corresponding tab. Tabs are identified by ids. A global `$ilTabs` instance is used to add/activate tabs and subtabs.

```php
// view command
function view()
{
	global $ilTabs;
 
	$ilTabs->activateTab("id_view");
	[...]
}
 
// edit command
function edit()
{
	global $ilTabs;
 
	$ilTabs->activateTab("id_edit");
	[...]
}
 
 
// setting main tabs
function setTabs()
{
	global $ilAccess, $lng, $ilTabs, $ilCtrl;
 
	if ($ilAccess->checkAccess("read", "", $this->object->getRefId()))
	{
		// add main tab, id 
		$ilTabs->addTab("id_view",
			$lng->txt("view"),
			$ilCtrl->getLinkTarget($this, "view"));
	}
	if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
	{
		$ilTabs->addTab("id_edit",
			$lng->txt("edit"),
			$ilCtrl->getLinkTarget($this, "edit"));
	}
}
```
\
**Common methods that should be used:**

- Add a tab: `$ilTabs->addTab($a_id, $a_text, $a_link, $a_frame = "");`

- Activate a tab: `$ilTabs->activateTab($a_id);`

- Add a subtab: `$ilTabs->addSubTab($a_id, $a_text, $a_link, $a_frame = "");`

- Activate a subtab: `$ilTabs->activateSubTab($a_id);`

- Clear all tabs: `$ilTabs->clearTargets();`

- Clear subtabs: `$ilTabs->clearSubTabs();`

\
*Other methods that are rarely used:*

- Remove a tab: `$ilTabs->removeTab($a_id);`

- Replace a tab: `$ilTabs->replaceTab($a_old_id,$a_new_id,$a_text,$a_link,$a_frame = '');`

\
*For help with the correct ordering of tabs please read the [tabs guideline](http://www.ilias.de/docu/goto_docu_wiki_1357_Tabs_Guideline.html).*
