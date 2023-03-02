# Public Service
## Using Booking Manager in Repository Objects

It is possible to integrate the booking manager as a service into other repository objects, see corresponding [feature wiki entry](https://docu.ilias.de/goto_docu_wiki_wpage_5722_1357.html).

Currently additional features are organised by **ilObjectServiceSettingsGUI**. You need to integrate this into your settings form initialisation and update procedure:

```
ilObjectServiceSettingsGUI::initServiceSettingsForm(
	$this->object->getId(),
	$form,
	array(
	[...],
		ilObjectServiceSettingsGUI::BOOKING
	)
);
```

```
// after $form initialisation
...
ilObjectServiceSettingsGUI::updateServiceSettingsForm(
	$this->object->getId(),
	$form,
	array(
		[...],
		ilObjectServiceSettingsGUI::BOOKING
	)
);
```


Furthermore you need to add a **tab** to your UI which points to the class ilBookingGatewayGUI:

```
$tabs->addTab("booking", $lng->txt("..."),
	$ctrl->getLinkTargetByClass(array("ilbookinggatewaygui"), ""));
```

The same class needs to be integrated in your **executeCommand** control flow:

```
* @ilCtrl_Calls ilYourClassGUI: ilBookingGatewayGUI
```

```
function executeCommand()
{
	...
	$next_class = $this->ctrl->getNextClass($this);
	switch($next_class)
	{
		case "ilbookinggatewaygui":
			...
			$gui = new ilBookingGatewayGUI($this);
			$this->ctrl->forwardCommand($gui);
			break;
	...
```

It is possible to **use the booking manager in a sub-context**, e.g. in a session of a course. The pool selection should only be offered in the course and the session derives these settings from the course. In this case you have to provide the master host ref id (e.g. the course ref id), when creating the instance of ilBookingGatewayGUI within the sub-context (session), e.g.:

```
function executeCommand()
{
	...
	$next_class = $this->ctrl->getNextClass($this);
	switch($next_class)
	{
		case "ilbookinggatewaygui":
			...
			// example: in ilObjSessionGUI we provide the course ref id
			// to define the course as the master host, which also defines the booking
			// pools being used
			$gui = new ilBookingGatewayGUI($this, $course_ref_id);
			$this->ctrl->forwardCommand($gui);
			break;
	...
```

If your repository objects should present the booking information on the **info screen**, add:

```
$info = new ilInfoScreenGUI($this);
$info->enableBookingInfo(true);
```

## JF Decisions

20 May 2019

- [Integrating Booking Manager into Courses](https://docu.ilias.de/goto_docu_wiki_wpage_5722_1357.html)
