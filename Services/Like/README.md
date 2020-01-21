# Like Service

The like service allows you to store a set of emoticons for users in relation to an ILIAS **object** (derived from ilObject), **subobject** or **news** related to these objects or subobjects.

## Using the Widget

Due to performance reasons, the widget instance (of type `ilLikeGUI`) will get all data related to a set of ILIAS objects in one step.

```
$f = new ilLikeFactoryGUI();
$like_widget = $f->widget($obj_ids);
```

E.g. if you use the widget for postings (subobjects) of a forum (object) and call `$f->widget([$forum_obj_id])`, the widget will be initialised with all data related to the forum and all of its subobjects (postings) and even news.

To render the widget you first set the ID of the concrete object to be rendered and pass it to `$ctrl->getHTML()` afterwards.

```
// add subobject, news id if needed
$like_widget->setObject($obj_id, $obj_type);
$html = $ctrl->getHTML($like_widget);
```

The widget needs to be part of the `ilCtrl` control flow.

```
 ...
 * @ilCtrl_Calls ilYourClassGUI: ilLikeGUI
 ...
 
	function executeCommand()
	{
		... 
		switch ($next_class)
		{
			case "illikegui":
				...
				// add subobject, news id if needed
				$like_widget->setObject($obj_id, $obj_type);
				$ctrl->forwardCommand($like_gui);
				break;
		}
	}

```


## JF Decisions

23 Oct 2017

- General introduction of the service
  https://docu.ilias.de/goto_docu_wiki_wpage_4698_1357.html
