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
