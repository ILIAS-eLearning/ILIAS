<?php

declare(strict_types=1);

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

use PHPUnit\Framework\TestCase;
use Sabre\DAV\INode;
use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\Exception\Forbidden;

require_once "./Services/WebDAV/test/webdav_overrides.php";

class ilDAVContainerTest extends TestCase
{
    use ilWebDAVCheckValidTitleTrait;

    public function testGetNameGetsObjectTitle(): void
    {
        $object = $this->createMock(ilObjFolder::class);
        $object->expects($this->once())->method('getTitle')->willReturn('Some random Title');

        $user = $this->createStub(ilObjUser::class);
        $request = $this->createStub('Psr\Http\Message\RequestInterface');
        $dav_factory = $this->createStub(ilWebDAVObjFactory::class);
        $repository_helper = $this->createStub(ilWebDAVRepositoryHelper::class);

        $dav_container = new ilDAVContainer($object, $user, $request, $dav_factory, $repository_helper);

        $this->assertEquals('Some random Title', $dav_container->getName());
    }

    public function testGetChildWithExistingNameOfFolderOrFileReturnsIlObject(): void
    {
        $ref_ids = [
            '7' => [
                'name' => 'Second Fourth Child',
                'class' => ilDAVContainer::class,
                'expects_objects' => 7
            ],
            '23356343' => [
                'name' => 'Last Last Child',
                'class' => ilDAVFile::class,
                'expects_objects' => 4
            ]
        ];

        foreach ($ref_ids as $ref_id => $additional_information) {
            $dav_container = $this->getDAVContainerWithExpectationForFunctions(
                (int) $ref_id,
                $additional_information['expects_objects']
            );
            $object = $dav_container->getChild($additional_information['name']);
            $this->assertInstanceOf($additional_information['class'], $object);
            $this->assertEquals($additional_information['name'], $object->getName());
        }
    }

    public function testGetChildWithExistingNonDavableNameThrowsNotFoundError(): void
    {
        $ref_id = '22';
        $webdav_test_helper = new ilWebDAVTestHelper();
        $tree = $webdav_test_helper->getTree();

        foreach ($tree[$ref_id]['children'] as $child_ref) {
            $dav_container = $this->getDAVContainerWithExpectationForFunctions(
                (int) $ref_id,
                11
            );
            try {
                $dav_container->getChild($tree[$child_ref]['title']);
                $this->assertFalse('This should never happen');
            } catch (NotFound $e) {
                $this->assertEquals($tree[$child_ref]['title'] . ' not found', $e->getMessage());
                ;
            }
        }
    }

    public function testGetChildWithExistingNameOfFolderOrFileWithoutAccessThrowsNotFoundError(): void
    {
        $ref_ids = [
            '7' => [
                'name' => 'Second Third Child',
                'expects_objects' => 7
            ],
            '23356343' => [
                'name' => 'Last Third Child',
                'expects_objects' => 4
            ]
        ];

        foreach ($ref_ids as $ref_id => $additional_information) {
            try {
                $dav_container = $this->getDAVContainerWithExpectationForFunctions(
                    (int) $ref_id,
                    $additional_information['expects_objects']
                );
                $dav_container->getChild($additional_information['name']);
                $this->assertFalse('This should not happen');
            } catch (NotFound $e) {
                $this->assertEquals($additional_information['name'] . ' not found', $e->getMessage());
            }
        }
    }

    public function testGetChildWithNonExistentNameOfFolderOrFileThrowsNotFoundError(): void
    {
        $ref_id = 7;
        $name = 'None existent name';

        try {
            $dav_container = $this->getDAVContainerWithExpectationForFunctions(
                $ref_id,
                7
            );
            $dav_container->getChild($name);
            $this->assertFalse('This should not happen');
        } catch (NotFound $e) {
            $this->assertEquals("$name not found", $e->getMessage());
        }
    }

    public function testGetChildWithExistingNameOfOtherObjectTypeThrowsNotFoundError(): void
    {
        $ref_ids = [
            '7' => [
                'name' => 'Second Last Child',
                'expects_objects' => 7
            ],
            '23356343' => [
                'name' => 'Last Second Child',
                'expects_objects' => 4
            ]
        ];

        foreach ($ref_ids as $ref_id => $additional_information) {
            try {
                $dav_container = $this->getDAVContainerWithExpectationForFunctions(
                    (int) $ref_id,
                    $additional_information['expects_objects']
                );
                $dav_container->getChild($additional_information['name']);
                $this->assertFalse('This should not happen');
            } catch (NotFound $e) {
                $this->assertEquals($additional_information['name'] . ' not found', $e->getMessage());
            }
        }
    }

