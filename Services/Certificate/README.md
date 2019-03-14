# Certificates

Certificates are used to display the users progress of learning
in a special way.
When this feature is activated the user will be able to download
a PDF file that will prove the successful attendance to a given
Module or Service.

This documentation can be used for developers to add the feature
for 'Certificates' to their component.

**Table Of Contents**
* [General](#general)
* [Settings](#settings)
  * [Java Server](#java-server)
  * [Certificate Settings](#certificate-settings)
* [GUI](#gui)
* [Implementation for new Services/Module](#implementation-for-new-services-module)
  * [Placeholder Description](#placeholder-description)
    * [Methods](#methods)
  * [Placeholder Values](#placeholder-values)
    * [Methods](#methods-1)
* [Cron Job](#cron-job)
  * [GUI](#gui-1)
    * [Custom Certificate Settings GUI](#custom-certificate-settings-gui)
  * [User Certificates Classes](#user-certificates-classes)
  * [Template Certificate Classes](#template-certificate-classes)
  * [Cron Queue Classes](#cron-queue-classes)
  * [Actions](#actions)
    * [Copy](#copy)
    * [Delete](#delete)
    * [Preview](#preview)
  * [Events](#events)
    * [updateStatus](#updatestatus)
    * [migrateUserCertificate](#migrateusercertificate)
* [Migration](#migration)
  * [Certificate Templates](#certificate-templates)
  * [User Certificates](#user-certificates)

## General

The certificates and the templates for the certificates will be stored
in the database.

The content of each certificate or template will be stored in the
format:
[XSL-FO](https://en.wikipedia.org/wiki/XSL_Formatting_Objects).

The data that is stored in the database is a projection of the current
status, which means that it MUST not be altered afterwards.
E.g. the name change of a user after achieving the certificate will **not**
automatically be **updated**.

If the ILIAS system will be upgraded from a ILIAS version <=5.3.0
a migration MUST be executed.
See the [migration](#migration) chapter for more information.

Currently only a the newest version of a user certificate will
be shown in the GUI.
Previous certificate are also stored in the database,
but are not displayed in the GUI.

The user certificates can be created directly or via a cron job
with a delay.

* Creating user certificates instantly after resolving the learning progress
  of a user is only recommended for systems with low up to mediocre user workload.
  Due to the fact that learning progress events can be raised for different users
  and different context objects in a very short amount of time
  this can lead to response delays in the GUI.
* Creating user certificates via the cron job is recommended for sytems
  with a high user workload.
  This approach stores an reminder of the learning progress event into a
  queue.
  The cron job will process the queue on execution.
  To avoid high latencies we recommend to execute the cron job in a
  few minute schedule.

## Settings

The feature to create certificate templates and therefore creating
certificates MUST be activated via `Administration -> Certificates`.

A default background image can be added that will be used as default
image for every certificate authority.

Additionally the `Learning Progress` MUST be activated for the Module/Service
to create new user certificates.

### Java Server

To create PDF files from the stored XSL-FO data from the database
a Java server is required to create these PDF files.

A configuration file must be created in the menu
`Administration -> General Settings -> Server(Tab) -> Java-Server(Subtab)`.
Start the server with the configuration file and enter
the required values into the form.

After being started the server is ready to be used
to create PDF files.

### Certificate Settings

The location of the settings to create the certificate template
depends on the implementing Service/Module.

It is recommended to add the certificate settings view for the
current template under the current object in the menu `Settings`
as a sub-tab `Certificate`.

The certificate settings for each component are very similar but
can be modified by the component itself to add new actions or fields.

The biggest differences between all components are the placeholders
that can be used for the component certificate.
How to add these components will be described in the following chapters.

A save of the template will be versioned in the database.
The newest version of the template that was recently saved
and has been set to `Active`
will be used to create the user certificate.

A change to a previous version is currently not supported.

## GUI

The table with persisted user certificates will be displayed
in a separated tab in `Personal Desktop -> Badges`.

The subtab `Certificate` is only visible when certificates
are activates via the [certificate settings](#certificate-settings).

## Implementation for new Services/Module

Beside integrating the settings into the GUI there are two classes that
also MUST be created.

The first class that MUST be added is a `Placeholder Description` class
which contain all the placeholders with the given descriptions.
This class is used in the settings GUI.

The second class that MUST be implemented is a `Placeholder Values` class
which will calculate based on the user and given component the values that
should be replaced for the placeholders.

### Placeholder Description

A `Placeholder Description` class is an implementation of
`ilCertificatePlaceholderDescription`.

This class defines the description for the placeholders and
will create a HTML-View of these parameters that will be displayed in
certificate settings GUI.

#### Methods

This chapter will describe the default methods that MUST be defined
by a new implementation.

```php
ilCertificatePlaceholderDescription::getPlaceholderDescriptions()
```

Will return a associative array with the placeholder as key and
the description as value.

```php
ilCertificatePlaceholderDescription::createPlaceholderHtmlDescription()
```

The method MUST create a HTML string that can be displayed in the settings
GUI.
Templates SHOULD be used to create the HTML string.
A default template can be used which is located in
`./Services/Certificates/default/tpl.default_description.html` 

### Placeholder Values

A `Placeholder Values` class is an implementation of
`ilCertificatePlaceholderValues`.

This class is used to calculate the values for the placeholders
based on the user data and object data.

#### Methods

```php
ilCertificatePlaceholderValues::getPlaceholderValues($userId: integer, $objId: integer)
```

This method will return an associative with the placeholder as key and the actual
data as value.
The value data will be calculated on the method call.
If the values can't be calculated or the user is not permitted to have certificate,
please throw an `ilInvalidCertificateException`.
The [cron job](#cron-job) won't create a user certificate if
this exception is thrown.

```php
ilCertificatePlaceholderValues::getPlaceholderValuesForPreview()
```

This method will return example data that will be used for a preview
PDF version of the certificate.

## Cron Job

A component that has a successful Learning Progress status will be
added to a queue that will be processed on each execution of the
cron job.

The cron job will create an implementation of the `Placeholder Value` class
that is provided in the queue entry to receive the values for the placeholders
and replace them in the XLS-FO content of the template.
The certificate will be stored in the database and will be listed as obtained certificate
in the [GUI](#gui).

### GUI

To implement a certificate settings in a GUI
an instance of `ilCertifcateGUI` MUST be used.
As seen in the constructor of this class, an instance
of `ilCertificateFormRepository` is needed.
The `ilCertificateSettingsFormRepository` can be used
for a default certificate settings interface.

#### Custom Certificate Settings GUI

If a custom certificate settings GUI is needed, a new Repository
class can be created.
This class MUST implement the interface `ilCertificateFormRepository`

### User Certificates Classes

User certificates should be stored in the data container object `ilUserCertificate`.
This container can be given the `ilUserCertificateRepository::save()` method that will
store the certificate in the defined database.

User certificate entries will mainly be created during the cron job based
on the [certificate queue entries](#cron-queue-classes).
Each entry displays an achievement of a certificate.

Example:

```php
// Refers to already used certificate template
$patternCertificateId = 10;

// Object ID of the Module/Service (e.g. Course, Test)
$objId                = 200;

// Type of the current object
$objType              = 'crs';

// User who achieved the certificate
$userId               = 3000;

// Current name of the User
$userName             = 'Ilyas Homer';

// Timstamp of achievement, if the time is not availble use current timstamp
$acquiredTimestamp    = 1532347101;

// The XLS-FO content, in older version this was the content of the file
$certificateContent   = '<xls-fo> ... </xls-fo>';

// Template values that have been replaced with actual user data
$templateValues       = '{"USER_LOGIN": "ilyas"}';

// Validation date/time. For future purposes(Currently not implemented)
$validUntil           = null;

// Numeric iterative version value of the current certifcate. 
$version              = 1;

// ILIAS version at the time of achievement
$iliasVersion         = 'v5.4.0';

// Determines if the current certifcate is the newest and visible certificate
$currentlyActive      = true;

$certificate = new ilUserCertificate(
	$patternCertificateId,
	$objId,
	$objType,
	$userId,
	$userName,
	$acquiredTimestamp,
	$certificateContent,
	$templateValues,
	$validUntil,
	$version,
	$iliasVersion,
	$currentlyActive,
);

$repository = new ilUserCertificateRepository($database, $logger);
$repository->save($certifcate);
```

### Template Certificate Classes

Certificate templates should be stored in the data container object `ilCertificateTemplate`.
This container can be given the `ilCertificateTemplateRepository::save()` method that will
store the template in the defined database.

These classes are used in the certificate settings GUI during creating the certificate
template.

Example:

```php

$objId                = 200;
$obj_type             = 'crs';
$certificateContent   = '<xls-fo>...</xls-fo>'
$certificateHash      = md5($certificateHash);
$templateValues       = json_encode(array('ID' => 'DESCRIPTION'));
$version              = '2';
$iliasVerion          = 'v5.4.0';
$createdTimestamp     = time();
$currentlyActive      = true;
$backgroundImagePath  = '/data/somone/certifcates/course/200/background_2.jpg';

$template = new ilCertificateTemplate(
	$obj_id,
	$obj_type,
	$certificateContent,
	$certificateHash,
	$templateValues,
	$version,
	$iliasVersion,
	$createdTimestamp,
	$currentlyActive,
	$backgroundImagePath
);

$repository = new ilCertificateTemplateRepository($database);
$repository->save($template);
```

### Cron Queue Classes

Queue entry data should be stored in the data container object `ilCertificateQueueEntry`.
This container can be given the `ilCertificateQueueRepository::save()` method that will
store the template in the defined database.

An entry will be created on an successful LP status change.
Queue entries will later be processed by a  cron job to create [user certificate](#user-certificates-classes).

Example:

```php

$objId = 200;
$userId = 5000;
$adapterClass = 'CoursePlaceholderValues'
$state = ilCronConstants::IN_PROGRESS;

$template = new ilCertificateQueueEntry(
	$objId,
	$userId,
	$adapterClass,
	$state
);

$repository = new ilCertificateQueueRepository($database);
$repository->save($template);
```

### Actions

The actions will be shown on the GUI as buttons.
Developers MAY create an specified action for
their Service/Module. 

#### Copy

The copy action SHOULD be executed during the
copy process of an Service/Module.

#### Delete

The delete action will activated via a button
in the template form.

Custom delete action can be created by implementing
the Interface `ilCertificateDeleteAction`,
but every template form SHOULD use at least
an Implementation of `ilCertificateTemplateDeleteAction`.

The actions will be added via `ilCertificateGUIFactory`

#### Preview

The preview action will create a PDF with default 
values.
This default values of this action are defined in the
implementations of the
[Placeholder Values Classes](#placeholder-values).

### Events

The certificates using the ILIAS Event System to add new
certificates to the cron job queue.
There are a few possible events the certificate service is
listening to:

| Event            | Component            | Explanation                                           |
|------------------|----------------------|-------------------------------------------------------|
| updateStatus     | Services/Tracking    | This event will be thrown by the Learning Progress    |
| migrateUserCertificate | Services/Certificate | These are custom events to add a new user certificate |


#### updateStatus

On an update status event (performed by the Learning Progress)
a possible new user certificate will be added directly to the queue.

If the Learning Progress is globally deactivated, the administrator
can enter particular Modules/Services for Learning Progress.
The course is NOT supported for this behaviour, but Modules/Services
can be added via the certificates template settings UI.
Completing all of the selected events will add the user
into the [queue](#cron-queue-classes).

#### migrateUserCertificate

The `migrateUserCertificate` event can be used to add a complete
user certificate directly to the database.
The certificates that come via this event will use the first
certificate template as reference in the database.
The reason for this is that this event is supposed to be used
by the migration service.
The template for this event will always be the first template
in the database.

Example to use this event:

```php
global $DIC;
$ilAppEventHandler = $DIC['ilAppEventHandler'];

$ilAppEventHandler->raise(
   'Services/Certificate',
   'migrateUserCertificate',
   array(
      'obj_id'                => $object->getId(),
      'user_id'               => $user->getId(),
      'background_image_path' => $backgroundImagePath,
      'acquired_timestamp'    => $acquiredTimestamp,
      'ilias_version'         => ILIAS_VERSION_NUMERIC
   )
);

// 'obj_id' - MUST be the object ID the certificate creator(e.g. course, test, ...)
// 'user_id' - MUST be the user ID of the actual user
// 'background_image_path' - relative path to the background image (without the web 
//                           directories eg. 'course/certificates/282/background.jpg')
// 'aquired_timestamp' - Timestamp at the time of achieving the certificate,
//                       could be creation date of the file
// ilias_version - ILIAS version at the time this event will be emitted
```

## Migration

Because persisting certificates where not available until
ILIAS 5.4.0 the old user certificates and templates MUST
be migrated to the above described behaviour.

### Certificate Templates

Certificate templates will be imported to the database
during an database update step.

This step **MUST** be executed before a user migrates
the achieved certificates.

### User Certificates

User certificates will be imported per user via a background
task.
This background task, will be executed by the user via the
[GUI](#gui).
