### Changelog Service

The changelog service provides a simple interface to log certain changes in ILIAS. Its responsibilities are:
* writing changes (provided by a consumer) to a storage
* reading and delivering those changes back to a consumer
 
 The service's architecture aims to be easily usable and extendable by any component or plugin.

## Usage

### Event Classes

Each loggable change has to be represented by a class implementing the Interface ILIAS\Changelog\Interfaces\Event. The consumers are able to and responsible for implementing those event classes. An event is represented by the following fields:

* *Event ID* (String)
    * randomly generated UUID
    * generated automatically when logging the event
* *Event Name* (String)
    * globally unique name 
    * written in snake_case (e.g. *changelog_activated*)
* *ILIAS Component* (String)
    * name of the consumers component (e.g. *Services/Membership*)
* *Actor User ID* (Integer)
    * ID of the user initiating the change event
    * may be 0 (e.g. for system initiated actions) 
* *Subject User ID* (Integer)
    * ID of the user affected by the change event
    * may be 0 (e.g. for *changelog_activated*)
* *Subject Obj ID* (Integer)
    * Obj ID of the involved object (e.g. a course)
    * may be 0
* *Additional Data* (Array)
    * Array of additional data to be logged
    * may be empty
* *Timestamp* (Integer)
    * Unix Timestamp of the moment the event occured
    * generated automatically when logging the event
    
### Implementing A New Event

1. Note that all fields except for the Event ID and the Timestamp have to be implemented and provided by the consumer. This can be done by implementing the given methods in the Interface ILIAS\Changelog\Interfaces\Event. 

2. It's highly recommended to implement the constructor of an event to request all necessary parameters. Use setter (or "with"-) methods only for optional parameters. 

3. The class name should be the event name in camelCase.

4. All classes implementing the Event interface should be located in a dedicated subfolder of the consuming component. E.g.: place membership event in *Services/Membership/src/ChangelogEvents*.

For an example, please have a look at ./src/Changelog/Events/Changelog/ChangelogActivated.php


### Logging

Once an event is implemented it can be logged by using the ILIAS\Changelog\ChangelogService. Simply pass the event to the service's logEvent method:

```php
$changelogService->logEvent(
    new ChangelogActivated($DIC->user()->getId())
);
```

### Querying

Fetching the already logged events can also be achieved via the ChangelogService, by using its *query* method. The *ChangelogService::query()* method accepts two objects: 
1. *Filter*: As the name implies, used to filter the logged events by any of the given fields. 
2. *Options*: Used to set query options like *limit* or *orderDirection*

To produce those, fetch the QueryFactory via *ChangelogService::queryFactory()* and call its *filter()* or *options()* method. Set the desired filters and options by using their *with..* methods.

Then pass the filter and options to *ChangelogService::query()* to receive an array of ILIAS\Changelog\Query\EventDTO objects:

```php
$result = $changelogService->query(
    $changelogService->queryFactory()->filter()->withActorUserIds([$DIC->user()->getId()]),
    $changelogService->queryFactory()->options()->withOrderByTimestamp()
);

var_dump($result);

// result
 array (size=2)
   'fdd906bc-1810-46a4-a9ae-6261b6f15a6f' => 
     object(ILIAS\Changelog\Query\EventDTO)[474]
       protected 'id' => int 3
       protected 'event_id' => string 'fdd906bc-1810-46a4-a9ae-6261b6f15a6f' (length=36)
       protected 'event_name' => string 'changelog_activated' (length=19)
       protected 'actor_user_id' => int 6
       protected 'subject_user_id' => int 0
       protected 'subject_obj_id' => int 0
       protected 'ilias_component' => string 'Services/Changelog' (length=18)
       protected 'additional_data' => 
         array (size=0)
           empty
       protected 'timestamp' => int 1566369617
   'a2fce894-5b5d-438f-8363-45d6d3fcc7a5' => 
     object(ILIAS\Changelog\Query\EventDTO)[475]
       protected 'id' => int 5
       protected 'event_id' => string 'a2fce894-5b5d-438f-8363-45d6d3fcc7a5' (length=36)
       protected 'event_name' => string 'request_accepted' (length=16)
       protected 'actor_user_id' => int 6
       protected 'subject_user_id' => int 289
       protected 'subject_obj_id' => int 285
       protected 'ilias_component' => string 'Services/Membership' (length=19)
       protected 'additional_data' => 
         array (size=0)
           empty
       protected 'timestamp' => int 1566369617
```

Note: EventDTOs can be converted into an array by calling *EventDTO::__toArray()*.