<?php

declare(strict_types=1);

require_once('libs/composer/vendor/autoload.php');

use PHPUnit\Framework\TestCase;

class ilSkinStyleLessCategoryTest extends TestCase
{
    public function testConstruct() : void
    {
        $category = new ilSystemStyleLessCategory('name', 'comment');
        $this->assertEquals('name', $category->getName());
        $this->assertEquals('comment', $category->getComment());
    }

    public function testSetters() : void
    {
        $category = new ilSystemStyleLessCategory('name', 'comment');

        $category->setName('newName');
        $category->setComment('newComment');

        $this->assertEquals('newName', $category->getName());
        $this->assertEquals('newComment', $category->getComment());
    }

    public function testToString() : void
    {
        $category = new ilSystemStyleLessCategory('name', 'comment');

        $this->assertEquals("//== name\n//\n//## comment\n", (string) $category);
    }
}
