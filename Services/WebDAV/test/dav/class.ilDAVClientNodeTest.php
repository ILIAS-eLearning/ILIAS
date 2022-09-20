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
use Sabre\DAV\Exception\Forbidden;

require_once "./Services/WebDAV/test/ilWebDAVTestHelper.php";

class ilDAVClientNodeTest extends TestCase
{
    public function testGetNameGetsObjectTitle(): void
    {
        $webdav_test_helper = new ilWebDAVTestHelper();
        $dav_client = $this->getDAVClientNodeWithExpectationForFunctions();

        $this->assertEquals($webdav_test_helper->getClientId(), $dav_client->getName());
    }

    /*public function testGetChildWithWellformedSlugContainingRefIdReturnsCorrespondingObject() : void
    {
        $slug = 'ref_7';
    }

        public function testGetChildWithExistingNameOfFolderOrFileReturnsIlObject()
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

        public function testGetChildWithExistingNonDavableNameThrowsNotFoundError()
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

        public function testGetChildWithExistingNameOfFolderOrFileWithoutAccessThrowsNotFoundError() : void
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

        public function testGetChildWithNonExistentNameOfFolderOrFileThrowsNotFoundError() : void
        {
            $ref_id = 7;
            $name = 'None existent name';

            try {
                $dav_container = $this->getDAVContainerWithExpectationForFunctions(
                    (int) $ref_id,
                    7
                );

                $dav_container->getChild($name);
                $this->assertFalse('This should not happen');
            } catch (NotFound $e) {
                $this->assertEquals("$name not found", $e->getMessage());
            }
        }

        public function testGetChildWithExistingNameOfOtherObjectTypeThrowsNotFoundError() : void
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

        public function testGetChilrendWithExistingNameOfFolderOrFileReturnsArrayOfObjects() : void
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

        public function testGetChilrendWithExistingNameOfFolderOrFileReturnsArrayWithProblemInfoFile() : void
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

        protected function getChildrenTest(int $ref_id, array $additional_information) : void
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

        public function testGetChildrenFromFolderWithOnlyNonDavableNamedContentReturnsEmptyArray() : void
        {
            $ref_id = 22;

            $dav_container = $this->getDAVContainerWithExpectationForFunctions(
                $ref_id,
                11
            );
            $children = $dav_container->getChildren();
            $this->assertEquals(0, count($children));
        }

        public function testChildExistsWithExistingNameOfFolderOrFileReturnsTrue() : void
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

        public function testChildExistsWithExistingNameOfFolderOrFileWhenOtherObjectOfSameNameExistsReturnsTrue() : void
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

        public function testChildExistsWithExistingNonDavableNameReturnsFalse() : void
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

        public function testChildExistsWithExistingNameOfFolderOrFileWithoutAccessReturnsFalse() : void
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

        public function testChildExistsWithNonExistentNameOfFolderOrFileReturnsFalse() : void
        {
            $ref_id = 7;
            $name = 'None existent name';

            $dav_container = $this->getDAVContainerWithExpectationForFunctions(
                (int) $ref_id,
                7
            );

            $this->assertFalse($dav_container->childExists($name));
        }

        public function testChildExistsWithExistingNameOfOtherObjectTypeReturnsFalse() : void
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
        } */

    public function testSetNameThrowsForbiddenError(): void
    {
        $dav_client = $this->getDAVClientNodeWithExpectationForFunctions();

        try {
            $dav_client->setName('My Valid Name');
            $this->assertFalse('This should not happen!');
        } catch (Forbidden $e) {
            $this->assertEquals('It is not possible to change the name of the root', $e->getMessage());
        }
    }

    public function testCreateFileThrowsForbiddenError(): void
    {
        $dav_client = $this->getDAVClientNodeWithExpectationForFunctions();

        try {
            $dav_client->createFile('My New File.txt');
            $this->assertFalse('This should not happen!');
        } catch (Forbidden $e) {
            $this->assertEquals('It is not possible to create a file here', $e->getMessage());
        }
    }

    public function testCreateDirectoryThrowsForbiddenError(): void
    {
        $dav_client = $this->getDAVClientNodeWithExpectationForFunctions();

        try {
            $dav_client->createDirectory('My New Folder');
            $this->assertFalse('This should not happen!');
        } catch (Forbidden $e) {
            $this->assertEquals('It is not possible to create a directory here', $e->getMessage());
        }
    }

    public function testDeleteThrowsForbiddenError(): void
    {
        $dav_client = $this->getDAVClientNodeWithExpectationForFunctions();

        try {
            $dav_client->delete();
            $this->assertFalse('This should not happen!');
        } catch (Forbidden $e) {
            $this->assertEquals('It is not possible to delete the root', $e->getMessage());
        }
    }

    protected function getDAVClientNodeWithExpectationForFunctions(
    ): ilDAVClientNode {
        $webdav_test_helper = new ilWebDAVTestHelper();
        return new ilDAVClientNode($webdav_test_helper->getClientId(), $this->createStub(ilWebDAVObjFactory::class), $this->createStub(ilWebDAVRepositoryHelper::class));
    }
}
