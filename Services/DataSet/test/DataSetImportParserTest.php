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

    protected function tearDown() : void
    {
    }

    public function testInstanceAndParseValidXML()
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
        $this->assertEquals(
            "ilDataSetImportParser",
            get_class($parser)
        );
    }
}
