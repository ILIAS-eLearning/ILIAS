<?php

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

declare(strict_types=1);

namespace ILIAS\COPage\Test\Layout;

use PHPUnit\Framework\TestCase;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PageLayoutTest extends \COPageTestBase
{
    public function testConstruction(): void
    {
        $layout = new \ilPageLayout(0);
        $this->assertEquals(
            \ilPageLayout::class,
            get_class($layout)
        );
    }

    public function testPreviewEmpty(): void
    {
        $layout = new \ilPageLayout(0);

        $page = $this->getEmptyPageWithDom();

        $layout->setXMLContent($page->getXMLFromDom());

        $this->assertStringContainsString(
            '<table class="il-style-layout-preview-wrapper">',
            $layout->getPreview()
        );
    }

    public function testPreviewPlaceholderText(): void
    {
        $layout = new \ilPageLayout(0);

        $page = $this->getEmptyPageWithDom();
        $pc = new \ilPCPlaceHolder($page);
        $pc->create($page, "pg");
        $pc->setContentClass("Text");
        $pc->setHeight("300");

        $layout->setXMLContent($page->getXMLFromDom());

        $this->assertStringContainsString(
            'class="ilc_TextPlaceHolderThumb"',
            $layout->getPreview()
        );
    }

    public function testPreviewPlaceholderMedia(): void
    {
        $layout = new \ilPageLayout(0);

        $page = $this->getEmptyPageWithDom();
        $pc = new \ilPCPlaceHolder($page);
        $pc->create($page, "pg");
        $pc->setContentClass("Media");
        $pc->setHeight("300");

        $layout->setXMLContent($page->getXMLFromDom());

        $this->assertStringContainsString(
            'class="ilc_MediaPlaceHolderThumb"',
            $layout->getPreview()
        );
    }

    public function testPreviewPlaceholderQuestion(): void
    {
        $layout = new \ilPageLayout(0);

        $page = $this->getEmptyPageWithDom();
        $pc = new \ilPCPlaceHolder($page);
        $pc->create($page, "pg");
        $pc->setContentClass("Question");
        $pc->setHeight("300");

        $layout->setXMLContent($page->getXMLFromDom());

        $this->assertStringContainsString(
            'class="ilc_QuestionPlaceHolderThumb"',
            $layout->getPreview()
        );
    }
}
