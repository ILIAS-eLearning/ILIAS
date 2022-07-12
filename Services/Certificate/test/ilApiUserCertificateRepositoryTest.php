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

use PHPUnit\Framework\MockObject\MockObject;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilApiUserCertificateRepositoryTest extends ilCertificateBaseTestCase
{
    /** @var MockObject&ilDBInterface */
    private $database;
    /** @var MockObject&ilLogger */
    private $logger;
    /** @var MockObject&ilCtrlInterface */
    private $controller;

    protected function setUp() : void
    {
        $this->database = $this->createMock(ilDBInterface::class);
        $this->controller = $this->createMock(ilCtrlInterface::class);
        $this->logger = $this->getMockBuilder(ilLogger::class)
                         ->disableOriginalConstructor()
                         ->getMock();
    }

    public function testGetUserData() : void
    {
        $filter = new \Certificate\API\Filter\UserDataFilter();

        $this->database
            ->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls(
                [
                    'id' => 5,
                    'title' => 'test',
                    'obj_id' => 100,
                    'ref_id' => 5000,
                    'acquired_timestamp' => 1234567890,
                    'user_id' => 2000,
                    'firstname' => 'ilyas',
                    'lastname' => 'homer',
                    'login' => 'breakdanceMcFunkyPants',
                    'email' => 'ilyas@ilias.de',
                    'second_email' => 'breakdance@funky.de'
                ],
                [
                    'id' => 5,
                    'title' => 'test',
                    'obj_id' => 100,
                    'ref_id' => 6000,
                    'acquired_timestamp' => 1234567890,
                    'user_id' => 2000,
                    'firstname' => 'ilyas',
                    'lastname' => 'homer',
                    'login' => 'breakdanceMcFunkyPants',
                    'email' => 'ilyas@ilias.de',
                    'second_email' => 'breakdance@funky.de'
                ]
            );

        $this->controller->method('getLinkTargetByClass')->willReturn('somewhere.php?goto=4');

        $repository = new \Certificate\API\Repository\UserDataRepository(
            $this->database,
            $this->logger,
            $this->controller,
            'no title given'
        );

        /** @var array<int, \Certificate\API\Data\UserCertificateDto> $userData */
        $userData = $repository->getUserData($filter, ['something']);

        /** @var \Certificate\API\Data\UserCertificateDto $object */
        $object = $userData[5];
        $this->assertSame('test', $object->getObjectTitle());
        $this->assertSame(5, $object->getCertificateId());
        $this->assertSame(100, $object->getObjectId());
        $this->assertSame([5000, 6000], $object->getObjectRefIds());
        $this->assertSame(1234567890, $object->getIssuedOnTimestamp());
        $this->assertSame(2000, $object->getUserId());
        $this->assertSame('ilyas', $object->getUserFirstName());
        $this->assertSame('homer', $object->getUserLastName());
        $this->assertSame('breakdanceMcFunkyPants', $object->getUserLogin());
        $this->assertSame('ilyas@ilias.de', $object->getUserEmail());
        $this->assertSame('breakdance@funky.de', $object->getUserSecondEmail());
        $this->assertSame('somewhere.php?goto=4', $object->getDownloadLink());
    }
}
