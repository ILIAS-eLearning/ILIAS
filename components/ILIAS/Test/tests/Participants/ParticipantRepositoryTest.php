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

namespace ILIAS\Test\Tests\Participants;

use ilDBInterface;
use ilDBStatement;
use ILIAS\Test\Participants\ParticipantRepository;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;

class ParticipantRepositoryTest extends ilTestBaseTestCase
{
    /**
     * @dataProvider lookupTestIdByActiveIdDataProvider
     * @throws \Exception|Exception
     */
    public function testLookupTestIdByActiveId_withValidId(array $input, int $output): void
    {
        $test_fi = $input['active_id'];
        $valid = $input['valid'];

        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) use ($test_fi, $valid) {
            $il_db_statement_mock = $this->createMock(ilDBStatement::class);

            $mock
                ->expects($valid && $test_fi >= 1 ? $this->once() : $this->never())
                ->method('fetchAssoc')
                ->willReturn(['test_fi' => $test_fi]);

            $mock
                ->expects($this->once())
                ->method('queryF')
                ->willReturn($il_db_statement_mock);

            $mock
                ->expects($this->once())
                ->method('numRows')
                ->with($il_db_statement_mock)
                ->willReturn($valid ? $test_fi : 0);
        });

        $participant_repository = $this->createInstanceOf(ParticipantRepository::class);
        $this->assertEquals($output, $participant_repository->lookupTestIdByActiveId($test_fi));
    }

    public static function lookupTestIdByActiveIdDataProvider(): array
    {
        return [
            'negative_one_true' => [['active_id' => -1, 'valid' => true], -1],
            'negative_one_false' => [['active_id' => -1, 'valid' => false], -1],
            'zero_true' => [['active_id' => 0, 'valid' => true], -1],
            'zero_false' => [['active_id' => 0, 'valid' => false], -1],
            'one_true' => [['active_id' => 1, 'valid' => true], 1],
            'one_false' => [['active_id' => 1, 'valid' => false], -1]
        ];
    }
}
