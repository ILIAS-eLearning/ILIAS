# ilCtrl UI Controller

Each request in ILIAS involves a number of classes. User interface and application classes. A global instance of the UI controller `$DIC->ctrl()` manages the main **control flow** through the user **interface layer**.
 
Each request in ILIAS comes with an action that should be performed,

- a **command**,
- and a class that is ultimatively responsible to handle this command, the **command class**.
- Additionally there is a **base class** that is the entry point of the request handling.

Many times you will find these three values in the URL of ILIAS requests. The parameters that are used are `cmd`, `cmdClass` and `baseClass`. If these parameters are not explicitely part of the URL, ILIAS usually determines them by other means during the request.

GUI Layer control flow:

```
+----------------+
| Base GUI Class |
+----------------+
        |           +---------------+
        |---------->|   GUI Class   |
        |           +---------------+
        |                  |           +-------------+
        |                  |---------->|     ...     |
        |                  |           +-------------+
        |                  |                  |           +-------------------+
        |                  |                  |---------->| Command GUI class |
        |                  |                  |           +-------------------+
        |                  |                  |                     |
        |                  |                  |                     |
        |                  |                  |<--------------------|
        |                  |                  |
        |                  |<-----------------|
        |                  |
        |<-----------------|
        |
        |
```

The main purpose of `$ilCtrl` is to support re-use of components that implement part of the user interface. The following picture tries to illustrate this fact. The features provided by `GUI B` are reused in the contexts of `GUI A` and `GUI C`.

```
+-------+
| GUI A |
+-------+
    |           +-------+
    |---------->| GUI B |
    |           +-------+
    |               |
    |<--------------|
    |

+-------+
| GUI C |
+-------+
    |           +-------+
    |---------->| GUI B |
    |           +-------+
    |               |
    |<--------------|
    |

```
**Example**: ILIAS provides a LOM metadata editor. This is re-used in different contexts, thus it could be the `GUI B` in the picture above. `GUI A` and `GUI C` could be e.g. the learning module editor, or the course component. Both of them re-use the metadata editor.

The $ilCtrl provides the following features:

- It **manages the flow of control** in the GUI layer. This means "it knows" which classes should be involved in what sequence for a given request.
- It allows the GUI classes involved to **save and set parameters between requests**. Saving in this case means: Keep the value of the current request for the subsequent request. Typical important parameters that components rely on are the ref_id or the obj_id of objects.
- It **generates href** attributes of **links** and **action attributes** of **forms**. These attributes define the "next request", when a links is clicked or a submit button is pressed within a form.

## Using ilCtrl

To be able to provide its functionality `ilCtrl` needs some help of the GUI classes. First of all they must declare their call dependencies, i.e. if a GUI class `ilFooGUI` uses (forwards the control flow) to a GUI class ilBarGUI, either

- `ilFooGUI` declares that it calls `ilBarGUI` by using the **$ilCtrl_Calls** declaration or
- `ilBarGUI` declares that it is called by `ilFooGUI` **$ilCtrl_isCalledBy** declaration

```
/**
 * GUI class ilFooGUI
 *
 * Identify subclasses that are called by the current class:
 *
 * @ilCtrl_Calls ilFooGUI: ilBarGUI (multiple classes can be separated by comma)
 */
class ilFooGUI
{
	[...]
}
```
or
```
/**
 * GUI class ilBarGUI
 *
 * Identify classes that call the current class:
 *
 * @ilCtrl_isCalledBy ilBarGUI: ilFooGUI (multiple classes can be separated by comma)
 */
class ilBarGUI
{
	[...]
}
```

Everytime you add, modify or delete these declarations, you need to add a special **step** to the ILIAS database update script located under `setup/sql`:

```
<#STEP-ID>
<?php
    $ilCtrlStructureReader->getStructure();
?>
```

The next step is that the calling class `ilFooGUI` implements a function called `executeCommand()`.

```
class ilFooGUI
{
	[...]
	function executeCommand()
	{
		global $DIC;
		
		$ctrl = $DIC->ctrl();
 
		// determine next class in the call structure
		$next_class = $ctrl->getNextClass($this);
 
		switch($next_class)
		{
			// if another class is responsible to process current
			// command, forward the process of control to the next class
			case "ilbargui":
				$bar_gui = new ilBarGUI(...);
				$ret = $ctrl->forwardCommand($bar_gui);
				break;
			[...]				
			// process command, if current class is responsible to do so
			default:
				$cmd = $ctrl->getCmd();
				$this->$cmd();
				break;
		}
	}
	[...]
}
```

