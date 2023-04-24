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

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component as I;

/**
 * Basic Tests for all Tables.
 */
class TableTest extends ILIAS_UI_TestBase
{
    public function testBaseTable()
    {
        $title = 'some title';
        $table = new class ($title) extends I\Table\Table {
        };
        $this->assertEquals($title, $table->getTitle());

        $title = 'some other title';
        $table = $table->withTitle($title);
        $this->assertEquals($title, $table->getTitle());
    }
}
