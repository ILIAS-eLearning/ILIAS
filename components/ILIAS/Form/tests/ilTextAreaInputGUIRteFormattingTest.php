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

use ILIAS\DI\Container;
use PHPUnit\Framework\TestCase;

/**
 * Mantis 0048391: TinyMCE 7 emits underline/strikethrough as span[style] / <s>.
 * Default RTE tag set must keep that markup through ilUtil::secureString.
 */
class ilTextAreaInputGUIRteFormattingTest extends TestCase
{
    private ?Container $original_dic = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->original_dic = $GLOBALS['DIC'] ?? null;

        $dic = new Container();
        $GLOBALS['DIC'] = $dic;
        $dic['ilCtrl'] = fn() => $this->createMock(ilCtrl::class);
        $dic['lng'] = fn() => $this->createMock(ilLanguage::class);
    }

    protected function tearDown(): void
    {
        if ($this->original_dic instanceof Container) {
            $GLOBALS['DIC'] = $this->original_dic;
        } else {
            unset($GLOBALS['DIC']);
        }
        parent::tearDown();
    }

    public function testStandardRteTagsKeepTinyMceUnderlineAndStrikethrough(): void
    {
        $input = new ilTextAreaInputGUI('feedback', 'feedback');
        $input->setUseRte(true);

        $tiny_mce_html = '<p><strong>bold</strong> <em>italic</em>'
            . ' <span style="text-decoration: underline;">under</span>'
            . ' <span style="text-decoration: line-through;">strike-span</span>'
            . ' <s>s-tag</s></p>';

        $sanitized = ilUtil::secureString($tiny_mce_html, true, $input->getRteTagString());

        $this->assertStringContainsString('<strong>bold</strong>', $sanitized);
        $this->assertStringContainsString('<em>italic</em>', $sanitized);
        $this->assertStringContainsString('text-decoration: underline', $sanitized);
        $this->assertStringContainsString('line-through', $sanitized);
        $this->assertStringContainsString('<s>s-tag</s>', $sanitized);
    }
}