    public function testGetChilrendWithExistingNameOfFolderOrFileReturnsArrayOfObjects(): void
    {
        $ref_id = 23356343;
        $additional_information = [
            'names' => ['Last First Child', 'Last Last Child'],
            'classes' => [ilDAVContainer::class, ilDAVFile::class],
            'ref_id' => ['233563432', '2335634323356343'],
            'expects_objects' => 4,
            'expects_problem_info_file' => 0
        ];

        $this->getChildrenTest($ref_id, $additional_information);
    }

    public function testGetChilrendWithExistingNameOfFolderOrFileReturnsArrayWithProblemInfoFile(): void
    {
        $ref_id = 7;
        $additional_information = [
            'names' => ['Second First Child', 'Second Second Child', 'Second Fourth Child', 'Problem Info File'],
            'classes' => [ilDAVFile::class, ilDAVFile::class, ilDAVContainer::class, ilDAVProblemInfoFile::class],
            'ref_id' => ['72', '78', '7221', '7222'],
            'expects_objects' => 7,
            'expects_problem_info_file' => 1,
        ];

        $this->getChildrenTest($ref_id, $additional_information);
    }

    /**
     * @param mixed[] $additional_information
     */
    protected function getChildrenTest(int $ref_id, array $additional_information): void
    {
        $dav_container = $this->getDAVContainerWithExpectationForFunctions(
            $ref_id,
            $additional_information['expects_objects'],
            $additional_information['expects_problem_info_file']
        );
        $children = $dav_container->getChildren();
        $this->assertEquals(count($additional_information['names']), count($children));
        for ($i = 0; $i < count($children); $i++) {
            $this->assertInstanceOf($additional_information['classes'][$i], $children[$additional_information['ref_id'][$i]]);
            $this->assertEquals($additional_information['names'][$i], $children[$additional_information['ref_id'][$i]]->getName());
        }
    }

    public function testGetChildrenFromFolderWithOnlyNonDavableNamedContentReturnsEmptyArray(): void
    {
        $ref_id = 22;

        $dav_container = $this->getDAVContainerWithExpectationForFunctions(
            $ref_id,
            11,
            1
        );
        $children = $dav_container->getChildren();
        $this->assertEquals(1, count($children));
    }

    public function testChildExistsWithExistingNameOfFolderOrFileReturnsTrue(): void
    {
        $ref_ids = [
            '7' => [
                'name' => 'Second Fourth Child',
                'expects_objects' => 6
            ],
            '23356343' => [
                'name' => 'Last Last Child',
                'expects_objects' => 4
            ]
        ];

        foreach ($ref_ids as $ref_id => $additional_information) {
            $dav_container = $this->getDAVContainerWithExpectationForFunctions(
                (int) $ref_id,
                $additional_information['expects_objects']
            );
            $this->assertTrue($dav_container->childExists($additional_information['name']));
        }
    }

    public function testChildExistsWithExistingNameOfFolderOrFileWhenOtherObjectOfSameNameExistsReturnsTrue(): void
    {
        $ref_id = 7;
        $additional_information = [
            'name' => 'Second Second Child',
            'expects_objects' => 4
        ];

        $dav_container = $this->getDAVContainerWithExpectationForFunctions(
            $ref_id,
            $additional_information['expects_objects']
        );
        $this->assertTrue($dav_container->childExists($additional_information['name']));
    }

    public function testChildExistsWithExistingNonDavableNameReturnsFalse(): void
    {
        $ref_id = '22';
        $webdav_test_helper = new ilWebDAVTestHelper();
        $tree = $webdav_test_helper->getTree();

        foreach ($tree[$ref_id]['children'] as $child_ref) {
            $dav_container = $this->getDAVContainerWithExpectationForFunctions(
                (int) $ref_id,
                11
            );
            $this->assertFalse($dav_container->childExists($tree[$child_ref]['title']));
        }
    }

