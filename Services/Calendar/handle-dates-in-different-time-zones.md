> This documentation does not warrant completeness or correctness, and is probably outdated. Reports of
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contributions via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories)
are greatly appreciated.

# How to handle dates in different time zones

With release 3.10.0 ILIAS introduces support for individual user time zones. On the presentation, side dates should always be displayed in the time zone of the currently logged in user. With the revision of the calendar in 3.10.0, new classes for the manipulation and presentation of dates have been introduced.

## PHP Basics

There are three interesting levels of time:

1. The Server Time Zone
2. The "Coordinated Universal Time" / UTC (which is more or less GMT)
3. The User Time Zone

Working with PHP usually brings you in contact with times in the server time zone and, if you work with unix timestamps, implicitely with UTC.

**Example 1**: Getting current unix timestamp (which are seconds from 1.1.1970 in UTC)

```php
// will display something like 1216474062
echo time();
```

**Example 2**: Getting the current server time zone (you will rarely need this).

```php
// show current timezone, e.g. "Europe/Berlin"
echo date_default_timezone_get();
```

**Example 3**: Using `date()` to format a date/time value. The PHP date function uses a (UTC) unix timestamp as input and delivers the date/time in the current server time zone.

```php
// displays something like 2008-07-19 15:27:42 (current server time zone)
echo date('Y-m-d H:i:s', time());
```

**Example 4**: The opposite function of `date()` is `mktime()`, which delivers a (UTC) unix timestamp, based on date/time values provided in the current server time zone.

```php
// get current hour from date (server time zone) and using the hour in mktime
// -> mktime input parameters must be in server time zone
$server_hour = date('H', time());
echo mktime($server_hour);
```

> Up to ILIAS 3.9.x all string representations ("YYYY-MM-DD HH:MM:SS") are in server time zone, also when working with MySQL timestamps. Only integer representated unix timestamps (like delivered by `time()`) are seconds in UTC since 1.1.1970.

With version 3.10.0 ILIAS displays dates in a user defined time zone. Every user can choose the time zone in his or her personal settings.

> Dates in the format of the **user defined time zone** are **only used in the user interface**. They must not be saved to the database.

## Using ilDateTime objects in ILIAS

1. **Creating date objects**:

```php
include_once('./Services/Calendar/classes/class.ilDate.php');
     
// Creating a DateTime object (from unix timestamp)
$now = new ilDateTime(time(),IL_CAL_UNIX);
     
// Creating a DateTime object (from string in server time zone)
$date = new ilDateTime('2008-07-17 10:00:00',IL_CAL_DATETIME);
     
// Creating a Date object (no time, no time zone conversion)
$birthday = new ilDate('1972-04-04',IL_CAL_DATE);
```

2. **Manipulating dates**:

```php
include_once('./Services/Calendar/classes/class.ilDate.php');
     
$days = new ilDateTime(time(),IL_CAL_UNIX);
     
// plus one hour
    $days->increment(IL_CAL_HOUR,1);
     
// minus two days
$days->increment(IL_CAL_DAY,-2);
     
// plus three months
$days->increment(IL_CAL_MONTH,3);
     
...
```

3. **Comparing dates**:

```php
include_once('./Services/Calendar/classes/class.ilDate.php');
     
$early = new ilDateTime('2008-07-17 06:00:00',IL_CAL_DATETIME);
$late = new ilDateTime('2008-07-17 23:00:00',IL_CAL_DATETIME);
     
// Check equality => returns false
ilDateTime::_equals($early,$late);
     
// Check same day => returns true
ilDateTime::_equals($early,$late,IL_CAL_DAY);
     
// Check $early < $late
ilDateTime::_before($early,$late);
     
// Check $early > $late
ilDateTime::_after($early,$late);

...
```

4. **Converting date formats**:

```php
include_once('./Services/Calendar/classes/class.ilDate.php');
     
// Creating object from date/time string representation in server time zone
$today = new ilDateTime('2008-07-17 12:00:00',IL_CAL_DATETIME);
     
// Convert to unix timestamp
$unix = $today->get(IL_CAL_UNIX);
     
// Convert to custom format in server time zone (PHP date syntax)
$date_str = $today->get(IL_CAL_FKT_DATE,'YmdHis');
     
// Convert to datetime in UTC time zone (usually you will not need this)
$utc = $today->get(IL_CAL_DATETIME,'','UTC');
```

