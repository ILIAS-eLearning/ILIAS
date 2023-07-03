# User Data
## Access Data of the User Currently Logged in

An instance of class `ilObjUser` for the user currently logged in ILIAS is available through the DI-Container. You can use the public methods of this class to access the data of the current user (see `Services/User/classes/class.ilObjUser.php`):

```php
function foo()
{
	global $ilUser;
	[...]
	$user_id = $ilUser->getId();
	$user_firstname = $ilUser->getFirstname();
	$user_lastname = $ilUser->getLastname();
	[...]
}
```

## Standard User Name Presentation

The class `ilUserUtil` provides a static method called `getNamePresentation(...)` that should be used to display user first and last name whenever possible.

- The login is always displayed as [login]
- First and last name are only displayed if there is a public profile (this can be overridden by parameter `$a_force_first_lastname`)
- Optionally the user image can be included
- Optionally a link to the public profile of the user can be included

```php
$this->tpl->setVariable("TXT_USER",
	ilUserUtil::getNamePresentation($user_id, true, true, $back_link));
```