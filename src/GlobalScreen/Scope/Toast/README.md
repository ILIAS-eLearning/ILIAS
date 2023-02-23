Scope Toasts
===================
This scope addresses toasts that are displayed to the user in the ToastContainer. Components can - as in all other scopes - via an implementation of a `ToastProvider` provide the `ToastCollector` with a list of toasts. These are summarized and displayed in the ToastContainer.

The following types are currently available via the Factory:

- `StandardToast`

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
    public function getToasts() : array
    {
        $factory = $this->globalScreen()->toasts()->factory();
        
        $toast = $this->getDefaultToast(
            'Toast Example', 
            $this->dic->ui()->factory()->symbol()->icon()->standard('exmpl', 'Example')
        )->withDescription('This is a toast example from scope inside the Global Screen');

        return [$toast];
    }
}
```

For more details on the properties of the UI Component Toast, see the respective documentation in src/UI/Components/Item/Toast and the respective examples.