- `getNextClass()` determines the **next class in the control flow**. If this is not the current class, you need to instantiate the next class and forward the control flow to this class. Please note that the class name returned is all lower case.
- `forwardCommand()` **forwards** the control flow to the next responsible class.
- `getCmd()` returns the **action command** that should be performed.

### Saving parameters between and setting parameters for requests

To preserve the value of a GET parameter for the subsequent request, use `saveParameter()` or `saveParameterByClass()`.

```
class ilFooGUI
{
	[...]
	function __construct(...)
	{
		global $DIC;
		
		$ctrl = $DIC->ctrl();
		[...]
		// save ref_id for next request (if current class is involved)
		$ctrl->saveParameter($this, array("ref_id"));
		$ctrl->saveParameterByClass("ilbargui", array("obj_id"));
		[...]
	}
	[...]
}
```
To set concrete values for GET parameters us `setParameter()` or `setParameterByClass()`.
```
[...]
$ctrl->setParameter($this, "obj_id", $obj_id);
[...]
$ctrl->setParameterByClass("ilbargui", "obj_id", $obj_id);
[...]
```

### Getting link href attributes or form action attributes

Many times you need `$ilCtrl` to generate the **href** attribute for a link. Before doing so you need to ensure that all parameters that should be included as GET parameters in the request are either set by using `setParameter()` or have been saved by using `saveParameter()`.

Links refer always to commands that are performed by a GUI class. If the command should be performed by the current GUI class, use `getLinkTarget($this, $cmd)`, where $cmd contains the command that should be performed as a string, usually this is a method name of the current GUI class. If the link should execute a command of another GUI class use `getLinkTargetByClass("class_name", $cmd)`.

```
[...]
 
// generate a link to a method of the current GUI class
$tpl->setVariable("LINK_HREF", $ctrl->getLinkTarget($this, "view"));
 
[...]
 
// ... or to another GUI class
$tpl->setVariable("LINK_HREF", $ctrl->getLinkTargetByClass("ilbargui", "view"));
 
[...]
 
// generate a form action attribute for the current gui class
$tpl->setVariable("ACTION", $ctrl->getFormAction($this))
 
[...]
 
// ... or for another gui class (rarely used)
$tpl->setVariable("ACTION", $ctrl->getFormActionByClass("ilbargui"));
```

### Performing Redirects

Often you need to perform HTTP redirects to commands of either the current GUI class or to commands of other GUI classes. This can be done by `redirect()` or `redirectByClass()`.

```
// redirect to the "listItems" command of the current GUI class
$ctrl->redirect($this, "listItems");
 
// redirect to "editItem" command of class ilBarGUI
$ctrl->redirectByClass("ilbargui", "editItem");
```
A special case is a redirect to an upper context of a component. Think of the example of the metadata editor above. The editor does not know in which contexts it runs (e.g. the learning module editor or the course component). But it may want to provide an "exit" procedure that should quit the metadata editor and return to some feasible point in the upper context.

Two things are needed for this:

- The upper context must define the target redirect command for the components underneath using `setReturn()`.
- The lower context must call `returnToParent()` to redirect to the command specified by the upper context.

```
class ilUpperGUI
{
	[...]
	// set return method for lower classes
	$ctrl->setReturn($this,  "view");
	[...]
	// call ilLowerGUI
	$low_gui = new ilLowerGUI(...);
	$ret = $ctrl->forwardCommand($low_gui);
	[...]
}
```

```
class ilLowerGUI
{
	[...]
	// redirect to upper context
	$ctrl->returnToParent($this);
	[...]
}
```

### Declaring base classes

Base classes must be declared in `the modules.xml` or `service.xml` files of components.

```
<baseclasses>
    <baseclass name="ilMyBaseClassGUI" dir="classes" />
</baseclasses>
```

### How ilCtrl works in detail

If `getLinkTargetByClass($class_name, ...)` is called `ilCtrl` has to determine a new control flow path to the requested class. `ilCtrl` will search the control flow tree using the following algorithm.

Sitation: We are currently in

`A_GUI -> B_GUI -> C_GUI`

and the command flow has already been forwarded to C_GUI. C_GUI is now calling `getLinkTargetByClass($class_name, ...)`.

- **Same class**: if `$class_name` is `C_GUI`, the current path will be used for the link (resulting in `A_GUI -> B_GUI -> C_GUI`).
- **Children**: if `$class_name` is a child of `C_GUI`, the new path will be `A_GUI -> B_GUI -> C_GUI -> $class_name`.
- **Sibling**: if `$class_name` is a sibling of `C_GUI`, the new path will be `A_GUI -> B_GUI -> $class_name`.
- **Ancestor**: if `$class_name` is an ancestor of `C_GUI`, e.g. `B_GUI` the new path will be `A_GUI -> B_GUI`.
- **Base class**: if `$class_name` is another base class, the new path will be `$class_name`.