## Storing dates in the database

MySQL offers mainly three possibilities for storing dates in the database. Timestamp, DateTime or Integer Fields. Timestamp and DateTime are very similar to use, even if Timestamp internally converts to UTC and DateTime does not. With ILIAS 3.10.0 ILIAS uses the PEAR::MDB2 database abstraction layer. PEAR::MDB2 can handle the types date, time and timestamp. The MDB2 timestamp type is mapped to the MySQL datetime type, if the MDB2 `createTable()` statement is used.

1. **Using MDB2 field type "timestamp"**:

You provide a normal string representation in server time zone (YYYY-MM-DD HH:MM:SS). When reading you will also get a string representation in server time zone.

```php
// Update/Insert
$registration_start = new ilDateTime(time(),IL_CAL_UNIX);
$db->manipulate("UPDATE grp_settings SET ".
		"  registration_start = ".$db->quote($start->get(IL_CAL_DATETIME), "timestamp").
		" WHERE obj_id = ".$db->quote(123, "integer")
	);
     
// Select
$set = $db->query("SELECT * FROM grp_settings ");
while ($record = $db->fetchAssoc($set))
{
	$registration_start = new ilDateTime($record['registration_start'],IL_CAL_DATETIME);
}
```

2. **Using MDB2 field type "integer" (for unix timestamps)**:

The (UTC) unix timestamps are just put into an integer field.

```php
// Update/Insert
$registration_start = new ilDateTime('2008-11-11 11:11:11' ,IL_CAL_DATETIME);
$db->manipulate("UPDATE grp_settings SET ".
		"  registration_start = ".$db->quote($start->get(IL_CAL_UNIX), "integer").
		" WHERE obj_id = ".$db->quote(123, "integer")
	);
     
// Select
$set = $db->query("SELECT * FROM grp_settings");
while ($record = $db->fetchAssoc($set))
{
    $registration_start = new ilDateTime($record['registration_start'], IL_CAL_UNIX);
}
```

## Date Presentation
For the presentation of dates in the user interface, the individually selected user time zone must be respected. This is done by the `ilDatePresentation` class.

1. **Presentation of dates (without time)**:

```php
ilDatePresentation::formatDate(
   new ilDate(time(),IL_CAL_UNIX));
     
// Returns: 
// 12. Jul 2008
```

2. **Presentation of dates (with time)**:

```php
ilDatePresentation::formatDate(
    new ilDateTime(time(),IL_CAL_UNIX));
     
// Returns:
// 12. Jul 2008 1:01pm or
// 12. Jul 2008 13:01
// depending on the user time zone and hour format setting.
```

3. **Presentation of date periods**:

```php
ilDatePresentation::formatPeriod(
    new ilDateTime(time(),IL_CAL_UNIX),
    new ilDateTime(time()+7200,IL_CAL_UNIX));
     
// Returns:
// 17. Jul 2008 1:00 pm - 3:00 pm or
// 17. Jul 2008 23:00 - 18. Jul 2008 01:00
// Depending on the user time zone and hour format settings.
```

## Editing dates using Property Forms

For date/time form fields, please use the `ilDateTimeInputGUI` property form class. The easiest way to get the entered values from the form is to use a combination of `getItemByPostVar()` and `getDate()` (alternative 1). If you use `getInput()` (alternative 2) the values will be in the user time zone and must be converted by providing the user time zone to the `ilDateTime` constructor.

```php
// Show date input
$dt_prop = new ilDateTimeInputGUI("Anmeldungsbeginn", "registration_start");
$dt_prop->setDate(new ilDateTime("2006-12-24 15:44:00",IL_CAL_DATETIME);
$dt_prop->setShowTime(true);
$dt_prop->setInfo("Info text for the start date.");
$form_gui->addItem($dt_prop);
 
...
// Save date
if ($form_gui->checkInput())
{
    // alternative one: using getItemByPostVar() and getDate()
    $date = $form_gui->getItemByPostVar("registration_start")->getDate();   // returns an ilDateTime object
 
    // alternative two: using getInput. IMPORTANT: this returns the values in user time zone
    $b = $form_gui->getInput("registration_start");
    $date = new ilDateTime($b['date']." ".$b['time'], IL_CAL_DATETIME, $ilUser->getTimeZone()));
}
```
