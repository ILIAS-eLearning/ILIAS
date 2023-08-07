# Using Content Styles

Content styles are used for page editor content. Repository objects can use their own content style.

## Integrating content style settings

The GUI class providing the style settings for an object is called `ilObjectContentStyleSettingsGUI`. An instance for a repository object is retrieved via `$DIC->contentStyle()->gui()->objectSettingsGUIForRefId(...)`.

Here is an example how to integrate this GUI class in tabs. This is usally used, if the repository item has multiple pages and provides on location for the style settings (e.g. learning modules, wiki, ...).

```php

public function __construct()
{
    $this->content_style_gui = $DIC->contentStyle()->gui();
}

protected function setSettingsSubTabs(): void
{
    $this->tabs_gui->addSubTab(
        "style",
        $this->lng->txt("obj_sty"),
        $this->ctrl->getLinkTargetByClass("ilobjectcontentstylesettingsgui", "")
    );
}

```

If a repository object has only one page, the settings can be integrated into the derived page GUI class directly, to extend the page editor internal action dropdown. This alternative is used e.g. for container pages:

```php
class ilContainerPageGUI extends ilPageObjectGUI
{
    public function getAdditionalPageActions(): array
    {
        $items[] = $ui->factory()->link()->standard(
            $lng->txt("obj_sty"),
            $ctrl->getLinkTargetByClass([
                ilRepositoryGUI::class,
                "ilObj" . $class . "GUI",
                ilObjectContentStyleSettingsGUI::class
            ], "")
        );
        return $items;
    }
}
```

Either way you need to forward commands in your executeCommand method.

```php

/**
 * @ilCtrl_Calls ilObj...GUI: ilObjectContentStyleSettingsGUI
 */
 
public function __construct()
{
    $this->content_style_gui = $DIC->contentStyle()->gui();
}

public function executeCommand(): void
{
    switch ($next_class) {
        case strtolower(ilObjectContentStyleSettingsGUI::class):
             $settings_gui = $this->content_style_gui
                        ->objectSettingsGUIForRefId(
                            null,
                            $this->object->getRefId()
                        );
            $this->ctrl->forwardCommand($settings_gui);
            break;
    }
}

```



## Add content style css to the output

This will add the correct content css to the output and take global settings like global default or globally forced content styles into account.

```php

$this->content_style_gui = $DIC->contentStyle()->gui();

$main_tpl = $DIC->ui()->mainTemplate();

$this->content_style_gui->addCss(
    $main_tpl,
    $this->object->getRefId()
);

```

## Getting the effective style ID

The effective style ID for a repository object depends on the local and global settings. Global default or globally forced content styles are taken into account.

```php

$content_style_domain = $DIC->contentStyle()->domain();
$effective_style_id = $content_style_domain->styleForRefId($this->object->getRefId())->getEffectiveStyleId();

```

## Cloning

When a repository object is cloned, the content style settings need to be cloned as well.

```php

$content_style_domain = $DIC->contentStyle()->domain();
$content_style_domain->styleForRefId($this->object->getRefId())->cloneTo($new_obj->getId());

```

## Exporting

In your Exporter class add a tail dependency for the "sty" entity of the style service. 

```php

public function init(): void
{
    global $DIC;
    ...
    $this->content_style_domain = $DIC
        ->contentStyle()
        ->domain();
}

public function getXmlExportTailDependencies(
    string $a_entity,
    string $a_target_release,
    array $a_ids
): array {
    $res = array();

    ...

    $style_ids = array();
    foreach ($a_ids as $id) {
        $style_id = $this->content_style_domain->styleForObjId((int) $id)->getStyleId();
        if ($style_id > 0) {
            $style_ids[] = $style_id;
        }
    }
    if (count($style_ids)) {
        $res[] = array(
            "component" => "Services/Style",
            "entity" => "sty",
            "ids" => $style_ids
        );
    }

    ...

    return $res;
}


```