    public function testChildExistsWithExistingNameOfFolderOrFileWithoutAccessReturnsFalse(): void
    {
        $ref_ids = [
            '7' => [
                'name' => 'Second Third Child',
                'expects_objects' => 7
            ],
            '23356343' => [
                'name' => 'Last Third Child',
                'expects_objects' => 4
            ]
        ];

        foreach ($ref_ids as $ref_id => $additional_information) {
            $dav_container = $this->getDAVContainerWithExpectationForFunctions(
                (int) $ref_id,
                $additional_information['expects_objects']
            );
            $this->assertFalse($dav_container->childExists($additional_information['name']));
        }
    }

    public function testChildExistsWithNonExistentNameOfFolderOrFileReturnsFalse(): void
    {
        $ref_id = 7;
        $name = 'None existent name';

        $dav_container = $this->getDAVContainerWithExpectationForFunctions(
            $ref_id,
            7
        );

        $this->assertFalse($dav_container->childExists($name));
    }

    public function testChildExistsWithExistingNameOfOtherObjectTypeReturnsFalse(): void
    {
        $ref_ids = [
            '7' => [
                'name' => 'Second Last Child',
                'expects_objects' => 7
            ],
            '23356343' => [
                'name' => 'Last Second Child',
                'expects_objects' => 4
            ]
        ];

        foreach ($ref_ids as $ref_id => $additional_information) {
            $dav_container = $this->getDAVContainerWithExpectationForFunctions(
                (int) $ref_id,
                $additional_information['expects_objects']
            );

            $this->assertFalse($dav_container->childExists($additional_information['name']));
        }
    }

    public function testSetNameWithoutPermissionsThrowsForbiddenError(): void
    {
        $parent_ref = 233563432;

        $dav_container = $this->getDAVContainerWithExpectationForFunctions($parent_ref, 0, 0, 0);

        try {
            $dav_container->setName('My Valid Name');
            $this->assertFalse('This should not happen!');
        } catch (Forbidden $e) {
            $this->assertEquals('Permission denied', $e->getMessage());
        }
    }

    public function testSetNameWithNonDavableNameThrowsForbiddenError(): void
    {
        $ref_id = 7221;
        $webdav_test_helper = new ilWebDAVTestHelper();
        $tree = $webdav_test_helper->getTree();

        foreach ($tree['22']['children'] as $invalid_object) {
            $dav_container = $this->getDAVContainerWithExpectationForFunctions($ref_id, 0, 0, 0);

            try {
                $dav_container->setName($tree[$invalid_object]['title']);
                $this->assertFalse('This should not happen!');
            } catch (Forbidden $e) {
                $this->assertEquals('Forbidden characters in title', $e->getMessage());
            }
        }
    }

    public function testCreateFileWithoutPermissionsThrowsForbiddenError(): void
    {
        $parent_ref = 233563432;

        $dav_container = $this->getDAVContainerWithExpectationForFunctions($parent_ref, 0, 0, 0, false);

        try {
            $dav_container->createFile('My New File.txt');
            $this->assertFalse('This should not happen!');
        } catch (Forbidden $e) {
            $this->assertEquals('Permission denied', $e->getMessage());
        }
    }

    public function testCreateDirectoryWithoutPermissionsThrowsForbiddenError(): void
    {
        $parent_ref = 233563432;

        $dav_container = $this->getDAVContainerWithExpectationForFunctions($parent_ref, 0, 0, 0, true);

        try {
            $dav_container->createDirectory('My New Folder');
            $this->assertFalse('This should not happen!');
        } catch (Forbidden $e) {
            $this->assertEquals('Permission denied', $e->getMessage());
        }
    }

    public function testCreateDirectoryWithNonDavableNameThrowsForbiddenError(): void
    {
        $ref_id = 7221;
        $webdav_test_helper = new ilWebDAVTestHelper();
        $tree = $webdav_test_helper->getTree();

        foreach ($tree['22']['children'] as $invalid_object) {
            $dav_container = $this->getDAVContainerWithExpectationForFunctions($ref_id, 0, 0, 0, true);

            try {
                $dav_container->createDirectory($tree[$invalid_object]['title']);
                $this->assertFalse('This should not happen!');
            } catch (Forbidden $e) {
                $this->assertEquals('Forbidden characters in title', $e->getMessage());
            }
        }
    }

