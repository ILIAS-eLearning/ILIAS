# Data Tables

Data tables should be implemented using the class `ilTable2GUI` provided by the Table Service. You usually need to extend this class and overwrite certain of its methods.

```php
// Calling GUI class
class ilFooGUI
{
	[...]
 
	/**
	* Show a data table
	*/
	function showSomeDataList()
	{
		global $tpl;
 
		include_once("./.../classes/class.ilMyTableGUI.php");
		$table_gui = new ilMyTableGUI($this, "showSomeDataList");
 
		$tpl->setContent($table_gui->getHTML());
	}
}
```

In this case a GUI class `ilFooGUI` uses `ilMyTableGUI` to display some data.

```php
/**
* Extended data table
*/
class ilMyTableGUI extends ilTable2GUI
{
 
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng;
 
		$this->setId("my_id");		
		parent::__construct($a_parent_obj, $a_parent_cmd);
 
		$this->addColumn($lng->txt("my_column_1"), "", "50%");
		$this->addColumn($lng->txt("my_column_2"), "", "50%");
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.my_row_template.html",
			"Service/MyService");     // could be a Module template, too
		$this->getMyDataFromDb();
 
		$this->setTitle($lng->txt("my_title"));
	}
 
	/**
	* Get data and put it into an array
	*/
	function getMyDataFromDb()
	{
		[...]
		$data[] = ...;          // array of assoc. data arrays
		[...]
		$this->setDefaultOrderField("nr");
		$this->setDefaultOrderDirection("asc");
		$this->setData($data);
	}
 
	/**
	* Fill a single data row.
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;
 
		$this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
		$this->tpl->setVariable("TXT_NR", $a_set["nr"]);
 
	}
 
}
```

The corresponding row template should have a similar structure to the next example:

```php
<tr class="{CSS_ROW&nbsp;}">
<td class="std">
{TXT_NR&nbsp;}
</td>
<td class="std">
{TXT_TITLE&nbsp;}
</td>
</tr>
```

More information can be found at the [ILIAS API documentation](http://ildoc.hrz.uni-giessen.de/ildoc) -> Services/Table -> Class Table2GUI.

## Table Filter

Filter sections contain one or multiple input fields that allow users to set a filter on the table content.

```php
class ilMyTableGUI...
{
	/**
	* Constructor
	*/
	function __construct(...)
	{
		...
		$this->initFilter();
		...
	}
 
	/**
	* Init filter
	*/
	function initFilter()
	{
		global $lng;
 
		// title/description
		include_once("./Services/Form/classes/class.ilTextInputGUI.php");
		$ti = new ilTextInputGUI($lng->txt("title"), "title");
		$ti->setMaxLength(64);
		$ti->setSize(20);
		$this->addFilterItem($ti);
		$ti->readFromSession();	// get currenty value from session (always after addFilterItem())
		$this->filter["title"] = $ti->getValue();
 
		...		
	}
 
	/**
	* Get items
	*/
	function getItems()
	{
		// Get table items and check filter
		// This is just an example, usually you call application classes
		// and pass the filter values.
		$items = $this->getItemsFromDB($this->filter["title"] ...);
		$this->setData($items);
	}
}
```

The input fields for table filters are a subset of form input fields. One basic difference is, that all input classes must be included separately.

The parent GUI class of the table class must implement two functions that store and reset the filter values. The default name for the first function is `applyFilter()`. The name can be set differently by `$table_gui->setFilterCommand('...')`. The reset function is called `resetFilter()` by default and can be customized with `$table_gui->setResetCommand('...')`.

```php
// Calling GUI class
class ilFooGUI
{
	[...]
 
	/**
	* Show a data table
	*/
	function showSomeDataList()
	{
		global $tpl;
 
		include_once("./.../classes/class.ilMyTableGUI.php");
		$table_gui = new ilMyTableGUI($this, "showSomeDataList");
		$table_gui->setFilterCommand("applyFilter");	// parent GUI class must implement this function
		$table_gui->setResetCommand("resetFilter");	// parent GUI class must implement this function
		$tpl->setContent($table_gui->getHTML());
	}
 
	/**
	* Apply filter
	*/
	function applyFilter()
	{
		include_once("./.../classes/class.ilMyTableGUI.php");
		$table_gui = new ilMyTableGUI($this, "showSomeDataList");
		$table_gui->writeFilterToSession();	// writes filter to session
		$table_gui->resetOffset();		// sets record offest to 0 (first page)
		$this->showSomeDataList();
	}
 
	/**
	* Reset filter
	*/
	function resetFilter()
	{
		include_once("./.../classes/class.ilMyTableGUI.php");
		$table_gui = new ilMyTableGUI($this, "showSomeDataList");
		$table_gui->resetOffset();		// sets record offest to 0 (first page)
		$table_gui->resetFilter();		// clears filter
		$this->showSomeDataList();
	}
 
}
```

### Prevent Possibility to Hide a Filter

Per default filters are hidden and can be toggled by the user. In some cases you want to always show a filter. To enforce this call `$table_gui->setDisableFilterHiding(true)`.

## Filter, Page Number, Order Field and Order Direction Storage

Many times the filter settings, ordering field and direction and the page shown (for multi-page tables) should be "kept" if the user navigates to other screens.

For example the user administration list contains a link for each user that opens a separate form to edit the properties of the user. After finishing and saving, the user list is displayed again. ILIAS should return to the same table page and use the same filter settings and ordering as before.

ILIAS saves these values only if an ID has been assigned to the table:

```php
class ilQuestionBrowserTableGUI extends ilTable2GUI
{
	...
	public function __construct($a_parent_obj, $a_parent_cmd, ...)
	{
		...
		$this->setId("qplqst".$a_parent_obj->object->getId());
		...
	}
}
```

This example is from the question list in question pools. It includes the object ID of the question pool in the table ID. This separates the table properties of different question pool tables.

Currently the table property values are stored the following way:

- Filter values (incl. hide/show): Session
- Table order field and order direction: Database (kept after logout for next user session)
- Table page number: Session

The fact that the table page number is kept may lead to undesired effects, e.g. when a new filter is set by the user, the user usually expects to be on the first page of the result set. For these cases use `$table->resetOffset()` to set the table page number to the first page - see `applyFilter()` function above.


## Selectable Columns

It is possible to add a drop down to the table representation that offers a list of columns that can be selected via checkboxes. To enable this feature, you first need to overwrite the method `getSelectableColumns()` that returns the array of column names and whether the column should be activated per default or not.

```php
function getSelectableColumns()
{
	$cols["firstname"] = array(
		"txt" => $lng->txt("firstname"),
		"default" => true);
	$cols["lastname"] = array(
		"txt" => $lng->txt("lastname"),
		"default" => true);
	$cols["email"] = array(
		"txt" => $lng->txt("email"),
		"default" => false);
	return $cols;
}
```

You can now get a list of all selected columns by using `$this->getSelectedColumns()`.

```php
protected function fillRow($a_set)
{
	...
	foreach ($this->getSelectedColumns() as $c)
	{
		...
	}
	...
}
```

Columns that should always be displayed (no choice to be deactivated) should not be returned by `getSelectableColumns()`. A good example for this feature can be found in `ilUserTablGUI` (Services/User).