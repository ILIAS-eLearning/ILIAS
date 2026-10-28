<?php

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
