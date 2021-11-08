<?php declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Implementation\Component\Layout\Page;
use ILIAS\UI\Implementation\Component\Legacy\Legacy;
use ILIAS\UI\Implementation\Component\SignalGenerator;

/**
 * Tests for the Standard Page
 */
class StandardPageTest extends ILIAS_UI_TestBase
{
    protected Page\Standard $stdpage;
    protected Page\Factory $factory;
    protected MainBar $mainbar;
    protected MetaBar $metabar;
    protected Breadcrumbs $crumbs;
    protected Image $logo;
    protected string $title;

    /**
     * @var Legacy[]
     */
    protected array $contents;

    public function setUp() : void
    {
        $sig_gen = new SignalGenerator();
        $this->metabar = $this->createMock(MetaBar::class);
        $this->metabar->method("getCanonicalName")->willReturn("MetaBar Stub");
        $this->mainbar = $this->createMock(MainBar::class);
        $this->mainbar->method("getCanonicalName")->willReturn("MainBar Stub");
        $this->crumbs = $this->createMock(Breadcrumbs::class);
        $this->crumbs->method("getCanonicalName")->willReturn("Breadcrumbs Stub");
        $this->logo = $this->createMock(Image::class);
        $this->logo->method("getCanonicalName")->willReturn("Logo Stub");
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
        );
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

    public function testWithTextDirection() : void
    {
        $this->assertEquals("ltr", $this->stdpage->getTextDirection());
        $this->assertEquals(
            "rtl",
            $this->stdpage
            ->withTextDirection($this->stdpage::RTL)
            ->getTextDirection()
        );
    }

    public function testRenderingWithTitle() : void
    {
        $this->stdpage = $this->stdpage
            ->withTitle("Title")
            ->withViewTitle("View Title")
            ->withShortTitle("Short Title");

        $r = $this->getDefaultRenderer(null, [$this->metabar, $this->mainbar, $this->crumbs, $this->logo]);
        $html = $this->brutallyTrimHTML($r->render($this->stdpage));

        $exptected = $this->brutallyTrimHTML('
<!DOCTYPE html>
<html lang="en" dir="ltr">
   <head>
      <meta charset="utf-8" />
      <meta http-equiv="X-UA-Compatible" content="IE=edge" />
      <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
      <title>Short Title: View Title</title>
      <style></style>
   </head>
   <body>
      <div class="il-layout-page">
         <header>
            <div class="header-inner">
               <div class="il-logo">Logo Stub<div class="il-pagetitle">Title</div></div>MetaBar Stub</div>
         </header>
         <div class="breadcrumbs"></div>
         <div class="il-system-infos"></div>
         <div class="nav il-maincontrols">MainBar Stub</div>
         <!-- html5 main-tag is not supported in IE / div is needed -->
         <main class="il-layout-page-content">
            <div>some content</div>
         </main>
      </div>
      <script>il.Util.addOnLoad(function() {});</script>
   </body>
</html>');
        $this->assertEquals($exptected, $html);
    }

    public function testRenderingWithRtlLanguage() : void
    {
        $this->stdpage = $this->stdpage->withTextDirection($this->stdpage::RTL);

        $r = $this->getDefaultRenderer(null, [$this->metabar, $this->mainbar, $this->crumbs, $this->logo]);
        $html = $this->brutallyTrimHTML($r->render($this->stdpage));

        $exptected = $this->brutallyTrimHTML('
<!DOCTYPE html>
<html lang="en" dir="rtl">
   <head>
      <meta charset="utf-8" />
      <meta http-equiv="X-UA-Compatible" content="IE=edge" />
      <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
      <title>: </title>
      <style></style>
   </head>
   <body>
      <div class="il-layout-page">
         <header>
            <div class="header-inner">
               <div class="il-logo">Logo Stub<div class="il-pagetitle">pagetitle</div></div>MetaBar Stub</div>
         </header>
         <div class="breadcrumbs"></div>
         <div class="il-system-infos"></div>
         <div class="nav il-maincontrols">MainBar Stub</div>
         <!-- html5 main-tag is not supported in IE / div is needed -->
         <main class="il-layout-page-content">
            <div>some content</div>
         </main>
      </div>
      <script>il.Util.addOnLoad(function() {});</script>
   </body>
</html>');
        $this->assertEquals($exptected, $html);
    }
}
