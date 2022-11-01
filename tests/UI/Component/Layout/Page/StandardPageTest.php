<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Component\Dropdown\Factory;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Toast\Container;
use ILIAS\UI\Implementation\Component\Layout\Page;
use ILIAS\UI\Implementation\Component\Legacy\Legacy;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Implementation\Component\Breadcrumbs\Breadcrumbs as Crumbs;
use ILIAS\UI\Implementation\Component\Link\Standard as CrumbEntry;
use ILIAS\UI\Implementation\Component\Button;
use ILIAS\UI\Implementation\Component\Dropdown;

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
    protected Image $responsive_logo;
    protected Container $overlay;
    protected string $title;

    /**
     * @var Legacy[]
     */
    protected array $contents;

    public function setUp(): void
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
        $this->responsive_logo = $this->createMock(Image::class);
        $this->responsive_logo->method("getCanonicalName")->willReturn("Responsive Logo Stub");
        $this->overlay = $this->createMock(Container::class);
        $this->overlay->method("getCanonicalName")->willReturn("Overlay Stub");
        $this->contents = array(new Legacy('some content', $sig_gen));
        $this->title = 'pagetitle';

        $this->factory = new Page\Factory();
        $this->stdpage = $this->factory->standard(
            $this->contents,
            $this->metabar,
            $this->mainbar,
            $this->crumbs,
            $this->logo,
            $this->responsive_logo,
            'favicon.ico',
            $this->overlay,
            null,
            $this->title
        );
    }

    public function testConstruction(): void
    {
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Layout\\Page\\Standard",
            $this->stdpage
        );
    }

    public function testGetContent(): void
    {
        $this->assertEquals(
            $this->contents,
            $this->stdpage->getContent()
        );
    }

    public function testGetMetabar(): void
    {
        $this->assertEquals(
            $this->metabar,
            $this->stdpage->getMetabar()
        );
    }

    public function testGetMainbar(): void
    {
        $this->assertEquals(
            $this->mainbar,
            $this->stdpage->getMainbar()
        );
    }

    public function testGetBreadcrumbs(): void
    {
        $this->assertEquals(
            $this->crumbs,
            $this->stdpage->getBreadcrumbs()
        );
    }

    public function testGetLogo(): void
    {
        $this->assertEquals(
            $this->logo,
            $this->stdpage->getLogo()
        );
    }

    public function testHasLogo(): void
    {
        $this->assertTrue($this->stdpage->hasLogo());
    }

    public function testGetResponsiveLogo(): void
    {
        $this->assertEquals(
            $this->responsive_logo,
            $this->stdpage->getResponsiveLogo()
        );
    }

    public function testHasResponsiveLogo(): void
    {
        $this->assertTrue($this->stdpage->hasResponsiveLogo());
    }

    public function testWithFaviconPath(): void
    {
        $this->assertEquals("favicon.ico", $this->stdpage->getFaviconPath());
        $this->assertEquals(
            "test",
            $this->stdpage->withFaviconPath("test")->getFaviconPath()
        );
    }

    public function testGetOverlay(): void
    {
        $this->assertEquals(
            $this->overlay,
            $this->stdpage->getOverlay()
        );
    }

    public function testWithWrongContents(): void
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

    public function testGetTitle(): void
    {
        $this->assertEquals(
            $this->title,
            $this->stdpage->getTitle()
        );
    }

    public function testWithTitle(): void
    {
        $title = 'some title';
        $this->assertEquals(
            $title,
            $this->stdpage->withTitle($title)->getTitle()
        );
    }
    public function testWithShortTitle(): void
    {
        $title = 'some short title';
        $this->assertEquals(
            $title,
            $this->stdpage->withShortTitle($title)->getShortTitle()
        );
    }
    public function testWithViewTitle(): void
    {
        $title = 'some view title';
        $this->assertEquals(
            $title,
            $this->stdpage->withViewTitle($title)->getViewTitle()
        );
    }

    public function testWithTextDirection(): void
    {
        $this->assertEquals("ltr", $this->stdpage->getTextDirection());
        $this->assertEquals(
            "rtl",
            $this->stdpage
            ->withTextDirection($this->stdpage::RTL)
            ->getTextDirection()
        );
    }

    public function testWithMetaDatum(): void
    {
        $meta_datum_key = 'meta_datum_key';
        $meta_datum_value = 'meta_datum_value';
        $meta_data = [$meta_datum_key => $meta_datum_value];
        $this->assertEquals(
            $meta_data,
            $this->stdpage->withAdditionalMetaDatum($meta_datum_key, $meta_datum_value)->getMetaData()
        );
    }

    public function testRenderingWithTitle(): void
    {
        $this->stdpage = $this->stdpage
            ->withTitle("Title")
            ->withViewTitle("View Title")
            ->withShortTitle("Short Title");

        $r = $this->getDefaultRenderer(null, [$this->metabar, $this->mainbar, $this->crumbs, $this->logo, $this->overlay]);
        $html = $this->brutallyTrimHTML($r->render($this->stdpage));

        $exptected = $this->brutallyTrimHTML('<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Short Title: View Title</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <style></style>
</head>

<body>
    <div class="il-page-overlay">Overlay Stub</div>
    <div class="il-layout-page">
        <header>
            <div class="header-inner">
                <div class="il-logo"><span class="hidden-xs">Logo Stub</span><span class="visible-xs">Responsive Logo Stub</span>
                    <div class="il-pagetitle">Title</div>
                </div>MetaBar Stub
            </div>
        </header>
        <div class="il-system-infos"></div>
        <div class="nav il-maincontrols">MainBar Stub</div>
        <main class="il-layout-page-content">some content</main>
    </div>
    <script>il.Util.addOnLoad(function() {});</script>
</body>

</html>');
        $this->assertEquals($exptected, $html);
    }

    public function testRenderingWithRtlLanguage(): void
    {
        $this->stdpage = $this->stdpage->withTextDirection($this->stdpage::RTL);

        $r = $this->getDefaultRenderer(null, [$this->metabar, $this->mainbar, $this->crumbs, $this->logo, $this->overlay]);
        $html = $this->brutallyTrimHTML($r->render($this->stdpage));

        $exptected = $this->brutallyTrimHTML('<!DOCTYPE html>
<html lang="en" dir="rtl">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>:</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <style></style>
</head>

<body>
    <div class="il-page-overlay">Overlay Stub</div>
    <div class="il-layout-page">
        <header>
            <div class="header-inner">
                <div class="il-logo"><span class="hidden-xs">Logo Stub</span><span class="visible-xs">Responsive Logo Stub</span>
                    <div class="il-pagetitle">pagetitle</div>
                </div>MetaBar Stub
            </div>
        </header>
        <div class="il-system-infos"></div>
        <div class="nav il-maincontrols">MainBar Stub</div>
        <main class="il-layout-page-content">some content</main>
    </div>
    <script>il.Util.addOnLoad(function() {});</script>
</body>

</html>');
        $this->assertEquals($exptected, $html);
    }

    public function testRenderingWithMetaData(): void
    {
        $this->stdpage = $this->stdpage->withAdditionalMetaDatum('meta_datum_key_1', 'meta_datum_value_1');
        $this->stdpage = $this->stdpage->withAdditionalMetaDatum('meta_datum_key_2', 'meta_datum_value_2');

        $r = $this->getDefaultRenderer(null, [$this->metabar, $this->mainbar, $this->crumbs, $this->logo, $this->overlay]);
        $html = $this->brutallyTrimHTML($r->render($this->stdpage));
        $expected = $this->brutallyTrimHTML('
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>:</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <style></style>
    <meta name="meta_datum_key_1" content="meta_datum_value_1" />
    <meta name="meta_datum_key_2" content="meta_datum_value_2" />
</head>

<body>
    <div class="il-page-overlay">Overlay Stub</div>
    <div class="il-layout-page">
        <header>
            <div class="header-inner">
                <div class="il-logo"><span class="hidden-xs">Logo Stub</span><span class="visible-xs">Responsive Logo Stub</span>
                    <div class="il-pagetitle">pagetitle</div>
                </div>MetaBar Stub
            </div>
        </header>
        <div class="il-system-infos"></div>
        <div class="nav il-maincontrols">MainBar Stub</div>
        <main class="il-layout-page-content">some content</main>
    </div>
    <script>il.Util.addOnLoad(function() {});</script>
</body>

</html>');
        $this->assertEquals($expected, $html);
    }


    public function getUIFactory(): NoUIFactory
    {
        return new class () extends NoUIFactory {
            public function button(): \ILIAS\UI\Component\Button\Factory
            {
                return new Button\Factory();
            }
            public function dropdown(): Factory
            {
                return new Dropdown\Factory();
            }
        };
    }

    public function testRenderingWithCrumbs(): void
    {
        $crumbs = new Crumbs([
            new CrumbEntry("label1", '#'),
            new CrumbEntry("label2", '#'),
            new CrumbEntry("label3", '#')
        ]);
        $r = $this->getDefaultRenderer(null, [$this->metabar, $this->mainbar, $this->logo, $this->overlay]);

        $stdpage = $this->factory->standard(
            $this->contents,
            $this->metabar,
            $this->mainbar,
            $crumbs,
            $this->logo,
            $this->responsive_logo,
            'favicon.ico',
            $this->overlay,
            null,
            $this->title
        );

        $html = $this->brutallyTrimHTML($r->render($stdpage));

        $exptected = $this->brutallyTrimHTML('<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>:</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <style></style>
</head>

<body>
    <div class="il-page-overlay">Overlay Stub</div>
    <div class="il-layout-page">
        <header>
            <div class="header-inner">
                <div class="il-logo"><span class="hidden-xs">Logo Stub</span><span class="visible-xs">Responsive Logo Stub</span>
                    <div class="il-pagetitle">pagetitle</div>
                </div>
                <nav class="il-header-locator">
                    <div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="id_3" aria-haspopup="true" aria-expanded="false" aria-controls="id_3_menu">label3<span class="caret"></span></button>
                        <ul id="id_3_menu" class="dropdown-menu">
                            <li><button class="btn btn-link" data-action="#" id="id_1">label2</button></li>
                            <li><button class="btn btn-link" data-action="#" id="id_2">label1</button></li>
                        </ul>
                    </div>
                </nav>MetaBar Stub
            </div>
        </header>
        <div class="il-system-infos"></div>
        <div class="nav il-maincontrols">MainBar Stub</div>
        <main class="il-layout-page-content">
                <div class="breadcrumbs">
                    <nav aria-label="breadcrumbs_aria_label" class="breadcrumb_wrapper">
                        <div class="breadcrumb"><span class="crumb"><a href="#">label1</a></span><span class="crumb"><a href="#">label2</a></span><span class="crumb"><a href="#">label3</a></span></div>
                    </nav>
                </div>some content
        </main>
    </div>
    <script>il.Util.addOnLoad(function() {});</script>
</body>

</html>');
        $this->assertEquals($exptected, $html);
    }
}
