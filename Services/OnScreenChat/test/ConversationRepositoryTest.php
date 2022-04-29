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

use ILIAS\OnScreenChat\Repository\Conversation;

class ConversationRepositoryTest extends ilOnScreenChatBaseTest
{
    public function testConversationsCanBeRetrieved() : void
    {
        $conversations_fixture = [
            [
                'conversation' => [
                    'id' => '1',
                    'is_group' => '1',
                    'participants' => json_encode([['id' => 1], ['id' => 2], ['id' => 3]], JSON_THROW_ON_ERROR)
                ],
                'messages' => [
                    [
                        'id' => '1',
                        'message' => 'Hello World',
                        'user_id' => '1',
                        'timestamp' => (string) time()
                    ],
                ]
            ],
            [
                'conversation' => [
                    'id' => '2',
                    'is_group' => '0',
                    'participants' => json_encode([['id' => 3], ['id' => 4]], JSON_THROW_ON_ERROR)
                ],
                'messages' => [
                    [
                        'id' => '2',
                        'message' => 'Hello World',
                        'user_id' => '1',
                        'timestamp' => (string) time()
                    ],
                ]
            ],
            [
                'conversation' => [
                    'id' => '3',
                    'is_group' => '0',
                    'participants' => json_encode([['id' => 5], ['id' => 1]], JSON_THROW_ON_ERROR)
                ],
                'messages' => [
                    [
                        'id' => '3',
                        'message' => 'Hello World',
                        'user_id' => '1',
                        'timestamp' => (string) time()
                    ],
                ]
            ],
            [
                'conversation' => [
                    'id' => '4',
                    'is_group' => '0',
                    'participants' => json_encode([['id' => 6], ['id' => 1]], JSON_THROW_ON_ERROR)
                ],
                'messages' => []
            ],
        ];

        $user = $this->getMockBuilder(ilObjUser::class)->onlyMethods(['getId'])->disableOriginalConstructor()->getMock();
        $user->method('getId')->willReturn(1);
        $db = $this->createMock(ilDBInterface::class);
        $resultMock = $this->createMock(ilDBStatement::class);

        $db->expects($this->once())
            ->method('query')
            ->with($this->stringContains('FROM osc_conversation'))
            ->willReturn($resultMock);

        $db->expects($this->exactly(count($conversations_fixture) - 1))
            ->method('queryF')
            ->with($this->stringContains('FROM osc_messages'))
            ->willReturn($resultMock);

        $db->expects($this->exactly((count($conversations_fixture) * 2) - 1 + 1))->method('fetchAssoc')->with($resultMock)->willReturnOnConsecutiveCalls(
            $conversations_fixture[0]['conversation'],
            $conversations_fixture[0]['messages'][0],
            $conversations_fixture[1]['conversation'],
            $conversations_fixture[2]['conversation'],
            $conversations_fixture[2]['messages'][0],
            $conversations_fixture[3]['conversation'],
            null
        );

        $repository = new Conversation(
            $db,
            $user
        );

        $conversations = $repository->findByIds(array_map(static function (array $conversation) : string {
            return $conversation['conversation']['id'];
        }, $conversations_fixture));

        self::assertCount(count($conversations_fixture) - 1, $conversations);

        self::assertSame($conversations_fixture[0]['conversation']['id'], $conversations[0]->getId());
        self::assertTrue($conversations[0]->isGroup());
        self::assertSame($conversations_fixture[0]['messages'][0]['id'], $conversations[0]->getLastMessage()->getId());

        self::assertFalse($conversations[1]->isGroup());
        self::assertSame($conversations_fixture[2]['conversation']['id'], $conversations[1]->getId());
        self::assertSame($conversations_fixture[2]['messages'][0]['id'], $conversations[1]->getLastMessage()->getId());

        self::assertFalse($conversations[2]->isGroup());
        self::assertSame($conversations_fixture[3]['conversation']['id'], $conversations[2]->getId());
        self::assertSame('', $conversations[2]->getLastMessage()->getId());
    }
}
