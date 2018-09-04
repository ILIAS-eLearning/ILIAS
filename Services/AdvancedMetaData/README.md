### Registration for Repository Objects
Custom Meta Data usage in Repository Objects needs to be registered
in their respective *module.xml* by adding the xml attribute *amet* to the 
*object* element.
```xml
...
<objects>
    <object ... id="grp" amet="1"
    ...
</objects>
...
```

The responsible GUI class for presenting the main features like *Settings* 
*Info-Screen* should provide a meta data tab and forward the meta data related 
commands to *ilObjectMetaDataGUI*.

```php
* @ilCtrl_Calls ilObjGroupGUI:ilObjectMetaDataGUI
...

// Add meta data tab
/**
 * @inheritdoc
 */
protected function getTabs() {
...
	if ($ilAccess->checkAccess('write','',$this->ref_id))
	{
		$mdgui = new ilObjectMetaDataGUI($this->object);					
		if($md_gui->getTab() !== null)
		{
			$this->tabs_gui->addTab(
			 "meta_data",
			 $DIC->language()->txt('meta_data'),
			 $mdtab,
			 "",
			 "ilobjectmetadatagui"
			);
		}
	}
	...

// forward to meta data gui

	

    

   


