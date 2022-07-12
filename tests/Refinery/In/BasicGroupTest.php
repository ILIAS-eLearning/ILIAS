<?php declare(strict_types=1);

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

namespace ILIAS\Tests\Refinery\In;

use ILIAS\Refinery\In\Parallel;
use ILIAS\Refinery\In\Series;
use ILIAS\Refinery\In\Group as InGroup;
use ILIAS\Refinery\To\Transformation\IntegerTransformation;
use ILIAS\Refinery\To\Transformation\StringTransformation;
use ILIAS\Tests\Refinery\TestCase;

class BasicGroupTest extends TestCase
{
    private InGroup $group;

    protected function setUp() : void
    {
        $this->group = new InGroup();
    }
    
    public function testParallelInstanceCreated() : void
    {
        $transformation = $this->group->parallel([new StringTransformation(), new IntegerTransformation()]);
        $this->assertInstanceOf(Parallel::class, $transformation);
    }

    public function testSeriesInstanceCreated() : void
    {
        $transformation = $this->group->series([new StringTransformation(), new IntegerTransformation()]);
        $this->assertInstanceOf(Series::class, $transformation);
    }
}
