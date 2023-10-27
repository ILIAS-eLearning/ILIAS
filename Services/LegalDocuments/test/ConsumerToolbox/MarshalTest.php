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

namespace ILIAS\LegalDocuments\test\ConsumerToolbox;

use ILIAS\Refinery\Constraint;
use ILIAS\LegalDocuments\ConsumerToolbox\Convert;
use ILIAS\Refinery\ByTrying;
use ILIAS\Refinery\In\Group as In;
use ILIAS\Refinery\Transformation;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\Refinery\Factory as Refinery;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\ConsumerToolbox\Marshal;

require_once __DIR__ . '/../ContainerMock.php';

class MarshalTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(Marshal::class, new Marshal($this->mock(Refinery::class)));
    }

    public function testDateTime(): void
    {
        $from = $this->mock(Transformation::class);
        $custom = $this->mock(Transformation::class);
        $date = $this->mock(Transformation::class);

        $refinery = $this->mockTree(Refinery::class, [
            'in' => $this->mockMethod(In::class, 'series', [[$custom, $date]], $from),
            'custom' => ['transformation' => $custom],
            'to' => ['dateTime' => $date],
        ]);

        $convert = (new Marshal($refinery))->dateTime();
        $this->assertSame($from, $convert->fromString());
        $this->assertSame($custom, $convert->toString());
    }

    public function testBoolean(): void
    {
        $from = $this->mock(ByTrying::class);
        $custom = $this->mock(Transformation::class);
        $bool = $this->mock(Transformation::class);
        $to = $this->mock(Transformation::class);

        $refinery = $this->mockTree(Refinery::class, [
            'custom' => ['transformation' => $custom],
            'kindlyTo' => ['string' => $to, 'bool' => $bool],
        ]);
        $refinery->expects(self::once())->method('byTrying')->with([$bool, $custom])->willReturn($from);

        $convert = (new Marshal($refinery))->boolean();
        $this->assertSame($from, $convert->fromString());
        $this->assertSame($to, $convert->toString());
    }

    public function testNullable(): void
    {
        $decorate = $this->mockTree(Convert::class, [
            'fromString' => $this->mock(Transformation::class),
            'toString' => $this->mock(Transformation::class),
        ]);

        $by_trying = $this->mock(ByTrying::class);
        $nice_null = $this->mock(Transformation::class);
        $null = $this->mock(Constraint::class);
        $always = $this->mock(Transformation::class);
        $series = $this->mock(Transformation::class);

        $refinery = $this->mockTree(Refinery::class, [
            'kindlyTo' => ['null' => $nice_null],
            'in' => $this->mockMethod(In::class, 'series', [[$null, $always]], $series),
            'always' => $always,
            'null' => $null,
        ]);
        $refinery->expects(self::exactly(2))->method('byTrying')->withConsecutive(
            [[$nice_null, $decorate->fromString()]],
            [[$series, $decorate->toString()]]
        )->willReturn($by_trying);

        $convert = (new Marshal($refinery))->nullable($decorate);
        $this->assertSame($by_trying, $convert->fromString());
        $this->assertSame($by_trying, $convert->toString());
    }

    public function testString(): void
    {
        $id = $this->mock(Transformation::class);

        $convert = (new Marshal($this->mockTree(Refinery::class, ['identity' => $id])))->string();
        $this->assertSame($id, $convert->fromString());
        $this->assertSame($id, $convert->toString());
    }
}
