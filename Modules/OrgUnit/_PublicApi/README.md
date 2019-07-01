## Public Service
Usage:
```
$orgunit = new OrgUnitUserService();
$orgu_users = $orgunit->getUsers([292,291]);
```
This will return a list of ilOrgUnitUser. The Positions and the Superios were not loaded. You may load them by:
```
foreach($orgu_users as $orgu_user) {
	/**
	 * @var OrgUnit\User\ilOrgUnitUser $user
	 */
	print_r($orgu_user->getSuperiors());
	print_r($orgu_user->getOrgUnitPositions());
}
```

If you know that you will use Superiors or Positions for the whole list. Load it Eager by:
```
$orgunit = new OrgUnitUserService();
$orgu_users = $orgunit->getUsers([292,291],$with_superios = true, $with_positions = true);
```