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

use ILIAS\OnScreenChat\Repository\Subscriber;

class SubscriberRepositoryTest extends ilOnScreenChatBaseTest
{
    public function testSubscribersCanBeRetrieved() : void
    {
        $user = $this->getMockBuilder(ilObjUser::class)->onlyMethods(['getId'])->disableOriginalConstructor()->getMock();
        $user->method('getId')->willReturn(1);
        $db = $this->createMock(ilDBInterface::class);
        $resultMock = $this->createMock(ilDBStatement::class);

        $db->expects($this->exactly(2))
            ->method('queryF')
            ->withConsecutive(
                [$this->stringContains('FROM osc_activity'), $this->isType('array'), $this->isType('array')],
                [$this->stringContains('FROM osc_messages'), $this->isType('array'), $this->isType('array')]
            )
            ->willReturn($resultMock);

        $db->expects($this->once())
            ->method('query')
            ->with($this->stringContains('FROM osc_conversation'))
            ->willReturn($resultMock);

        $db->expects($this->exactly(10))->method('fetchAssoc')->with($resultMock)->willReturnOnConsecutiveCalls(
            ['conversation_id' => '1'],
            ['conversation_id' => '2'],
            null,
            ['conversation_id' => '1'],
            ['conversation_id' => '3'],
            null,
            ['participants' => json_encode([['id' => 1], ['id' => 2], ['id' => 3]], JSON_THROW_ON_ERROR)],
            ['participants' => json_encode([['id' => 1], ['id' => 4]], JSON_THROW_ON_ERROR)],
            ['participants' => json_encode([['id' => 1], ['id' => 6]], JSON_THROW_ON_ERROR)],
            null,
        );

        $repository = new class($db, $user) extends Subscriber {
            public function getDataByUserIds(array $usrIds) : array
            {
                $data = [];
                foreach ($usrIds as $usrId) {
                    $data[$usrId] = [
                        'public_name' => 'User ' . $usrId,
                        'profile_image' => 'Image ' . $usrId
                    ];
                }

                return $data;
            }
        };

        $profile_data = $repository->getInitialUserProfileData();
        $this->assertCount(5, $profile_data);
    }
}
