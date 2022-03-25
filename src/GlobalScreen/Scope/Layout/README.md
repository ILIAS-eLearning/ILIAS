Scope Layout
============

## Modification
The GlobalScreen service takes care of the mediation between the command classes and UI components and is responsible for assembling all necessary elements into a complete `Page` and submitting it to rendering.

> Currently (ILIAS 6.0), the services and modules deliver their content via an `ilGlobalPageTemplate`, an implementation of the `ilGlobalTemplateInterface`. This is done for compatibility reasons. Internally, this instance delegates all relevant elements to the scope layout of the GlobalScreen service.

The Scope `Layout` assumes by default that a completely filled `page` is displayed. This `page` is filled with UI components, which in turn are filled with information from the other areas (e.g. `MetaBar` or `MainBar`). Now not all places in ILIAS need the same `Page` or all elements of a `Page` anymore. For this purpose, a developer can influence the composition of the page from his code before passing it to rendering.

He can do this for the whole page (e.g. without the `MainBar`) or he can provide a completely custom `MainBar` if required (e.g. LTI).

All possibilities to change the components of a `page` can be found via the GlobalScreen service in the `DIC`:

```php
global $DIC;
$DIC->globalScreen()->layout()->factory()->...
```

Currently the following areas can be addressed:

- Page
- MetaBar
- MainBar
- BreadCrumbs
- Icon
- Content

### Register modification

Like all other scopes, this scope works in the provider/collector procedure. In order to be able to make a change to the page, you implement your own `ModificationProvider`. These providers are `ScreenContextAwareProviders`, further information can be found at [src/GlobalScreen/ScreenContext/README.md](../../ScreenContext/README.md)

```php
<?php namespace ILIAS\Container\Screen;

//...

class MemberViewLayoutProvider extends AbstractModificationProvider implements ModificationProvider
{

    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->repository();
    }

    public function getLogoModification(CalledContexts $screen_context_stack) : ?LogoModification
    {
        if (!$screen_context_stack->current()->hasReferenceId()) {
            return null;
        }

        $mv = ilMemberViewSettings::getInstance();
        if ($mv->isActive()) {
            return $this->globalScreen()->layout()->factory()->logo()->withModification(function (Image $current) use ($mv) : Image {
                $ref_id = $mv->getCurrentRefId();

                $image = $this->dic->ui()->factory()->image()->responsive("https://www.colourbox.com/preview/5559052-icon-user-red.jpg", "mv");
                if ($ref_id) {
                    $url = ilLink::_getLink(
                        $ref_id,
                        ilObject::_lookupType(ilObject::_lookupObjId($ref_id)),
                        array('mv' => 0)
                    );
                    $image = $image->withAction($url);
                }

                return $image;
            })->withHighPriority();
        }

        return null;
    }
}

```

The example returns - if in the `ScreenContext` 'repository', if a Ref-ID is present and if the MemberView is used in a course, – a change to the logo (which is only offered as an example until a dedicated message for it is offered in the UI service).

Since multiple `ModificationProviders` could modify the same area of the page at the same time, a priority is set for each modification (in this case `->withHighPriority()`). Same priorities lead to an exception and it must be decided in JourFixe which of the two modifications gets the higher priority.

## Attention

The possibility of influencing the components of the "page" holds dangers:

- You can never be sure that you will not make any changes before or after the change.
- Do not use this option to bring menu items or `Tools` into the `MainBar`. Use the methods provided for this purpose as `Providers` in the respective scopes.

# MetaContent (such as CSS, JS und MetaData)
The GlobalScreen Scope 'Layout' is also used to collect and prepare other contents for rendering, so that they can be taken into account in the final rendering. The MetaContents are not added via `Provider`, but currently via the call via

```
$DIC->globalScreen()->layout()->meta()->addXY(...);
```

Currently there are the following areas:
- addCss
- addJs
- addInlineCss
- addOnloadCode
- addMetaDate

## Examples
**CSS**
```php
$DIC->globalScreen()->layout()->meta()->addCss('./path/to.css');
$DIC->globalScreen()->layout()->meta()->addInlineCss('.my-class {color: red; };');
```

**JS**
```php
$DIC->globalScreen()->layout()->meta()->addJs('./path/to.js');
$DIC->globalScreen()->layout()->meta()->addOnloadCode('alert();');
```

**MetaData**
```php
$DIC->globalScreen()->layout()->meta()->addMetaDatum('keywords', 'Learning,Management');
```
