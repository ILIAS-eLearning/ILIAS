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

namespace ILIAS\MetaData\Search\Filters;

use PHPUnit\Framework\TestCase;

class FilterAndFactoryTest extends TestCase
{
    public function testObjID()
    {
        $factory = new Factory();
        $filter = $factory->get(23, 5, 'type');
        $this->assertSame(23, $filter->objID());
    }

    public function testObjIDPlaceholder()
    {
        $factory = new Factory();
        $filter = $factory->get(Placeholder::ANY, 5, 'type');
        $this->assertSame(Placeholder::ANY, $filter->objID());
    }

    public function testSubID()
    {
        $factory = new Factory();
        $filter = $factory->get(23, 5, 'type');
        $this->assertSame(5, $filter->subID());
    }

    public function testSubIDPlaceholder()
    {
        $factory = new Factory();
        $filter = $factory->get(245, Placeholder::OBJ_ID, 'type');
        $this->assertSame(Placeholder::OBJ_ID, $filter->subID());
    }

    public function testType()
    {
        $factory = new Factory();
        $filter = $factory->get(23, 5, 'type');
        $this->assertSame('type', $filter->type());
    }

    public function testTypePlaceholder()
    {
        $factory = new Factory();
        $filter = $factory->get(23, 5, Placeholder::ANY);
        $this->assertSame(Placeholder::ANY, $filter->type());
    }
}
