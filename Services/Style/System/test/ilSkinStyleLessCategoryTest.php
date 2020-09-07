<?php

include_once("./Services/Style/System/classes/Less/class.ilSystemStyleLessCategory.php");

/**
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$*
 */
class ilSkinStyleLessCategoryTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $category = new ilSystemStyleLessCategory("name", "comment");
        $this->assertEquals("name", $category->getName());
        $this->assertEquals("comment", $category->getComment());
    }

    public function testSetters()
    {
        $category = new ilSystemStyleLessCategory("name", "comment");

        $category->setName("newName");
        $category->setComment("newComment");

        $this->assertEquals("newName", $category->getName());
        $this->assertEquals("newComment", $category->getComment());
    }

    public function testToString()
    {
        $category = new ilSystemStyleLessCategory("name", "comment");

        $this->assertEquals("//== name\n//\n//## comment\n", (string) $category);
    }
}
