# LegalDocuments Service

This service provides the base to define concrete processes to be used for legal documents.

The service is split into 3 parts.

1. The core system which provides the slot system, the bridge between ILIAS calls and hooks and which provides the services.
2. Generic classes to make the creation of an administration for the document and history slots easier.
3. A `ConsumerToolbox` to make the creation of consumers easier.

## 1. The core system / using the slots

This service provides several slots which one can use. The service which wants to use slots of the legal documents needs to define a class which implements the `ILIAS\LegalDocuments\Consumer` interface.

Example:
```php
<?php

declare(strict_types=1);

use ILIAS\LegalDocuments\Consumer;
use ILIAS\LegalDocuments\UseSlot;
use ILIAS\LegalDocuments\LazyProvide;

class MyConsumer implements Consumer
{
    public function id(): string
    {
        return 'my-consumer';
    }

    public function uses(UseSlot $slot, LazyProvide $provide): UseSlot
    {
        return $slot->showInFooter(fn($footer) => $footer);
    }
}
```

The new consumer must be registered. To do so, run the update command of the ILIAS setup program.

Within a consumer one can define several slots.
Each slot defines one or more of the following:
1. a place to hook in to, like `->showInFooter(...)`
2. a complete process, like `->canWithdraw(...)`
3. a service to be available through `$DIC['legalDocuments']->provide('my-consumer');`, like `hasHistory(...)`

The following will describe each slot individually:

### UseSlot::afterLogin

This is a simple hook.

The slot expects a callback which will be called after login. Internally this uses the Services/Authentication afterLogin event.

### UseSlot::showInFooter

This is a simple hook.

This slot expects a callback which will be called with the `ILIAS\UI\Component\MainControls\Footer` so new items can be added to it.

### UseSlot::showOnLoginPage

This is a simple hook.

This slot expects a callback which will be called when the login page is built. All components returned from the callback will be rendered on the login page.

### UseSlot::hasOnlineStatusFilter

This is a simple hook.

This slot expects a callback which will be called to filter user ids which should not be visible to other users. E.g. for privacy reasons.

### UseSlot::hasDocuments

This provides a new service.

With this slot one can request an own `namespace` (the consumer id) for documents.

This provides a repository for `ILIAS\LegalDocuments\Value\Document` and `ILIAS\LegalDocuments\Value\Criterion`, a table for the administration and methods to for criteria validation.

To be able to use different kinds of content types for the actual documents one can provide a mapping from a document's type to a component (callback(DocumentContent): Component).
The same applies to criteria. But instead of callbacks to create components one supplies `ConditionDefinition`s.

