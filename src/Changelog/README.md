###Usage

Possible Events can be found in the namespace ILIAS\Changelog\Events

```php
use ILIAS\Changelog\ChangelogService;
use ILIAS\Changelog\Events\Membership\MembershipRequested;

$changelogService = new changelogService();
$event = new MembershipRequested($crs_obj_id, $requesting_user_id);
$changelogService->logEvent($event);
```