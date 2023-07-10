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

class ilSkinStyleTest extends TestCase
{
    protected ilSkinStyle $style1;

    public function testStyleNameAndId(): void
    {
        $this->style1 = new ilSkinStyle('style1', 'Style 1');
        $this->assertEquals('style1', $this->style1->getId());
        $this->assertEquals('Style 1', $this->style1->getName());
    }

    public function testStyleProperties(): void
    {
        $this->style1 = new ilSkinStyle('style1', 'Style 1');
        $this->style1->setId('id');
        $this->style1->setName('name');
        $this->style1->setCssFile('css');
        $this->style1->setImageDirectory('image');
        $this->style1->setSoundDirectory('sound');

        $this->assertEquals('id', $this->style1->getId());
        $this->assertEquals('name', $this->style1->getName());
        $this->assertEquals('css', $this->style1->getCssFile());
        $this->assertEquals('image', $this->style1->getImageDirectory());
        $this->assertEquals('sound', $this->style1->getSoundDirectory());
    }
}
