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

namespace ILIAS\Tests\Refinery\Encode;

use ILIAS\Refinery\Encode\Group;
use PHPUnit\Framework\TestCase;
use ILIAS\Refinery\Encode\Transformation\HTMLAttributeValue;
use ILIAS\Refinery\Encode\Transformation\HTMLSpecialCharsAsEntities;
use ILIAS\Refinery\Encode\Transformation\Json;
use ILIAS\Refinery\Encode\Transformation\URL;

class GroupTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(Group::class, new Group());
    }

    public function testHtmlAttributeValue(): void
    {
        $group = new Group();
        $this->assertInstanceOf(HTMLAttributeValue::class, $group->htmlAttributeValue());
    }

    public function testHtmlSpecialCharsAsEntities(): void
    {
        $group = new Group();
        $this->assertInstanceOf(HTMLSpecialCharsAsEntities::class, $group->htmlSpecialCharsAsEntities());
    }

    public function testJson(): void
    {
        $group = new Group();
        $this->assertInstanceOf(Json::class, $group->json());
    }

    public function testUrl(): void
    {
        $group = new Group();
        $this->assertInstanceOf(URL::class, $group->url());
    }
}
