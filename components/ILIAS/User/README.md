# User Service

Currently the user service is only represented by an ilObjUser object of the
current user in the DIC via `$DIC->user()`. However ilObjUser is not a real
interface for other components, since it reveals lots of internals that should
be hidden, see ROADMAP.

## Starting Point

Business Rules

- The starting point process will be triggered, if a user session starts at the
login. If the user browser throught the public area before, ILIAS will keep the
users at the reference ID location in the repository.
JF decision: https://mantis.ilias.de/view.php?id=30710

## Personal Profile

Camera Input for Profile Picture

- There are some known issues in various iOS mobile version that unfortunately
show inconsistent behaviour.

## Adding Settings
Settings that can be personalised by users are added by implementing
`ILIAS\User\Settings\SettingDefinition`. The component contributing the settings
is responsible for any default values, ie. the system wide values. Any settings
to be added through this mechanism MUST be approved by the Jour Fixe.

## Change Listeners for global User Field Attributes

Each change listener MUST implement the `ILIAS\User\Profile\ChangeListeners\UserFieldAttributesChangeListener`
interface and it needs to be published through the component class of the component.

```php
$contribute[User\Profile\ChangeListeners\UserFieldAttributesChangeListener::class] = fn() =>
    new MyChangeListener();
```

If a privledged actor changes one or more attributes of one or more global user
fields and at least one listener is
interested in this change, a confirmation dialogue will be presented to the user.
After confirming the change and the
consequences provided by the consumers, the event system of ILIAS will be used
to emit a system event:

* Component: `ILIAS/User`
* Event: `onUserFieldAttributesChanged`
* Parameters:
  * `array<string, ILIAS\User\Profile\ChangeListeners\ChangedUserFieldAttribute>`

The array keys will be the value of the enum `ILIAS\User\PropertyAttributes`.

Other components are able listen to this events and act depending on the provided
`ChangedUserFieldAttribute` elements.

# Custom Profile Field Types

Custom profile field types can be provided as was previously possible through a
plugin, by providing a class through the component class of the component that
implements `ILIAS\User\Profile\Fields\Custom\Type`.

```
$contribute[ILIAS\User\Profile\Fields\Custom\Type::class] = fn() =>
    new CustomFieldType();
```

The setup does not yet provide for an integration of custom language variables so
you will need to find your own solution.