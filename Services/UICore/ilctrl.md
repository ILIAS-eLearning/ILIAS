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

###Saving parameters between and setting parameters for requests

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

###Getting link href attributes or form action attributes
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

###Performing Redirects
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
