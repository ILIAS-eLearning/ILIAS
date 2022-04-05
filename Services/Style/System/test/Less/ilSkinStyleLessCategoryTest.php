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