So any link to the same class, a children class, an ancestor or another base class will only need one class name as an attribute.

### Using arrays as target class parameter

For more complex scenarios it is also possible to specify an array as a target for the next command. In this case `ilCtrl` will take the first class in the array and perform the algorithm above. Afterwards it repeats the same procedure again for every additional class, starting from the latest determined path.

#### Linking to a grandchild

Situation: We are currently in

`A_GUI -> B_GUI -> C_GUI`

We want to provide a link to a "grandchild" of `C_GUI`:

`A_GUI -> B_GUI -> C_GUI -> D_GUI -> E_GUI`.

We can do so within `C_GUI` by calling `getLinkTargetByClass(['D_GUI', 'E_GUI'], ...)`.

In this case `ilCtrl` will start with the current path `A_GUI -> B_GUI -> C_GUI` and identify `D_GUI` as a child of `C_GUI`, so the updated path will be `A_GUI -> B_GUI -> C_GUI -> D_GUI`. Now it will identify `E_GUI` as a child of `D_GUI` which will result in the final path `A_GUI -> B_GUI -> C_GUI -> D_GUI -> E_GUI`.

#### Linking into a structure of another base class

Situation: We are currently in

`A_GUI -> B_GUI -> C_GUI`

Now we want to link into a complete different context, e.g.

`F_GUI -> G_GUI -> H_GUI`

We can do so within `C_GUI` by calling `getLinkTargetByClass(['F_GUI', 'G_GUI', 'H_GUI'], ...)`.

In this case `ilCtrl` will first look for `F_GUI`. `F_GUI` is neither a child, sibling or ancestor of `C_GUI`. But `F_GUI` is a base class, so the updated path will be `F_GUI`. Next `G_GUI` will be identified as a child of `F_GUI` making `F_GUI -> G_GUI` the updated path. In the last step `H_GUI` will be identified as a child of `G_GUI` which will result in the final path `F_GUI -> G_GUI -> H_GUI`.

### How to quickly identify the current control flow path

If you are a developer, you may want to know the control flow path of a certain screen. The procedure to get this information depends on your ILIAS version. Up to ILIAS 5.4.x you need to activate the **development mode** in your `client.ini.php`.

```
[system]
...
DEVMODE = "1"
```

Now you will see the ilCtrl path information in the footer of ILIAS. All lines including "execComm" will mention the GUI classes being involved in the current command.

From 6.x on this feature has been removed from the core. There is a plugin available at https://github.com/leifos-gmbh/LfDevTool that presents the same information in a metabar slate.

### Integrate UI elements using $ilCtrl->getHTML()

All cases discussed so far assume that a target GUI class performs the current command and is responsible for the output of the screen.

There are cases where a GUI class wants to merge a number of HTML snippets from sub GUI classes, without these classes performing a command (in the current request). E.g. the ILIAS 6 dashboard needs to collect parts from other GUI classes in its main "show" command.

Similar to `$ilCtrl->forwardCommand($childGUI) > $childGUI->executeCommand()` a child class may be called by `$ilCtrl->getHTML($childGUI); -> $childGUI->getHTML();`. In the second case the child GUI class does not perform the current command, it just returns a HTML snippet to the parent. However since `ilCtrl` is aware of the child performing its `getHTML` method, the child may use `ilCtrl` within this method in the usual way. `ilCtrl` will update the current control flow path adding the child GUI class when the method is called and reset the path to the parents GUI class, once the child `getHTML()` method is finished.

## Listen to ilCtrl Events
Components can be notified about events in ilCtrl, this is done according to the observer pattern. ilCtrl acts as a subject and informs about the following events (see enum `ilCtrlEvent`):

- ilCtrlEvent::COMMAND_CLASS_FORWARD: ilCtrl forwards the request to the next command class (including base class). The class name of the class is passed as parameter `$data`.
- ilCtrlEvent::COMMAND_DETERMINATION: ilCtrl is requested via `getCmd()` for the current command and issues the determined command. The event is broadcast with the determined command as parameter `$data`.

As `ilCtrlObserver` one can react to these events or use this information. Use cases are e.g.

- Generating a screen ID for the online help
- Output call stack in DEV mode, e.g. in the footer

> In a observer, the request MUST NOT be changed under any circumstances (e.g. by redirecting or by aborting the request), no exceptions MUST BE thrown and nothing MUST be done that changes the control flow.
