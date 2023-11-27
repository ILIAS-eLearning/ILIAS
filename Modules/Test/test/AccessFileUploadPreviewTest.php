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

namespace ILIAS\Modules\Test\test;

use PHPUnit\Framework\TestCase;
use ILIAS\Modules\Test\AccessFileUploadPreview;
use ILIAS\Modules\Test\Incident;
use ilDBInterface;
use ilDBStatement;
use ilDBConstants;
use ilAccess;
use Closure;

class AccessFileUploadPreviewTest extends TestCase
{
    public function testConstruct() : void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)->disableOriginalConstructor()->getMock();
        $access = $this->getMockBuilder(ilAccess::class)->disableOriginalConstructor()->getMock();
        $this->assertInstanceOf(AccessFileUploadPreview::class, new AccessFileUploadPreview($database, $access));
    }

    public function testNoUploadPath() : void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)->disableOriginalConstructor()->getMock();
        $access = $this->getMockBuilder(ilAccess::class)->disableOriginalConstructor()->getMock();

        $instance = new AccessFileUploadPreview($database, $access);
        $result = $instance->isPermitted('/data/some/path/file.pdf');
        $this->assertFalse($result->isOk());
    }

    public function testFalseWithInvalidId() : void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)->disableOriginalConstructor()->getMock();
        $access = $this->getMockBuilder(ilAccess::class)->disableOriginalConstructor()->getMock();
        $statement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();

        $database->expects(self::once())->method('queryF')->with('SELECT obj_fi FROM qpl_questions WHERE question_id = %s', [ilDBConstants::T_INTEGER], [383])->willReturn($statement);
        $database->expects(self::once())->method('fetchAssoc')->with($statement)->willReturn(null);

        $instance = new AccessFileUploadPreview($database, $access);
        $result = $instance->isPermitted('http://my-ilias/assessment/qst_preview/123/383/fileuploads/my-file.pdf');
        $this->assertTrue($result->isOk());
        $this->assertFalse($result->value());
    }

    /**
     * @dataProvider types
     */
    public function testWithTypes(?string $type, bool $permitted, ?string $requires_permission) : void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)->disableOriginalConstructor()->getMock();
        $access = $this->getMockBuilder(ilAccess::class)->disableOriginalConstructor()->getMock();
        $statement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $incident = $this->getMockBuilder(Incident::class)->disableOriginalConstructor()->getMock();

        $ref_called = 0;
        $type_called = 0;
        $references_of = $this->expectCall(383, ['987'], $ref_called);
        $type_of = $this->expectCall(987, $type, $type_called);

        $database->expects(self::once())->method('queryF')->with('SELECT obj_fi FROM qpl_questions WHERE question_id = %s', [ilDBConstants::T_INTEGER], [383])->willReturn($statement);
        $database->expects(self::once())->method('fetchAssoc')->with($statement)->willReturn(['obj_fi' => '383']);

        $incident->expects(self::once())->method('any')->willReturnCallback(function (callable $call_me, array $ref_ids) : bool {
            $this->assertEquals(['987'], $ref_ids);
            return $call_me('987');
        });

        if (null === $requires_permission) {
            $access->expects(self::never())->method('checkAccess');
        } else {
            $access->expects(self::once())->method('checkAccess')->with($requires_permission, '', 987)->willReturn($permitted);
        }


        $instance = new AccessFileUploadPreview($database, $access, $incident, $references_of, $type_of);
        $result = $instance->isPermitted('http://my-ilias/assessment/qst_preview/123/383/fileuploads/my-file.pdf');
        $this->assertTrue($result->isOk());
        $this->assertSame($permitted, $result->value());

        $this->assertSame(1, $ref_called);
        $this->assertSame(1, $type_called);
    }

    public function types() : array
    {
        return [
            'Type qpl with access rights.' => ['qpl', false, 'read'],
            'Type qpl without access rights.' => ['qpl', true, 'read'],
            'Type tst with access rights.' => ['tst', false, 'write'],
            'Type tst without access rights.' => ['tst', true, 'write'],
            'Type crs will never has access rights.' => ['crs', false, null],
            'Unknown types will never have access rights.' => [null, false, null],
        ];
    }

    private function expectCall($expected, $return, &$called): Closure
    {
        return function ($value) use ($expected, $return, &$called) {
            $this->assertSame($expected, $value);
            $called++;
            return $return;
        };
    }
}
