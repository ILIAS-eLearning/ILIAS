# User Service

Currently the user service is only represented by an ilObjUser object of the current user in the DIC via `$DIC->user()`. However ilObjUser is not a real interface for other components, since it reveals lots of internals that should be hidden, see ROADMAP.

## Starting Point

Business Rules

- The starting point process will be triggered, if a user session starts at the login. If the user browser throught the public area before, ILIAS will keep the users at the reference ID location in the repository. JF decision: https://mantis.ilias.de/view.php?id=30710


## Change Listeners for global User Field Attributes

Consumers could add change listeners for changed attributes (e.g. visbility in user profile) of global user profile
fields.  Change listeners could be added by configuration in the static definition of user
fields: `ilUserProfile::$user_field`.

```php
private static $user_field = [
    // ...
    'second_email' => [
        // ...
        'change_listeners' => [
            ilMailUserFieldChangeListener::class,
        ]]
    // ...
];
```

Each change listener MUST extend the abstract `UserFieldAttributesChangeListener` class.

```php
class MyChangeListener extends UserFieldAttributesChangeListener
{
    public function getDescriptionForField(string $fieldName, string $attribute) : ?string
    {
        if ($fieldName === 'second_email' && $attribute === 'visible_second_email') {
            return 'Dear administration, changed this will lead to ...';
        }

        return null;
    }

    public function getComponentName() : string
    {
        return 'Services/MyComponent';
    }
}
```

If a privledged actor changes one or more attributes of one or more global user fields and at least one listener is
interested in this change, a confirmation dialogue will be presented to the user. After confirming the change and the
consequences provided by the consumers, the event system of ILIAS will be used to emit a system event:

* Component: `Services/User`
* Event: `onUserFieldAttributesChanged`
* Parameters:
  * `array<string, ChangedUserFieldAttribute>`

Other components are able listen to this events and act depending on the provided `ChangedUserFieldAttribute` elements. 