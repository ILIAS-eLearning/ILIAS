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

declare(strict_types=1);

namespace ILIAS\GlobalScreen\Scope\Layout;

use PHPUnit\Framework\TestCase;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaContent;
use ILIAS\Data\Meta\Html;

require_once('./libs/composer/vendor/autoload.php');

/**
 * Class MetaDataTest
 *
 * @author                 Fabian Schmid <fs@studer-raimann.ch>
 */
class MetaDataTest extends TestCase
{
    public MetaContent $meta_content;

    protected function setUp(): void
    {
        parent::setUp();
        $this->meta_content = new MetaContent('1.0');
    }

    public function testAddMetaDatum(): void
    {
        $html = "test_html";
        $html_meta_data = $this->getMockedTag($html);
        $this->meta_content->addMetaDatum($html_meta_data);
        $collection = $this->meta_content->getMetaData();

        $first_item = $collection[0];
        $this->assertInstanceOf(Html\Tag::class, $first_item);
        $this->assertEquals($html, $first_item->toHtml());
    }

    public function testAddMetaDatumWithDuplicate(): void
    {
        $meta_datum_key = 'key';
        $meta_datum_1_value = 'value_1';
        $meta_datum_2_value = 'value_2';
        $meta_datum_1 = new Html\UserDefined($meta_datum_key, $meta_datum_1_value);
        $meta_datum_2 = new Html\UserDefined($meta_datum_key, $meta_datum_2_value);

        $this->meta_content->addMetaDatum($meta_datum_1);
        $first_item = $this->meta_content->getMetaData()[$meta_datum_key];

        $this->assertInstanceOf(Html\UserDefined::class, $first_item);
        $this->assertEquals($meta_datum_1_value, $first_item->getValue());

        $this->meta_content->addMetaDatum($meta_datum_2);
        $first_item = $this->meta_content->getMetaData()[$meta_datum_key];

        $this->assertInstanceOf(Html\UserDefined::class, $first_item);
        $this->assertNotEquals($meta_datum_1_value, $first_item->getValue());
        $this->assertEquals($meta_datum_2_value, $first_item->getValue());
    }

    public function getMockedTag(string $html): Html\Tag
    {
        return new class ($html) extends Html\Tag {
            public function __construct(
                protected string $html
            ) {
            }

            public function toHtml(): string
            {
                return $this->html;
            }
        };
    }
}
