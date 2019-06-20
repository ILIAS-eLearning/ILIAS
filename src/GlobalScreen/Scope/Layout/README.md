Scope Layout
============


# Examples
```php
        //
        // PAGE
        //
        $this->gs->layout()->modifyPageWithClosure(function (Page $current) : Page {
            return $this->ui->factory()->layout()->page()->standard([]);
        });

        $this->gs->layout()->modifyPageWithInstance(new class implements PageBuilder
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
        $this->gs->layout()->modifyMetaBarWithClosure(function (MetaBar $current) : MetaBar {
            $f = $this->ui->factory();

            $symbol = $f->symbol()->glyph()->sortDescending();
            $content = $f->legacy('This is a completely replaced MetaBar');
            $entry = $f->mainControls()->slate()->legacy('test', $symbol, $content);

            return $f->mainControls()->metaBar()
                ->withAdditionalEntry('lorem', $entry);
        });

        $this->gs->layout()->modifyMetaBarWithInstance(new class implements MetaBarModifier
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
        $this->gs->layout()->modifyMainBarWithClosure(function (MainBar $current) : MainBar {
            $f = $this->ui->factory();

            $symbol = $f->symbol()->glyph()->up();
            $content = $f->legacy("Hi there!");
            $entry = $f->mainControls()->slate()->legacy('entry', $symbol, $content);

            return $current->withAdditionalEntry('lorem', $entry);
        });

        $this->gs->layout()->modifyMainBarWithInstance(new class implements MainBarModifier
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

        $this->gs->layout()->modifyBreadCrumbsWithClosure(function (Breadcrumbs $current) : Breadcrumbs {
            return $current->withAppendedItem($this->ui->factory()->link()->standard("Additional Item!", "#"));
        });

        $this->gs->layout()->modifyBreadCrumbsWithInstance(new class implements BreadCrumbsModifier
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
        $this->gs->layout()->modifyLogoWithClosure(function (Image $current) : Image {
            return $this->ui->factory()->image()->responsive("https://brandmark.io/logo-rank/random/apple.png", "ILIAS");
        });

        $this->gs->layout()->modifyLogoWithInstance(new class implements LogoModifier
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