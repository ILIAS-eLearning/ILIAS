###Usage

```php
use ILIAS\Changelog\ChangelogService;
use ILIAS\Changelog\Membership\Events\MembershipRequested;

$changelogService = new changelogService();
$event = new MembershipRequested($crs_obj_id, $requesting_user_id);
$changelogService->logEvent($event);
```