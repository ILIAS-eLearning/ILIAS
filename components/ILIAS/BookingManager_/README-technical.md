# Booking Pool - Technical Documentation

## Service

### Using Booking Manager in Repository Objects

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

*JF Decisions*

20 May 2019

- [Integrating Booking Manager into Courses](https://docu.ilias.de/goto_docu_wiki_wpage_5722_1357.html)


## Entities and Properties

### Booking Pool

A booking pool is the main entity for managing booking objects (resources) and their usage (reservations).

* **Code**: `Modules/BookingManager`
* **DB Tables**: `booking_settings`

#### Properties

* **Fixed Schedule** or **No Schedule, Direct Booking** or **No Schedule, Using Prefences**: There are two main types of booking pools, those which are using schedules (e.g. for booking rooms) and those who don't (e.g. for selection of term paper topics). Booking pools without schedule either allow direct booking, or assign objects by preferences. (`booking_settings.schedule_type`)
* **Public Reservations**: The list of reservations can be made publicly available for all users with read permission. (`booking_settings.public_log`)
* **Overall Limit of Bookings** (No schedule only): Limits the maximum number of bookings a single user can do in this pool. (`booking_settings.ovlimit`)
* **Default Period for Reservation List** (Fixed schedule only): Sets the default period of the filter in the reservation list view. (`booking_settings.rsv_filter_period`)
* **Reminder**: A reminder can be activated (`booking_settings.reminder_status`) to remind users of their upcoming bookings. The period before users are reminded can be set (`booking_settings.reminder_day`). A cronjob stores the timestamp for last execution (`booking_settings.last_remind_ts`).

*Deprecated*

* `booking_settings.slots_no` ?

### Schedules

* **Code**: `Modules/BookingManager/Schedule`
* **DB Tables**: `booking_schedule`, `booking_schedule_slot`

#### Properties

...

### Booking Objects

* **Code**: `Modules/BookingManager/Objects`
* **DB Tables**: `booking_objects`

#### Properties

...

#### File Storage

*Additional Description File*
- `ilBookingManager/book*BOOK_OBJ_ID*/file/*FILENAME*` (web data directory)
- Additional description file as uploaded in the booking object settings

*Post Booking Information File*
- `ilBookingManager/book*BOOK_OBJ_ID*/post/*FILENAME*` (web data directory)
- Information file being presented post booking as uploaded in the booking object settings

### Reservations

* **Code**: `Modules/BookingManager/Reservations`
* **DB Tables**: `booking_reservation`

#### Properties

* **User**: User who 'owns' the reservation. (`booking_reservation.user`)
* **Assigner**: User who created the reservation. A tutor (assigner) may assign a reservation to a student (user who owns the reservation). (`booking_reservation.assigner_id`)
* **Object**: Object that is reserved. (`booking_reservation.object_id`)
* **Reservation Period**: Timestamps that correspond to a concrete instance of a slot of the object schedule. (`booking_reservation.date_from`, `booking_reservation.date_to`)
* **Reservation Status**: Currently `NULL` (reserved) or `ilBookingReservation::STATUS_CANCELLED` (reservation cancelled). (`booking_reservation.status`)
* **Reservation Grouping**: If multiple instances of an object are reserved, each of them will get an entry in the reservation table. They will all share the same internal group ID. (`booking_reservation.group_id`)
* **Context Object**: If a reservation is done within another repository object (e.g. course), this is stored with the reservation. (`booking_reservation.context_obj_id`)

### Participants

* **Code**: `Modules/BookingManager/Participants`
* **DB Tables**: `booking_member`

#### Properties

...
