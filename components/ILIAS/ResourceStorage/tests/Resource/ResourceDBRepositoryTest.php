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

namespace ILIAS\ResourceStorage\Resource\Repository;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Resource\StorableFileResource;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ResourceDBRepositoryTest extends TestCase
{
    #[DataProvider('confidentialProvider')]
    public function testStoreWritesConfidentialFlag(bool $confidential): void
    {
        $resource = new StorableFileResource(new ResourceIdentification('rid'));
        $resource->setStorageID('fsv2');
        $resource->setConfidential($confidential);

        $db = $this->createMock(\ilDBInterface::class);
        $db->expects($this->once())
           ->method('replace')
           ->with(
               'il_resource',
               ['rid' => ['text', 'rid']],
               [
                   'storage_id' => ['text', 'fsv2'],
                   'rtype' => ['integer', 1],
                   'confidential' => ['integer', (int) $confidential],
               ]
           );

        (new ResourceDBRepository($db))->store($resource);
    }

    #[DataProvider('confidentialProvider')]
    public function testGetReadsConfidentialFlag(bool $confidential): void
    {
        $db = $this->createStub(\ilDBInterface::class);
        $db->method('queryF')->willReturn($this->createStub(\ilDBStatement::class));
        $db->method('fetchObject')->willReturn((object) [
            'storage_id' => 'fsv2',
            'rtype' => '1',
            'confidential' => $confidential ? '1' : '0',
        ]);

        $resource = (new ResourceDBRepository($db))->get(new ResourceIdentification('rid'));

        $this->assertSame($confidential, $resource->isConfidential());
    }

    #[DataProvider('confidentialProvider')]
    public function testPopulateFromArrayReadsConfidentialFlag(bool $confidential): void
    {
        $db = $this->createMock(\ilDBInterface::class);
        $db->expects($this->never())->method('queryF');

        $repository = new ResourceDBRepository($db);
        $repository->populateFromArray([
            'rid' => 'rid',
            'storage_id' => 'fsv2',
            'rtype' => '1',
            'confidential' => $confidential ? '1' : '0',
        ]);

        $this->assertSame($confidential, $repository->get(new ResourceIdentification('rid'))->isConfidential());
    }

    public function testPopulateFromArrayWithoutConfidentialColumnIsNotConfidential(): void
    {
        $repository = new ResourceDBRepository($this->createStub(\ilDBInterface::class));
        $repository->populateFromArray([
            'rid' => 'rid',
            'storage_id' => 'fsv2',
            'rtype' => '1',
        ]);

        $this->assertFalse($repository->get(new ResourceIdentification('rid'))->isConfidential());
    }

    public static function confidentialProvider(): \Iterator
    {
        yield 'confidential' => [true];
        yield 'not confidential' => [false];
    }
}
