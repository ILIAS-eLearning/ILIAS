# COPage (ILIAS Page Editor) - Technical Documentation

Note: Since ILIAS 7 the page editor undergoes a major revision. The old way to integrate will still work, however the interface to use slates or other new concepts is still not in a final state. If you use any of these parts breaking changes will most propable come with future versions.

### Using the page editor in another components

In order to use the page component in your component you first need to extend your `modules.xml` or `service.xml`.

```
	<copage>
		<pageobject parent_type="{PARENT_TYPE}" class_name="{BASIC_CLASS_NAME}" directory="classes"/>
	</copage>
```

The `{PARENT_TYPE}` should be unique through the ILIAS code base, e.g. this could be the module repository type of your component. But please note that page parent types and repository object types are not the same. Some repository components may implementy multiple page parent types, others may share the same parent page type, e.g. container pages.

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

**Setting the Page Parent ID**

If your page belongs to a repository object, set the parent ID of the page to the object ID of your repository object when creating new pages:

```
  $new_page_object = new ...();
  $new_page_object->setParentId($this->object->getId());   // $this->object is an ilObject instance
  ...
```

This will enable the default WAC checking for embedded media objects.


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

A good example for how this can be done is `components/ILIAS/ContentPage/classes/class.ilContentPagePageCommandForwarder.php`.

** Import / Export **

To add pages to the export, you need to add tail dependencies for the COPage component:

```
    public function getXmlExportTailDependencies(
        string $a_entity,
        string $a_target_release,
        array $a_ids
    ): array {
        $deps = [];

        if ($a_entity == "...") {
        
            $your_page_parent_type = "...";
            $pg_ids = [];
        
            // get all page IDs and prefix them with your parent type
            foreach ($a_ids as $obj_id) {
                $pg_ids = array_merge($pg_ids, array_map(static function ($i) {
                    return $your_page_parent_type . ":" $i;
                }, yourFunctionToGetAllPageIds($obj_id));
            }

            $deps = array(
                array(
                    "component" => "components/ILIAS/COPage",
                    "entity" => "pg",
                    "ids" => $pg_ids),
            );

        }

        return $deps;
    }
```

On import you need to add a mapping for your pages. Since your component has to manage the page IDs (COPage will not create these) you need to tell the COPage importer the mapping between import ID and the new ID for the page. This might happen in your importXmlRepresentation() method of your importer class or in importRecord() method when using datasets:

```
    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping): void
    {
        ...
        $your_page_parent_type = "...";
        $a_mapping->addMapping(
            "components/ILIAS/COPage",
            "pg",
            $your_page_parent_type . ":" . $old_id,
            $your_page_parent_type . ":" . $new_id
        );
        ...
    }
```

Since the COPage dependency is a tail dependency, it will run after your import methods and assign the pages with the old import IDs to the new IDs.


### Implementing new page components

Please replace the string `BaseName` and `typeid` with your individual class base name and component type id in the following code.

Add the definition for the new page component to your `module.xml` or `service.xml` file.

```
<copage>
    ...
    <pagecontent pc_type="typeid" name="BaseName" directory="classes" int_links="0" style_classes="0" xsl="0" def_enabled="0" top_item="1" order_nr="300"/>
</copage>
```

Provide a class derived from `ilPageContent`.

```
class ilPCBaseName extends ilPageContent
{
    /**
     * Init page content component.
     */
    public function init()
    {
        ...
        $this->setType("typeid");
        ...
    }
    ...
}
```

Provide a class derived from `ilPageContentGUI`.

```
/**
 * @ilCtrl_isCalledBy ilPCBaseNameGUI: ilPageEditorGUI
 */
class ilPCBaseNameGUI extends ilPageContentGUI
{
    /**
     * Constructor
     */
    public function __construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id = "")
    {
        ...
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }
    ...
}

```

If your `module.xml` or `service.xml` does not enable the component per default `def_enabled="0"`, you will need to enable it in the PageConfig class of your target context.

```
class ilMyPageConfig extends ilPageConfig
{
    public function init()
    {
        $this->setEnablePCType("BaseName", true);
    }
}
```


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

### Text Handling


