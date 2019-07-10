<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../../Base.php");

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
	public function setUp(): void {


		$this->metabar = $this->createMock(MetaBar::class);
		$this->mainbar = $this->createMock(MainBar::class);
		$this->crumbs = $this->createMock(Breadcrumbs::class);
		$this->logo = $this->createMock(Image::class);
		$this->contents = array(new Legacy('some content'));

		$this->factory = new Page\Factory();
		$this->stdpage = $this->factory->standard(
			$this->contents,
			$this->metabar,
			$this->mainbar,
			$this->crumbs,
			$this->logo
		);
	}

	public function testConstruction()
	{
		$this->assertInstanceOf(
			"ILIAS\\UI\\Component\\Layout\\Page\\Standard",
			$this->stdpage
		);
	}

	public function testGetContent()
	{
		$this->assertEquals(
			$this->contents,
			$this->stdpage->getContent()
		);
	}

	public function testGetMetabar()
	{
		$this->assertEquals(
			$this->metabar,
			$this->stdpage->getMetabar()
		);
	}

	public function testGetMainbar()
	{
		$this->assertEquals(
			$this->mainbar,
			$this->stdpage->getMainbar()
		);
	}

	public function testGetBreadcrumbs()
	{
		$this->assertEquals(
			$this->crumbs,
			$this->stdpage->getBreadcrumbs()
		);
	}

	public function testGetLogo()
	{
		$this->assertEquals(
			$this->logo,
			$this->stdpage->getLogo()
		);
	}

	public function testWithWrongContents()
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
}
