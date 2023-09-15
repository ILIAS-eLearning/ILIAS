<?php

use PHPUnit\Framework\TestCase;

/**
 * Test dashboard settings repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class DataSetImportParserTest extends TestCase
{
    protected ilPDSelectedItemsBlockViewSettings $view_settings;

    protected function setUp(): void
    {
        $GLOBALS["DIC"] = new \ILIAS\DI\Container();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS["DIC"]);
    }

    public function testInstanceAndParseValidXML(): void
    {
        $map_mock = $this->createMock(ilImportMapping::class);
        $ds_mock = $this->createMock(ilDataSet::class);
        $parser = new ilDataSetImportParser(
            "ent",
            "1.0.0",
            "<xml></xml>",
            $ds_mock,
            $map_mock
        );
        $this->assertInstanceOf(
            \ilDataSetImportParser::class,
            $parser
        );
    }
}
