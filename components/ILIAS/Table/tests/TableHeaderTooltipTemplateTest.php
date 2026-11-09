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
 */

use PHPUnit\Framework\TestCase;

final class TableHeaderTooltipTemplateTest extends TestCase
{
    private string $template;

    protected function setUp(): void
    {
        $template = file_get_contents(
            __DIR__ . '/../templates/default/tpl.table2.html'
        );

        $this->assertNotFalse($template);
        $this->template = $template;
    }

    public function testTooltipIsAttachedToNonSortableHeaderContent(): void
    {
        $block = $this->getBlock('tbl_header_no_link');

        $this->assertMatchesRegularExpression(
            '/<span\b[^>]*\{TBL_HEADER_CELL_TITLE\}[^>]*>/',
            $block
        );
        $this->assertDoesNotMatchRegularExpression(
            '/<th\b[^>]*\{TBL_HEADER_CELL_TITLE\}[^>]*>/',
            $block
        );
    }

    public function testTooltipIsAttachedInsideSortableHeaderLink(): void
    {
        $block = $this->getBlock('tbl_header_cell');

        $this->assertMatchesRegularExpression(
            '/<a\b[^>]*title="\{TBL_ORDER_ALT\}"[^>]*>.*'
            . '<span\b[^>]*\{TBL_HEADER_CELL_TITLE\}[^>]*>/s',
            $block
        );
        $this->assertDoesNotMatchRegularExpression(
            '/<th\b[^>]*\{TBL_HEADER_CELL_TITLE\}[^>]*>/',
            $block
        );
    }

    private function getBlock(string $name): string
    {
        $name = preg_quote($name, '/');
        $matched = preg_match(
            '/<!-- BEGIN ' . $name . ' -->(.*?)<!-- END ' . $name . ' -->/s',
            $this->template,
            $matches
        );

        $this->assertSame(1, $matched);

        return $matches[1];
    }
}
