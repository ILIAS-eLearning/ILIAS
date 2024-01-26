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

namespace ILIAS\GlobalScreen\Scope\Layout;

use PHPUnit\Framework\TestCase;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaContent;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaData\MetaDatum;

require_once('./libs/composer/vendor/autoload.php');

/**
 * Class MetaDataTest
 *
 * @author                 Fabian Schmid <fs@studer-raimann.ch>
 */
class MetaDataTest extends TestCase
{
    /**
     * @var \ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaContent
     */
    public $meta_content;

    protected function setUp() : void
    {
        parent::setUp();
        $this->meta_content = new MetaContent('1.0');
    }

    public function testAddMetaDatum() : void
    {
        $key = 'key';
        $value = 'value';
        $this->meta_content->addMetaDatum($key, $value);
        $collection = $this->meta_content->getMetaData();

        $first_item = iterator_to_array($collection->getItems())[0];
        $this->assertInstanceOf(MetaDatum::class, $first_item);
        $this->assertEquals($key, $first_item->getKey());
        $this->assertEquals($value, $first_item->getValue());
    }
}
