## Public Service
Usage:
```
$orgunit_user_spec = new OrgUnitUserSpecification([6,291,292]);
$orgunit_service = new OrgUnitUserService($orgunit_user_spec);

foreach($orgunit_service->getUsers() as $user) {
	/**
	 * @var OrgUnit\User\ilOrgUnitUser $user
	 */
	print_r($user->getSuperiors());
	print_r($user->getOrgUnitPositions());

	$users[] = $user;
}