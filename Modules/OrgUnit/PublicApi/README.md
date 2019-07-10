## Public Service
Usage:
```
$orgunit = new OrgUnitUserService();
$orgu_users = $orgunit->getUsers([292,291]);
```
This will return a list of ilOrgUnitUser. The Positions and the Superios can be lazy loaded. 

Position: A position in ILIAS is a possibility to assign users to an organizational unit. For example, a user can be assigned to an organizational unit in the position superior or employee. The positions Employees and Superiors are standard ILIAS positions. Note Additional Positions can be configured arbitrarily in ILIAS.

Superiors: Superiors of a user are those users who are assigned as superiors to the ILIAS organizational unit for which a user has assigned the position Employee.

Position: Eine Position ist in ILIAS eine Zuweisungsmöglichkeit von Benutzern zu einer Organisationseinheit. Zum Beispiel ein Benutzer kann in der Position Vorgesetzter oder Mitarbeiter zu einer Organisationseinheit zugewiesen werden. Hinweis Positionen können in ILIAS beliebig konfiguriert werden.

You may load them by:
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