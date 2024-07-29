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

use ilDBStatement;
use ILIAS\Test\Participants\ParticipantRepository;
use ilTestBaseTestCase;

class ParticipantRepositoryTest extends ilTestBaseTestCase
{
    public function test_lookupTestIdByActiveId_withValidId(): void
    {
        global $DIC;

        $ilDBStatementMock = $this->createMock(ilDBStatement::class);

        $test_fi = 5;

        $this->mockServiceMethod(service_name: "ilDB", method: "fetchAssoc", expects: $this->once(), will_return: ['test_fi' => $test_fi]);
        $this->mockServiceMethod(service_name: "ilDB", method: "queryF", expects: $this->once(), will_return: $ilDBStatementMock);
        $this->mockServiceMethod(service_name: "ilDB", method: "numRows", expects: $this->once(), with: [$ilDBStatementMock], will_return: $test_fi);

        $repo = new ParticipantRepository($DIC['ilDB']);

        $this->assertSame($test_fi, $repo->lookupTestIdByActiveId(1));
    }

    public function test_lookupTestIdByActiveId_withInvalidId(): void
    {
        global $DIC;

        $ilDBStatementMock = $this->createMock(ilDBStatement::class);

        $this->mockServiceMethod(service_name: "ilDB", method: "fetchAssoc", expects: $this->never());
        $this->mockServiceMethod(service_name: "ilDB", method: "queryF", expects: $this->once(), will_return: $ilDBStatementMock);
        $this->mockServiceMethod(service_name: "ilDB", method: "numRows", expects: $this->once(), with: [$ilDBStatementMock], will_return: 0);

        $repo = new ParticipantRepository($DIC['ilDB']);

        $this->assertSame(-1, $repo->lookupTestIdByActiveId(1));
    }
}
