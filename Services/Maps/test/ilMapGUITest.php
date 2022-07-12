<?php declare(strict_types=1);

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

class ilMapGUITest extends TestCase
{
    protected function setUp() : void
    {
        $this->gui = new class() extends ilMapGUI {
            public function __construct()
            {
            }

            public function getHtml() : string
            {
            }
    
            public function getUserListHtml() : string
            {
            }
        };
    }

    /**
     * @dataProvider properties
     */
    public function testSettersAndGetters($name, $value) : void
    {
        $set = "set$name";
        $get = "get$name";
        $this->gui->$set($value);
        $this->assertEquals($value, $this->gui->$get());
    }

    public function properties() : array
    {
        return [
            ["MapId", "a_map_id"],
            ["Width", "a_width"],
            ["Height", "a_height"],
            ["Latitude", "a_latitude"],
            ["Longitude", "a_longitude"],
            ["Zoom", 50],
            ["EnableTypeControl", true],
            ["EnableNavigationControl", false],
            ["EnableUpdateListener", true],
            ["EnableLargeMapControl", false],
            ["EnableCentralMarker", true]
        ];
    }
}
