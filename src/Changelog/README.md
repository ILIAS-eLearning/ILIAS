###Changelog Service

The changelog service provides a simple interface to log certain changes in ILIAS. In this first version, the service only provides the logging of course membership events, like 'MembershipRequested' or 'AddedToCourse', which will be stored in the database. But the service's architecture aims to be easily extendable to other components and even allow a developer to write events to different storages.

##Usage
###Logging
All loggable Events can be found in the namespace ILIAS\Changelog\Events (with a subnamespace for each component). An Event is a simple Data Class which demands all required parameters in its constructor and cannot be changed afterwards. The Changelog Service Class accepts any event in a single 'logEvent' method which will forward the event to the corresponding handler.

Therefore, the logging processing consists of the two easy steps of constructing an event and passing it to the Changelog Service. 

#####Example:

```php
use ILIAS\Changelog\ChangelogService;
use ILIAS\Changelog\Events\Membership\MembershipRequested;

$changelogService = new ChangelogService();
$event = new MembershipRequested($crs_obj_id, $member_user_id);
$changelogService->logEvent($event);
```

###Querying
The query services are also accessible via the central changelogService, while there will be a separate service for each component (at the moment there's only the 'membership' service). So first, fetch the desired service:

```php
$membershipQueryService = $changelogService->query()->membership();
```
The query services provide a method for every executable query, while every method expects a certain 'Request' object as parameter and returns a certain 'Response' object. E.g.:
```php
/**
 * @param getLogsOfUserRequest $getLogsOfUsersRequest
 * @return getLogsOfUserResponse
 */
public function getLogsOfUser(getLogsOfUserRequest $getLogsOfUsersRequest): getLogsOfUserResponse;
```
So before sending a query, the corresponding request has to be constructed. Each request will demand all mandatory parameters in its constructor, while all optional parameters can be set through a setter. Most requests also provide a filter, which can be accessed via a getFilter() method and can be adjusted via setters.

#####Example:
```php
$request = new getLogsOfCourseRequest($crs_obj_id);
$request->setLimit(10);
$request->setOrderBy('event_type_id');
$request->setOrderDirectionAscending();
$request->getFilter()->setUserId($DIC->user()->getId());
$request->getFilter()->setDateFrom(new ilDateTime('2019-07-11 10:00:00', IL_CAL_DATETIME));
$response = $changelog_service->query()->membership()->getLogsOfCourse($request);
```

###Register additional Loggers
The service's default logger is the ilDBLogger which, as the name implies, stores the events in the ILIAS database. If it should be necessary to log events in some other storage, which could e.g. be a file or another database through some api, a developer can easily implement and register an additional Logger. 

The new Logger needs to extend from the abstract class ILIAS\Changelog\Logger\Logger and implement the method getRepositoryForEvent(Event). This method decides which Repository will be used for which type of event. E.g. it might return a FileMembershipRepository for every Event which is a subclass of ILIAS\Changelog\Events\Membership\MembershipEvent. The FileMembershipRepository would have to be implemented by the developer and would extend from ILIAS\Changelog\Infrastructure\Repository\MembershipRepository. 

To register  an additional Logger, simply call the changelogServices registerLogger() method before logging an event:
```php
$changelogService->registerLogger(new myLogger());
$changelogService->logEvent($event);
```