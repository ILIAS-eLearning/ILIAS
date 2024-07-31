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

namespace Participants;

use ilDBInterface;
use ilDBStatement;
use ILIAS\Test\Participants\ParticipantRepository;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class ParticipantRepositoryTest extends ilTestBaseTestCase
{
    private ParticipantRepository $participantRepository;

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->participantRepository = $this->createInstanceOf(ParticipantRepository::class);
    }

    /**
     * @throws \Exception|Exception
     */
    public function test_lookupTestIdByActiveId_withValidId(): void
    {
        $test_fi = 5;

        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) use ($test_fi) {
            $ilDBStatementMock = $this->createMock(ilDBStatement::class);

            $mock
                ->expects($this->once())
                ->method('fetchAssoc')
                ->willReturn(['test_fi' => $test_fi]);

            $mock
                ->expects($this->once())
                ->method('queryF')
                ->willReturn($ilDBStatementMock);

            $mock
                ->expects($this->once())
                ->method('numRows')
                ->with($ilDBStatementMock)
                ->willReturn($test_fi);
        });

        $this->assertSame($test_fi, $this->participantRepository->lookupTestIdByActiveId(1));
    }

    /**
     * @throws \Exception|Exception
     */
    public function test_lookupTestIdByActiveId_withInvalidId(): void
    {
        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) use ($test_fi) {
            $ilDBStatementMock = $this->createMock(ilDBStatement::class);

            $mock
                ->expects($this->never())
                ->method('fetchAssoc')
                ->willReturn(['test_fi' => $test_fi]);

            $mock
                ->expects($this->once())
                ->method('queryF')
                ->willReturn($ilDBStatementMock);

            $mock
                ->expects($this->once())
                ->method('numRows')
                ->with($ilDBStatementMock)
                ->willReturn(0);
        });

        $this->assertSame(-1, $this->participantRepository->lookupTestIdByActiveId(1));
    }
}