To use default ones, please see [ConsumerToolbox](#ConsumerToolbox).

#### Implementing new ConditionDefinition

A `ConditionDefinition` provides a form group for construction of new criteria of that type and to create a condition from a criterion.

`Condition`s are used to determine if a document should be used for a user.
For this a document contains a list of criteria.
Each `Criterion` in this list is converted to a `Condition` and checked against an `ilObjUser`.
Only when **all** `Condition`s of the document return `true`, does the document match a user.

Please see `ILIAS\LegalDocuments\Provide\ProvideDocument` for further information.

They can be accessed with `$DIC['legalDocuments']->provide('my-consumer')->document()`.

### UseSlot::hasUserManagementFields

This is a simple hook.

This slot expects a callback which will receive a `ilObjUser` object and returns an array of key value pairs (array<string, string>).

These fields will be shown in the user administration GUI when editing a user. The user which is being edited is given to the callback.

The key must be a language key.

### UseSlot::canWithdraw

This defines a process and provides a service.

This provides a method to initiate the withdrawal process for this consumer id.
And another method to finish the withdrawal process for this consumer id.

They can be accessed with `$DIC['legalDocuments']->provide('my-consumer')->withdrawal()`.

Please see `ILIAS\LegalDocuments\Provide\ProvideWithdrawal` for further information.

This slot requires a `ILIAS\LegalDocuments\ConsumerSlots\WithdrawProcess`.

The process will look like this:

1. When the withdrawal is requested `WithdrawProcess::withdrawalRequested` is called. After this method the user is logged out.
   This should enable `WithdrawProcess::isOnGoing` to return `true` on successive requests.
2. On the loggout page `WithdrawProcess::showValidatePasswordMessage` will be called and all returned components will be rendered.
3. When the user is logging in again `WithdrawProcess::isOnGoing` should return `true`.
4. As long as `WithdrawProcess::isOnGoing` returns `true` the user is redirected to a withdrawal GUI. There `WithdrawProcess::showWithdraw` will be called
   and the returned `ILIAS\LegalDocument\PageFragemnt` will be rendered. To provide a form all commands to the given gui class will `WithdrawProcess::showWithdraw` again.
5. When `WithdrawProcess::showWithdraw` calls `$DIC['legalDocuments']->provide('my-consumer')->withdrawal()->finishAndLogout()` the withdrawal is completed.
6. After completion the user will be logged out and `WithdrawProcess::withdrawalFinished` is called on the `logout.php` page (the user is not available anymore).

#### Aborting the withdraw process

At any time (even after step 1.) `WithdrawProcess::isOnGoing` can return `false`. As soon as it returns `false`,
the withdrawal is aborted and `$DIC['legalDocuments']->provide('my-consumer')->withdrawal()->beginProcessURL()` needs to called again to resart the process.

### UseSlot::hasAgreement

This defines a process and provides a service.

Similar to `UseSlot::canWithdraw` this defines a process.
This process is in contrary to `canWithdraw` not started manually (e.g. with an URL).

This slot requires a `ILIAS\LegalDocuments\ConsumerSlots\Agreement`.

The process will look like this:
1. After the login of a user `Agreement::needsToAgree` is called. When it returns `true` the process is started.
2. The user is redirected to an agreement page. On this page `Agreement::showAgreementForm` will be called to render the page.
3. It is up to the service to define if this process is `completed` or `aborted`.
   But the service is finished (either completed or aborted) when `Agreement::needsToAgree` returns `false`.

To create the URL to the public page use: `$DIC['legalDocuments']->provide('my-consumer')->publicPage()->url()`.

### UseSlot::hasPublicPage

This is a hook. And a service.

This will provide an informative page for logged out users.
The callback will be called with a gui class and a command and must return a `ILIAS\LegalDocument\PageFragemnt` which will be rendered.

To create the URL to the public page use: `$DIC['legalDocuments']->provide('my-consumer')->publicPage()->url()`.

### UseSlot::hasHistory

This is a service.

With this slot one can request an own `namespace` (the consumer id) for a document history.

This service requires documents, so the slot UseSlot::hasDocuments must be used as well when using this slot.

This provides a repository for `ILIAS\LegalDocuments\Value\History`.

Please see `ILIAS\LegalDocuments\Provide\ProvideHistory` for further information.

They can be accessed with `$DIC['legalDocuments']->provide('my-consumer')->history()`.

### UseSlot::onSelfRegistration

This defines a process.

This slot requires a `ILIAS\LegalDocuments\ConsumerSlots\SelfRegistration`.

The `SelfRegistration` will be called when a new user is created with the self registration GUI.

The process will look like this:
1. A user navigates to the self registration page. The self registration GUI is rendered and `SelfRegistration::legacyInputGUIs` is called.
   This method must return a list of input GUI's that will be added to a `ilPropertyFormGUI`.
2. The user submits the form. The registration form is validated and `SelfRegistration::saveLegacyForm` is called.
   This must be used for validation only (the user ist still anonymnous) and return whether or not the previously returned input GUI's are valid.
   They can be access with their input name (Error messages can / should be added to them as well).
3. When the form is valid (and `SelfRegistration::saveLegacyForm` returned `true`) a user is created and
   `SelfRegistration::userCreation` is called with the newly constructed user. The user can now be modified if needed.

### UseSlot::canReadInternalMails

This is a simple hook.

The slot expects a `ILIAS\Refinery\Constraint`. The Constraint will receive a `ilObjUser`.
When the user should not be able to receive internal mails the constraint must reject the user.

### UseSlot::canUseSoapApi

This is a simple hook.

The slot expects a `ILIAS\Refinery\Constraint`. The Constraint will receive a `ilObjUser`.
When the user should not be able to use the soap API the constraint must reject the user.

## 2. Administration

The following classes can be used freely to create one's own administration:
- `ilLegalDocumentsAdministrationGUI` A GUI to which can be redirected to.
- `ILIAS\LegalDocument\Administration` A class which provides building blocks to create a administration
  (and which is used and accessible from and by `ilLegalDocumentsAdministrationGUI`)
- `ILIAS\LegalDocument\Config` A wrapper to provide direct access to $DIC['legalDocuments']->provide('my-consumer')` and automatically handle write restrictions.

Additionally with `ProvideDocument::table` and `ProvideHistory::table` a table can be created for the administration.

### 3. ConsumerToolbox

The services TermsOfService and LegalDocument contain a lot of the same functionality.
For that reason the ConsumerToolbox provides default implementations for many of the conumer slots as well as new interfaces which the default implementations require instead.

The ConsumerToolbox can be used* when the service can implement the following 2 interfaces:
- `ILIAS\LegalDocument\ConsumerToolbox\Setting`
- `ILIAS\LegalDocument\ConsumerToolbox\UserSettings`

(*) Parts of the ConsumerToolbox can still be used withoput these interfaces.

To simplify the object creation, `ILIAS\LegalDocument\ConsumerToolbox\Blocks` can be used.

Here a dummy example to use the ConsumerToolbox to add slots:
```php
<?php

declare(strict_types=1);

use ILIAS\LegalDocuments\Consumer;
use ILIAS\LegalDocuments\UseSlot;
use ILIAS\LegalDocuments\LazyProvide;
use ILIAS\LegalDocuments\ConsumerToolbox\Blocks;

class MyConsumer implements Consumer
{
    public function id(): string
    {
        return 'my-consumer';
    }

    public function uses(UseSlot $slot, LazyProvide $provide): UseSlot
    {
        global $DIC;
        $blocks = new Blocks($DIC, $provide);
        $default = $blocks->defaultMappings();

        return $slot->hasDocuments($default->contentAsComponent(), $default->conditionDefinitions())
                    ->hasHistory()
                    ->showOnLoginPage($blocks->slot()->showOnLoginPage());
    }
}

```

Please see `ILIAS\TermsOfService\Consumer` or `ILIAS\DataProtection\Consumer` for more examples.