    public function testDeleteWithoutPermissionsThrowsForbiddenError(): void
    {
        $parent_ref = 233563432;

        $dav_container = $this->getDAVContainerWithExpectationForFunctions($parent_ref, 0, 0, 0);

        try {
            $dav_container->delete();
            $this->assertFalse('This should not happen!');
        } catch (Forbidden $e) {
            $this->assertEquals('Permission denied', $e->getMessage());
        }
    }

    protected function getDAVContainerWithExpectationForFunctions(
        int $object_ref_id,
        int $expects_object,
        int $expects_problem_info_file = 0,
        int $expects_child_ref = 1,
        bool $for_create = false
    ): ilDAVContainer {
        $object_folder = $this->createPartialMock(ilObjFolder::class, []);
        $object_folder->setRefId($object_ref_id);
        $user = $this->createStub(ilObjUser::class);
        $request = $this->createStub('Psr\Http\Message\RequestInterface');

        $webdav_test_helper = new ilWebDAVTestHelper();
        $tree = $webdav_test_helper->getTree();

        $mocked_dav_factory = $this->createPartialMock(ilWebDAVObjFactory::class, ['retrieveDAVObjectByRefID', 'getProblemInfoFile']);
        $mocked_dav_factory->expects($this->exactly($expects_object))
        ->method('retrieveDAVObjectByRefID')->willReturnCallback(
            function (int $ref_id) use ($tree): INode {
                if ($tree[$ref_id]['access'] === 'none') {
                    throw new Forbidden("No read permission for object with reference ID $ref_id");
                }

                if ($tree[$ref_id]['type'] === 'fold') {
                    $obj_class = ilDAVContainer::class;
                } elseif ($tree[$ref_id]['type'] === 'file') {
                    $obj_class = ilDAVFile::class;
                } else {
                    throw new ilWebDAVNotDavableException(ilWebDAVNotDavableException::OBJECT_TYPE_NOT_DAVABLE);
                }

                if ($this->hasTitleForbiddenChars($tree[$ref_id]['title'])) {
                    throw new ilWebDAVNotDavableException(ilWebDAVNotDavableException::OBJECT_TITLE_NOT_DAVABLE);
                }

                if ($this->isHiddenFile($tree[$ref_id]['title'])) {
                    throw new ilWebDAVNotDavableException(ilWebDAVNotDavableException::OBJECT_HIDDEN);
                }

                $object = $this->createMock($obj_class);
                $object->expects($this->atMost(3))->method('getName')->willReturn($tree[$ref_id]['title']);
                return $object;
            }
        );
        $mocked_dav_factory->expects($this->exactly($expects_problem_info_file))
        ->method('getProblemInfoFile')->willReturnCallback(
            function (int $ref_id): ilDAVProblemInfoFile {
                $problem_info_file = $this->createMock(ilDAVProblemInfoFile::class);
                $problem_info_file->expects($this->atMost(2))->method('getName')->willReturn('Problem Info File');
                return $problem_info_file;
            }
        );

        $mocked_repo_helper = $this->createPartialMock(ilWebDAVRepositoryHelper::class, ['getChildrenOfRefId', 'checkcreateAccessForType', 'checkAccess']);
        $mocked_repo_helper->expects($this->exactly($expects_child_ref))
        ->method('getChildrenOfRefId')->willReturnCallback(
            function (int $parent_ref) use ($tree): array {
                return $tree[$parent_ref]['children'];
            }
        );
        $mocked_repo_helper->expects($this->atMost(1))
        ->method('checkcreateAccessForType')->willReturnCallback(
            function ($parent_ref, $type) use ($tree) {
                if ($tree[$parent_ref]['access'] === 'write') {
                    return true;
                }

                return false;
            }
        );
        $mocked_repo_helper->expects($this->atMost(1))
        ->method('checkAccess')->willReturnCallback(
            function (string $permission, int $ref_id) use ($tree) {
                if (in_array($permission, ['write', 'delete']) && $tree[$ref_id]['access'] === 'write') {
                    return true;
                }

                return false;
            }
        );

        if ($for_create) {
            $object_child = $this->createPartialMock(ilObjFolder::class, []);
            $object_child->setType('fold');
            $dav_container = new ilDAVContainerWithOverridenGetChildCollection($object_folder, $user, $request, $mocked_dav_factory, $mocked_repo_helper);
            $dav_container->setChildcollection($object_child);
            return $dav_container;
        }

        return new ilDAVContainer($object_folder, $user, $request, $mocked_dav_factory, $mocked_repo_helper);
    }
}
