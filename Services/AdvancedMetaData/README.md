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
<?php

/*
 * ...
 * @ilCtrl_Calls ilObjGroupGUI:ilObjectMetaDataGUI
 * ...
 */
class ilObjXXXGUI extends ilObject2GUI 
{
	// Add meta data tab
	/**
	 * @inheritdoc
	 */
	protected function getTabs() 
	{
		global $DIC;
		
		if ($DIC->access()->checkAccess('write','',$this->ref_id))
		{
			$mdgui = new ilObjectMetaDataGUI($this->object);
			$mdtab = $mdgui->getTab();
			if($mdtab !== null)
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
	}
	//...
	// forward to meta data gui
	public function executeCommand() 
	{
		// ...
		switch($next_class) {
			
			case 'ilobjectmetadatagui';
				$this->tabs_gui->activateTab('meta_data');
				$md_gui = new ilObjectMetaDataGUI($this->object);	
				$this->ctrl->forwardCommand($md_gui);
				break;
		}
	}
}

```

	

    

   


