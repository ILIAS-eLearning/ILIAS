# COPage (ILIAS Page Editor)

This component implements the ILIAS page editor as being used e.g. in learning modules, wikis, content pages in courses and other containers.

## Browser support

Since ILIAS 7 the browser makes extensive use of ES6 features. For ILIAS 7 the current maintainer accepts issues for the latest Firefox, Chrome, Safari and Edge versions. However please note that issues that appear only on specific browsers


### [WIP] Using the page editor in another components

In order to use the page component in your component you first need to extend your `modules.xml` or `service.xml`.

```
	<copage>
		<pageobject parent_type="{PARENT_TYPE}" class_name="{BASIC_CLASS_NAME}" directory="classes"/>
	</copage>
```

The `{PARENT_TYPE}` should be unique through the ILIAS code base, e.g. this could be the module repository type of your component.

You will need implement new classes in your component that derive from these classes of the COPage component:

* `ilPageObject`
* `ilPageObjectGUI`
* `ilPageConfig`

The class files should be located in the directory stated in `modules.xml` or `service.xml` (in this examples `classes`).

* `class {BASIC_CLASS_NAME} extends \ilPageObject`
* `class {BASIC_CLASS_NAME}GUI extends \ilPageObjectGUI`
* `class {BASIC_CLASS_NAME}Config extends \ilPageObjectConfig`

**class {BASIC_CLASS_NAME} extends \ilPageObject**

This class should overwrite method `getParentType()` and returned the value specified in your `modules.xml` or `service.xml`.

```
/**
 * @inheritdoc
 */
public function getParentType()
{
	return `{PARENT_TYPE}`;
}
```

Please do not overwrite the constructor of this class.

**class {BASIC_CLASS_NAME}GUI extends \ilPageObjectGUI**

The weak point of this class is also its constructor. You should overwrite it in order to pass your parent type in the following way:

```
	function __construct($a_id = 0, $a_old_nr = 0, $a_prevent_get_id = false, $a_lang = '')
	{
		...		
		parent::__construct('{PARENT_TYPE}', $a_id, $a_old_nr, $a_prevent_get_id, $a_lang);
		...
	}
```

We might declare the constructor final in the base class in the future to enable a factory for these classes as well.

**class {BASIC_CLASS_NAME}Config extends \ilPageObjectConfig**

This class is used to enable/disable different features of the page (editor). You should overwrite its `init()` method:

```
/**
 * @inheritdoc
 */
public function init()
{
	$this->setEnableInternalLinks(...);
	...
}
```

**Embedding in ilCtrl control flow**

Depending on where you want to use the presentation or editing features of the component, you will need to init and embed your {BASIC_CLASS_NAME}GUI in your `executeCommand()` methods.

A good example for how this can be done is `Modules/ContentPage/classes/class.ilContentPagePageCommandForwarder.php`.


## [WIP] Internal Documentation

### Data

The page editor stores data in XML format that needs to validate against the latest `xml/ilias_pg_x_x.dtd` (ILIAS main directory). This data is being stored in table `page_object` handled by class `ilPageObject`.

### Rendering

The main content rendering currently happens in class `ilPageObjectGUI` which transforms the XML using `./xsl/page.xsl` and a lot of post processing afterwards. 

### Page Content Components

...

### Multi-Language Support

Multi language support has added an additional dimension of complexity to the content page component.

Multi language support depends always on the parent repository object.

**Basics**

* new table `copg_multilang`: defines default language per repository obj id (-> "-" records)
* all `page_object` records with "-" in `lang` field represent the default language (value is not set in page_object -> no dependent tables need to be updated)
* table `copg_multilang_lang` contains all other languages supported by the repository object