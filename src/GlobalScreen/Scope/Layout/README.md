Scope Layout
============
The GlobalScreen service takes care of the mediation between the Command-Classes and UI components and is the instance that assembles all components of the finished page and transfers them to rendering.

> Currently (ILIAS 6.0), the services and modules deliver their content via an `ilGlobalPageTemplate`, an implementation of the `ilGlobalTemplateInterface` to maintain compatibility. Internally this instance delegates all relevant parts to the Scope `Layout` of the GlobalScreen service.

The Scope `Layout` assumes by default a completely filled `Page` to be displayed. This `page` is filled with UI components, which in turn are filled with information from the other scopes (e.g., `MetaBar` or `MainBar`). Now not all places in ILIAS need the same `Page` or all elements of a `Page`.  For this purpose, a developer can influence the composition of the page from within his code before passing it to rendering.

He can do this for the whole page (omitting the `MainBar` for example) or he can provide a completely own `MainBar` because this is required (e.g. LTI).

All possibilities to modify the components of a `Page` can be found via the GlobalScreen service in the `DIC`:

```php
global $DIC;
$DIC->globalScreen()->layout()->modifiers()->...
```

Currently the following components can be modified:
- Page
- MetaBar
- MainBar
- BreadCrumbs
- Icon
- Content

All these components can be modified in two ways, either as `Closure` or as a class of a certain interface.

## As Closure 

For example, if you want to modify the `MetaBar`, pass a `Closure` in the following form to the method `modifyMetaBarWithClosure()`:

```php
function (ILIAS\UI\Component\MainControls\MetaBar $current) : ILIAS\UI\Component\MainControls\MetaBar {
    ...
}
```

The first parameter as well as the return value must correspond to `ILIAS\UI\Component\MainControls\MetaBar`. Within the `Closure` the current state of the `MetaBar` can be changed (in `$current`) or a completely own `Metabar` can be returned.

The correct parameters and return values are checked.

## As Instance
The same result can be achieved by passing an instance of the required interface.

Again for the MetaBar, you can pass an anonymous class to the `modifyPageWithInstance()` method (of course you can also implement effective classes):

```php
new class implements ILIAS\GlobalScreen\Scope\Layout\Modifier\MetaBarModifier
{
    public function getMetaBar(MetaBar $current) : MetaBar
    {
        ...
    }
}
```

Have a look at the following concrete examples:

### All Examples
```php
//
// PAGE
//
$this->gs->layout()->modifiers()->modifyPageWithClosure(function (Page $current) : Page {
    return $this->ui->factory()->layout()->page()->standard([]);
});

$this->gs->layout()->modifiers()->modifyPageWithInstance(new class implements PageBuilder
{

    public function build(PagePartProvider $parts) : Page
    {
        global $DIC;

        return $DIC->ui()->factory()->layout()->page()->standard(
            [$parts->getContent()],
            $parts->getMetaBar(),
            $parts->getMainBar(),
            $parts->getBreadCrumbs(),
            $parts->getLogo());
    }
});

//
// MetaBar
//
$this->gs->layout()->modifiers()->modifyMetaBarWithClosure(function (MetaBar $current) : MetaBar {
    $f = $this->ui->factory();

    $symbol = $f->symbol()->glyph()->sortDescending();
    $content = $f->legacy('This is a completely replaced MetaBar');
    $entry = $f->mainControls()->slate()->legacy('test', $symbol, $content);

    return $f->mainControls()->metaBar()
        ->withAdditionalEntry('lorem', $entry);
});

$this->gs->layout()->modifiers()->modifyMetaBarWithInstance(new class implements MetaBarModifier
{

    public function getMetaBar(MetaBar $current) : MetaBar
    {
        global $DIC;
        $f = $DIC->ui()->factory();

        $symbol = $f->symbol()->glyph()->sortDescending();
        $content = $f->legacy('This is a completely replaced MetaBar');
        $entry = $f->mainControls()->slate()->legacy('test', $symbol, $content);

        return $f->mainControls()->metaBar()
            ->withAdditionalEntry('lorem', $entry);
    }
});

//
// MainBar
//
$this->gs->layout()->modifiers()->modifyMainBarWithClosure(function (MainBar $current) : MainBar {
    $f = $this->ui->factory();

    $symbol = $f->symbol()->glyph()->up();
    $content = $f->legacy("Hi there!");
    $entry = $f->mainControls()->slate()->legacy('entry', $symbol, $content);

    return $current->withAdditionalEntry('lorem', $entry);
});

$this->gs->layout()->modifiers()->modifyMainBarWithInstance(new class implements MainBarModifier
{

    public function getMainBar(MainBar $current) : MainBar
    {
        global $DIC;
        $f = $DIC->ui()->factory();

        $symbol = $f->symbol()->glyph()->up();
        $content = $f->legacy("Hi there!");
        $entry = $f->mainControls()->slate()->legacy('entry', $symbol, $content);

        return $current->withAdditionalEntry('lorem2', $entry);
    }
});

//
// BreadCrumbs
//

$this->gs->layout()->modifiers()->modifyBreadCrumbsWithClosure(function (Breadcrumbs $current) : Breadcrumbs {
    return $current->withAppendedItem($this->ui->factory()->link()->standard("Additional Item!", "#"));
});

$this->gs->layout()->modifiers()->modifyBreadCrumbsWithInstance(new class implements BreadCrumbsModifier
{

    public function getBreadCrumbs(Breadcrumbs $current) : Breadcrumbs
    {
        global $DIC;

        return $current->withAppendedItem($DIC->ui()->factory()->link()->standard("another Item!", "#"));
    }
});

//
// Logo
//
$this->gs->layout()->modifiers()->modifyLogoWithClosure(function (Image $current) : Image {
    return $this->ui->factory()->image()->responsive("https://brandmark.io/logo-rank/random/apple.png", "ILIAS");
});

$this->gs->layout()->modifiers()->modifyLogoWithInstance(new class implements LogoModifier
{

    /**
     * @inheritDoc
     */
    public function getLogo(Image $current) : Image
    {
        global $DIC;

        return $DIC->ui()->factory()->image()->responsive("https://brandmark.io/logo-rank/random/apple.png", "ILIAS");
    }
});
```

#Attention

The possibility of influencing the components of the `Page` holds dangers:
- You can never be sure that before or after you do not make a modification.
- Do not use this possibility to bring menu entries or `Tools` into the `MainBar`. Use the methods provided for this as `providers`.
