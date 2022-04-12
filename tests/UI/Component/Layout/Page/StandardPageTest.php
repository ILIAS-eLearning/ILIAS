<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Component\MainControls\MetaBar;
use \ILIAS\UI\Component\MainControls\MainBar;
use \ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use \ILIAS\UI\Component\Image\Image;
use \ILIAS\UI\Implementation\Component\Layout\Page;
use \ILIAS\UI\Implementation\Component\Legacy\Legacy;

/**
 * Tests for the Standard Page
 */
class StandardPageTest extends ILIAS_UI_TestBase
{
    public function setUp() : void
    {
        $sig_gen = new \ILIAS\UI\Implementation\Component\SignalGenerator();
        $this->metabar = $this->createMock(MetaBar::class);
        $this->mainbar = $this->createMock(MainBar::class);
        $this->crumbs = $this->createMock(Breadcrumbs::class);
        $this->logo = $this->createMock(Image::class);
        $this->logo->method("getCanonicalName")->willReturn("Logo Stub");
        $this->responsive_logo = $this->createMock(Image::class);
        $this->responsive_logo->method("getCanonicalName")->willReturn("Responsive Logo Stub");
        $this->contents = array(new Legacy('some content', $sig_gen));
        $this->title = 'pagetitle';

        $this->factory = new Page\Factory();
        $this->stdpage = $this->factory->standard(
            $this->contents,
            $this->metabar,
            $this->mainbar,
            $this->crumbs,
            $this->logo,
            null,
            $this->title
        )->withResponsiveLogo($this->responsive_logo);
    }

    public function testConstruction() : void
    {
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Layout\\Page\\Standard",
            $this->stdpage
        );
    }

    public function testGetContent() : void
    {
        $this->assertEquals(
            $this->contents,
            $this->stdpage->getContent()
        );
    }

    public function testGetMetabar() : void
    {
        $this->assertEquals(
            $this->metabar,
            $this->stdpage->getMetabar()
        );
    }

    public function testGetMainbar() : void
    {
        $this->assertEquals(
            $this->mainbar,
            $this->stdpage->getMainbar()
        );
    }

    public function testGetBreadcrumbs() : void
    {
        $this->assertEquals(
            $this->crumbs,
            $this->stdpage->getBreadcrumbs()
        );
    }

    public function testGetLogo() : void
    {
        $this->assertEquals(
            $this->logo,
            $this->stdpage->getLogo()
        );
    }

    public function testHasLogo() : void
    {
        $this->assertTrue($this->stdpage->hasLogo());
    }

    public function testGetResponsiveLogo() : void
    {
        $this->assertEquals(
            $this->responsive_logo,
            $this->stdpage->getResponsiveLogo()
        );
    }

    public function testHasResponsiveLogo() : void
    {
        $this->assertTrue($this->stdpage->hasResponsiveLogo());
    }

    public function testWithWrongContents() : void
    {
        $this->expectException(TypeError::class);
        $this->stdpage = $this->factory->standard(
            $this->metabar,
            $this->mainbar,
            'string is not allowed here',
            $this->crumbs,
            $this->logo
        );
    }

    public function testGetTitle() : void
    {
        $this->assertEquals(
            $this->title,
            $this->stdpage->getTitle()
        );
    }

    public function testWithTitle() : void
    {
        $title = 'some title';
        $this->assertEquals(
            $title,
            $this->stdpage->withTitle($title)->getTitle()
        );
    }
    public function testWithShortTitle() : void
    {
        $title = 'some short title';
        $this->assertEquals(
            $title,
            $this->stdpage->withShortTitle($title)->getShortTitle()
        );
    }
    public function testWithViewTitle() : void
    {
        $title = 'some view title';
        $this->assertEquals(
            $title,
            $this->stdpage->withViewTitle($title)->getViewTitle()
        );
    }
    
    public function testWithMetaDatum()
    {
        $meta_datum_key = 'meta_datum_key';
        $meta_datum_value = 'meta_datum_value';
        $meta_data = [$meta_datum_key => $meta_datum_value];
        $this->assertEquals(
            $meta_data,
            $this->stdpage->withAdditionalMetaDatum($meta_datum_key, $meta_datum_value)->getMetaData()
        );
    }
}
