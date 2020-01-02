<?php

include_once("Services/Style/System/classes/Icons/class.ilSystemStyleIconColor.php");
include_once("Services/Style/System/classes/Icons/class.ilSystemStyleIconColorSet.php");
include_once("Services/Style/System/classes/Exceptions/class.ilSystemStyleColorException.php");
include_once("Services/Style/System/classes/Exceptions/class.ilSystemStyleException.php");


/**
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$*
 */
class ilSystemStyleIconColorSetTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $set = new ilSystemStyleIconColorSet();

        $this->assertEquals([], $set->getColors());
    }

    public function testAddColor()
    {
        $color1 = new ilSystemStyleIconColor("id1", "name", "FF0000", "description");
        $color2 = new ilSystemStyleIconColor("id2", "name", "FF0000", "description");

        $set = new ilSystemStyleIconColorSet();

        $this->assertEquals([], $set->getColors());

        $set->addColor($color1);

        $this->assertEquals(1, count($set->getColors()));
        $this->assertEquals($color1, $set->getColorById("id1"));

        $set->addColor($color2);
        $this->assertEquals(2, count($set->getColors()));
        $this->assertEquals($color1, $set->getColorById("id1"));
        $this->assertEquals($color2, $set->getColorById("id2"));

        $set->addColor($color2);
        $this->assertEquals(2, count($set->getColors()));
        $this->assertEquals($color1, $set->getColorById("id1"));
        $this->assertEquals($color2, $set->getColorById("id2"));
    }

    public function testGetInvalidId()
    {
        $color1 = new ilSystemStyleIconColor("id1", "name", "FF0000", "description");
        $set = new ilSystemStyleIconColorSet();
        $set->addColor($color1);

        try {
            $set->getColorById("idXY");
            $this->assertTrue(false);
        } catch (ilSystemStyleException $e) {
            $this->assertEquals(ilSystemStyleException::INVALID_ID, $e->getCode());
        }
    }

    public function testDoesColorExist()
    {
        $color1 = new ilSystemStyleIconColor("id1", "name", "FF0000", "description");
        $set = new ilSystemStyleIconColorSet();
        $set->addColor($color1);

        $this->assertTrue($set->doesColorExist("id1"));
        $this->assertFalse($set->doesColorExist("otherId"));
        $this->assertFalse($set->doesColorExist(""));
    }

    public function testMergeColorSet()
    {
        $color1 = new ilSystemStyleIconColor("id1", "name", "FF0000", "description");
        $color2 = new ilSystemStyleIconColor("id2", "name", "FF0000", "description");
        $color3 = new ilSystemStyleIconColor("id3", "name", "FF0000", "description");

        $set1 = new ilSystemStyleIconColorSet();
        $set2 = new ilSystemStyleIconColorSet();

        $set1->addColor($color1);
        $set1->addColor($color2);

        $set2->addColor($color2);
        $set2->addColor($color3);

        $set1->mergeColorSet($set2);

        $this->assertEquals(3, count($set1->getColors()));
        $this->assertEquals($color1, $set1->getColorById("id1"));
        $this->assertEquals($color2, $set1->getColorById("id2"));
        $this->assertEquals($color3, $set1->getColorById("id3"));

        $this->assertEquals(2, count($set2->getColors()));
        $this->assertEquals($color2, $set2->getColorById("id2"));
        $this->assertEquals($color3, $set2->getColorById("id3"));
    }

    public function testGetColorsSortedAsArray()
    {
        $white = new ilSystemStyleIconColor("id1", "name", "FFFFFF", "description");
        $black = new ilSystemStyleIconColor("id2", "name", "000000", "description");
        $grey = new ilSystemStyleIconColor("id3", "name", "AAAAAA", "description");

        $red = new ilSystemStyleIconColor("id4", "name", "FF0000", "description");
        $green = new ilSystemStyleIconColor("id5", "name", "00FF00", "description");
        $blue = new ilSystemStyleIconColor("id6", "name", "0000FF", "description");

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

    public function testAsArray()
    {
        $white = new ilSystemStyleIconColor("id1", "name", "FFFFFF", "description");
        $black = new ilSystemStyleIconColor("id2", "name", "000000", "description");
        $grey = new ilSystemStyleIconColor("id3", "name", "AAAAAA", "description");

        $red = new ilSystemStyleIconColor("id4", "name", "FF0000", "description");
        $green = new ilSystemStyleIconColor("id5", "name", "00FF00", "description");
        $blue = new ilSystemStyleIconColor("id6", "name", "0000FF", "description");

        $as_array = ["id1","id2","id3","id4","id5","id6"];

        $set1 = new ilSystemStyleIconColorSet();

        $set1->addColor($white);
        $set1->addColor($black);
        $set1->addColor($grey);
        $set1->addColor($red);
        $set1->addColor($green);
        $set1->addColor($blue);

        $this->assertEquals($as_array, $set1->asArray());
    }

    public function testAsString()
    {
        $white = new ilSystemStyleIconColor("id1", "name", "FFFFFF", "description");
        $black = new ilSystemStyleIconColor("id2", "name", "000000", "description");
        $grey = new ilSystemStyleIconColor("id3", "name", "AAAAAA", "description");

        $red = new ilSystemStyleIconColor("id4", "name", "FF0000", "description");
        $green = new ilSystemStyleIconColor("id5", "name", "00FF00", "description");
        $blue = new ilSystemStyleIconColor("id6", "name", "0000FF", "description");

        $as_string = "id1; id2; id3; id4; id5; id6; ";

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
