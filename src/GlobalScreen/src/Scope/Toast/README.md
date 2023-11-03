Scope Toasts
===================
This scope addresses toasts that are displayed to the user in the ToastContainer. Components can - as in all other
scopes - via an implementation of a `ToastProvider` provide the `ToastCollector` with a list of GloablScreen Toasts
Items. These are summarized, translated to UI\Toasts and displayed in the ToastContainer.

After implementing a `ToastProvider`, you need to perform a classmap update which triggers the collection of Providers
in the GlobalScreen-Service:

```bash
./libs/composer/vendor/bin/composer dump-autoload
```

The following types are currently available via the Factory (`$this->toast_factory` in `AbstractToastProvider`):

- `isStandardItem` aka `StandardToastItem`
- `ToastAction` to perform additional actions on the Toast

# Provider

An own provider is implemented, e.g:

```php
<?php declare(strict_types=1);

namespace ILIAS\Example\Provider;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Toast\Provider\AbstractToastProvider;

class ExampleToastProvider extends AbstractToastProvider
{
    /**
     * @inheritDoc
     */
    public function getToasts(): array
    {
        // This is just a simple example, we use the session to store if a toast has been seen. In the real world,
        // you should persist this information.
        if ((ilSession::get('toast_dismissed') ?? false)) {
            return [];
        }

        // We provide a callback, which is executed, if the toast is shown in the GUI.
        // This callback is called asynchronously.
        $on_shown = function (): void {
            $this->dic->ui()->mainTemplate()->setOnScreenMessage(
                'success',
                'Test Toast Shown', // Use ilLanguage here!
                true
            );
        };

        // We provide a callback, which is executed, if the toast is closed using the X glyph in the GUI.
        // This callback is called asynchronously.
        $on_closed = function (): void {
            $this->dic->ui()->mainTemplate()->setOnScreenMessage(
                'failure',
                'Test Toast Closed',
                true
            );
        };

        // We provide a callback, which is executed, if the toast has vanished automatically in the GUI after some time.
        // This callback is called asynchronously.
        $on_vanished = function (): void {
            $this->dic->ui()->mainTemplate()->setOnScreenMessage(
                'info',
                'Test Toast Vanished',
                true
            );
        };

        // You can provide more Actions which will be rendered as Links in the UI\Toast. Please note that this
        // Actions are called synchronously, therefore you must redirect after performing the action. See withAdditionToastAction below
        $on_explicity_clicked = function (): void {
            ilSession::set('toast_dismissed', true); // This is the Place we could persist this information
            $this->dic->ui()->mainTemplate()->setOnScreenMessage(
                'info',
                'Test Toast dismissed', // Use ilLanguage here!
                true
            );
            $this->dic->ctrl()->redirectToURL('/'); // Redirect to somewhere
        };

        // We build a Standard GlobalScreen Toast Item, which is a wrapper around the UI Toast Component.
        $toast = $this->toast_factory
            ->standard(
                $this->if->identifier('test_toast'), // Every item needs an identification
                "Test Toast", // Use ilLanguage here!
            )
            ->withDescription("This is a test toast") // Use ilLanguage here!
            ->withShownCallable($on_shown)
            ->withClosedCallable($on_closed)
            ->withVanishedCallable($on_vanished)
            ->withAdditionToastAction(
                $this->toast_factory->action(
                    'dismiss',
                    "Dismiss Toast completely", // Use ilLanguage here!
                    $on_explicity_clicked
                )
            );
        return [
            $toast
        ];
    }
}
```

For more details on the properties of the UI Component Toast, see the respective documentation in
src/UI/Components/Item/Toast and the respective examples.
