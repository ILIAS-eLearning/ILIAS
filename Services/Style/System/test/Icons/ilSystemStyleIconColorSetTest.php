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

require_once('libs/composer/vendor/autoload.php');

use PHPUnit\Framework\TestCase;

class ilSystemStyleIconColorSetTest extends TestCase
{
    public function testConstruct() : void
    {
        $set = new ilSystemStyleIconColorSet();

        $this->assertEquals([], $set->getColors());
    }

    public function testAddColor() : void
    {
        $color1 = new ilSystemStyleIconColor('id1', 'name', 'FF0000', 'description');
        $color2 = new ilSystemStyleIconColor('id2', 'name', 'FF0000', 'description');

        $set = new ilSystemStyleIconColorSet();

        $this->assertEquals([], $set->getColors());

        $set->addColor($color1);

        $this->assertCount(1, $set->getColors());
        $this->assertEquals($color1, $set->getColorById('id1'));

        $set->addColor($color2);
        $this->assertCount(2, $set->getColors());
        $this->assertEquals($color1, $set->getColorById('id1'));
        $this->assertEquals($color2, $set->getColorById('id2'));

        $set->addColor($color2);
        $this->assertCount(2, $set->getColors());
        $this->assertEquals($color1, $set->getColorById('id1'));
        $this->assertEquals($color2, $set->getColorById('id2'));
    }

    public function testGetInvalidId() : void
    {
        $color1 = new ilSystemStyleIconColor('id1', 'name', 'FF0000', 'description');
        $set = new ilSystemStyleIconColorSet();
        $set->addColor($color1);

        try {
            $set->getColorById('idXY');
            $this->fail();
        } catch (ilSystemStyleException $e) {
            $this->assertEquals(ilSystemStyleException::INVALID_ID, $e->getCode());
        }
    }

    public function testDoesColorExist() : void
    {
        $color1 = new ilSystemStyleIconColor('id1', 'name', 'FF0000', 'description');
        $set = new ilSystemStyleIconColorSet();
        $set->addColor($color1);

        $this->assertTrue($set->doesColorExist('id1'));
        $this->assertFalse($set->doesColorExist('otherId'));
        $this->assertFalse($set->doesColorExist(''));
    }

    public function testMergeColorSet() : void
    {
        $color1 = new ilSystemStyleIconColor('id1', 'name', 'FF0000', 'description');
        $color2 = new ilSystemStyleIconColor('id2', 'name', 'FF0000', 'description');
        $color3 = new ilSystemStyleIconColor('id3', 'name', 'FF0000', 'description');

        $set1 = new ilSystemStyleIconColorSet();
        $set2 = new ilSystemStyleIconColorSet();

        $set1->addColor($color1);
        $set1->addColor($color2);

        $set2->addColor($color2);
        $set2->addColor($color3);

        $set1->mergeColorSet($set2);

        $this->assertCount(3, $set1->getColors());
        $this->assertEquals($color1, $set1->getColorById('id1'));
        $this->assertEquals($color2, $set1->getColorById('id2'));
        $this->assertEquals($color3, $set1->getColorById('id3'));

        $this->assertCount(2, $set2->getColors());
        $this->assertEquals($color2, $set2->getColorById('id2'));
        $this->assertEquals($color3, $set2->getColorById('id3'));
    }

    public function testGetColorsSortedAsArray() : void
    {
        $white = new ilSystemStyleIconColor('id1', 'name', 'FFFFFF', 'description');
        $black = new ilSystemStyleIconColor('id2', 'name', '000000', 'description');
        $grey = new ilSystemStyleIconColor('id3', 'name', 'AAAAAA', 'description');

        $red = new ilSystemStyleIconColor('id4', 'name', 'FF0000', 'description');
        $green = new ilSystemStyleIconColor('id5', 'name', '00FF00', 'description');
        $blue = new ilSystemStyleIconColor('id6', 'name', '0000FF', 'description');

        $ordered_array = [
                ilSystemStyleIconColor::GREY => [$black,$grey,$white],
                ilSystemStyleIconColor::RED => [$red],
                ilSystemStyleIconColor::GREEN => [$green],
                ilSystemStyleIconColor::BLUE => [$blue]
        ];

        $set1 = new ilSystemStyleIconColorSet();

        $set1->addColor($white);
        $set1->addColor($black);
        $set1->addColor($grey);
        $set1->addColor($red);
        $set1->addColor($green);
        $set1->addColor($blue);

        $this->assertEquals($ordered_array, $set1->getColorsSortedAsArray());
    }

    public function testAsArray() : void
    {
        $white = new ilSystemStyleIconColor('id1', 'name', 'FFFFFF', 'description');
        $black = new ilSystemStyleIconColor('id2', 'name', '000000', 'description');
        $grey = new ilSystemStyleIconColor('id3', 'name', 'AAAAAA', 'description');

        $red = new ilSystemStyleIconColor('id4', 'name', 'FF0000', 'description');
        $green = new ilSystemStyleIconColor('id5', 'name', '00FF00', 'description');
        $blue = new ilSystemStyleIconColor('id6', 'name', '0000FF', 'description');

        $as_array = ['id1','id2','id3','id4','id5','id6'];

        $set1 = new ilSystemStyleIconColorSet();

        $set1->addColor($white);
        $set1->addColor($black);
        $set1->addColor($grey);
        $set1->addColor($red);
        $set1->addColor($green);
        $set1->addColor($blue);

        $this->assertEquals($as_array, $set1->asArray());
    }

    public function testAsString() : void
    {
        $white = new ilSystemStyleIconColor('id1', 'name', 'FFFFFF', 'description');
        $black = new ilSystemStyleIconColor('id2', 'name', '000000', 'description');
        $grey = new ilSystemStyleIconColor('id3', 'name', 'AAAAAA', 'description');

        $red = new ilSystemStyleIconColor('id4', 'name', 'FF0000', 'description');
        $green = new ilSystemStyleIconColor('id5', 'name', '00FF00', 'description');
        $blue = new ilSystemStyleIconColor('id6', 'name', '0000FF', 'description');

        $as_string = 'id1; id2; id3; id4; id5; id6; ';

        $set1 = new ilSystemStyleIconColorSet();

        $set1->addColor($white);
        $set1->addColor($black);
        $set1->addColor($grey);
        $set1->addColor($red);
        $set1->addColor($green);
        $set1->addColor($blue);

        $this->assertEquals($as_string, $set1->asString());
    }
}
