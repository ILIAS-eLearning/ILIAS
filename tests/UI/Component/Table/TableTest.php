<?php declare(strict_types=1);

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation\Component as I;

/**
 * Basic Tests for all Tables.
 */
class TableTest extends ILIAS_UI_TestBase
{
    public function testBaseTable()
    {
        $title = 'some title';
        $table = new class($title) extends I\Table\Table {
        };
        $this->assertEquals($title, $table->getTitle());

        $title = 'some other title';
        $table = $table->withTitle($title);
        $this->assertEquals($title, $table->getTitle());
    }
}
