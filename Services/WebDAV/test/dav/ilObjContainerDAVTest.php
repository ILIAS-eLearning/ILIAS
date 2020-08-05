<?php

use \PHPUnit\Framework\TestCase;
use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

require_once 'ilObjDummyDAV.php';

class ilObjContainerDAVTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var int */
    protected $ref_id;

    /** @var \Mockery\MockInterface */
    protected $mocked_obj;

    /** @var \Mockery\MockInterface */
    protected $mocked_repo_helper;

    /** @var \Mockery\MockInterface */
    protected $mocked_dav_helper;

    /** @var ilobjContainerDAV */
    protected $container_dav;

    /**
     * Setup
     */
    protected function setUp() : void
    {
        $this->ref_id = 100;
        $this->mocked_obj = \Mockery::mock('ilContainer');
        $this->mocked_obj->shouldReceive(['getRefId' => $this->ref_id]);

        $this->mocked_repo_helper = \Mockery::mock('ilWebDAVRepositoryHelper');

        $this->mocked_dav_helper = \Mockery::mock('ilWebDAVObjDAVHelper');

        $this->container_dav = $this->setUpContainerDAV($this->mocked_obj, $this->mocked_repo_helper, $this->mocked_dav_helper);

        parent::setUp();
    }

    /**
     * Setup instance for ilObjectDAV
     */
    protected function setUpContainerDAV($mocked_obj, $mocked_repo_helper, $mocked_dav_helper)
    {
        return new class($mocked_obj, $mocked_repo_helper, $mocked_dav_helper) extends ilObjContainerDAV {
            // Dummy implementation
            public function getChildCollectionType()
            {
                return null;
            }
        };
    }

    /**
     * @test
     * @small
     */
    public function GetChildren_OnlyDavableObjects_ReturnAllChildren()
    {
        // Arrange
        $number_of_children = 5;
        $number_of_davable_children = $number_of_children;

        $children_ref_ids = array();
        for ($i = 101; $i < $number_of_children + 101; $i++) {
            $children_ref_ids[] = $i;
            $children_titles[] = "abc" . $i;
        }

        $this->mocked_repo_helper->shouldReceive('getChildrenOfRefId')->andReturn($children_ref_ids);
        $this->mocked_dav_helper->shouldReceive('isDAVableObject')->andReturn(true);
        $this->mocked_repo_helper->shouldReceive('checkAccess')->andReturn(true);
        $this->mocked_repo_helper->shouldReceive('getObjectTitleFromRefId')->andReturnValues($children_titles);
        $this->mocked_dav_helper->shouldReceive('createDAVObjectForRefId')->andReturn(new ilObjDummyDAV());

        // Act
        $dav_children = $this->container_dav->getChildren();

        // Assert
        $this->assertTrue(count($dav_children) == $number_of_children);
    }

    /**
     * Important parameters:
     * isDAVableObject = true
     * checkAccess = false -> we test the behavior for no access
     *
     * @test
     * @small
     */
    public function GetChildren_NoDavableObjects_ReturnEmptyArray()
    {
        // Arrange
        $number_of_children = 5;
        $number_of_davable_children = 0;

        $children_ref_ids = array();
        $children_titles = array();
        for ($i = 101; $i < $number_of_children + 101; $i++) {
            $children_ref_ids[] = $i;
            $children_titles[] = "abc" . $i;
        }

        $this->mocked_repo_helper->shouldReceive('getChildrenOfRefId')->andReturn($children_ref_ids);
        $this->mocked_dav_helper->shouldReceive('isDAVableObject')->andReturn(false);
        $this->mocked_repo_helper->shouldReceive('getObjectTitleFromRefId')->andReturnValues($children_titles);
        $this->mocked_repo_helper->shouldReceive('getObjectTypeFromRefId')->andReturnValues($children_ref_ids);
        $this->mocked_dav_helper->shouldReceive('isDAVableObjType')->andReturn(false);

        // Act
        $dav_children = $this->container_dav->getChildren();

        // Assert
        $this->assertTrue(count($dav_children) == $number_of_davable_children);
    }

    /**
     * @test
     * @small
     */
    public function GetChildren_NoVisibleNoReadPermissionToObjects_ReturnEmptyArray()
    {
        // Arrange
        $number_of_children = 5;
        $number_of_davable_children = 0;

        $children_ref_ids = array();
        $children_titles = array();
        for ($i = 101; $i < $number_of_children + 101; $i++) {
            $children_ref_ids[] = $i;
            $children_titles[] = "abc" . $i;
        }

        $this->mocked_repo_helper->shouldReceive('getChildrenOfRefId')->andReturn($children_ref_ids);
        $this->mocked_dav_helper->shouldReceive('isDAVableObject')->andReturn(true);
        $this->mocked_repo_helper->shouldReceive('getObjectTitleFromRefId')->andReturnValues($children_titles);
        $this->mocked_repo_helper->shouldReceive('checkAccess')->andReturnUsing(function ($permission, $ref) {
            if ($permission == 'visible' || $permission == 'read') {
                return false; // No Visible and no read
            } else {
                throw new UnexpectedValueException($permission . ' was not expected as argument in this test case');
            }
        });

        // Act
        $dav_children = $this->container_dav->getChildren();

        // Assert
        $this->assertTrue(count($dav_children) == $number_of_davable_children);
    }

    /**
     * @test
     * @small
     */
    public function GetChildren_WithVisibleNoReadPermissionToObjects_ReturnEmptyArray()
    {
        // Arrange
        $number_of_children = 5;
        $number_of_davable_children = 0;

        $children_ref_ids = array();
        $children_titles = array();
        for ($i = 101; $i < $number_of_children + 101; $i++) {
            $children_ref_ids[] = $i;
            $children_titles[] = "abc" . $i;
        }

        $this->mocked_repo_helper->shouldReceive('getChildrenOfRefId')->andReturn($children_ref_ids);
        $this->mocked_dav_helper->shouldReceive('isDAVableObject')->andReturn(true);
        $this->mocked_repo_helper->shouldReceive('getObjectTitleFromRefId')->andReturnValues($children_titles);
        $this->mocked_repo_helper->shouldReceive('checkAccess')->andReturnUsing(function ($permission, $ref) {
            if ($permission == 'visible') {
                return true; // With visible
            } elseif ($permission == 'read') {
                return false; // No Read
            } else {
                throw new UnexpectedValueException($permission . ' was not expected as argument in this test case');
            }
        });

        // Act
        $dav_children = $this->container_dav->getChildren();

        // Assert
        $this->assertTrue(count($dav_children) == $number_of_davable_children);
    }

    /**
     * @test
     * @small
     */
    public function GetChildren_NoVisibleWithReadPermissionToObjects_ReturnEmptyArray()
    {
        // Arrange
        $number_of_children = 5;
        $number_of_davable_children = 0;

        $children_ref_ids = array();
        $children_titles = array();
        for ($i = 101; $i < $number_of_children + 101; $i++) {
            $children_ref_ids[] = $i;
            $children_titles[] = "abc" . $i;
        }

        $this->mocked_repo_helper->shouldReceive('getChildrenOfRefId')->andReturn($children_ref_ids);
        $this->mocked_dav_helper->shouldReceive('isDAVableObject')->andReturn(true);
        $this->mocked_repo_helper->shouldReceive('getObjectTitleFromRefId')->andReturnValues($children_titles);
        $this->mocked_repo_helper->shouldReceive('checkAccess')->andReturnUsing(function ($permission, $ref) {
            if ($permission == 'visible') {
                return false; // No Visible
            } elseif ($permission == 'read') {
                return true; // With Read
            } else {
                throw new UnexpectedValueException($permission . ' was not expected as argument in this test case');
            }
        });

        // Act
        $dav_children = $this->container_dav->getChildren();

        // Assert
        $this->assertTrue(count($dav_children) == $number_of_davable_children);
    }

    /**
     * @test
     * @small
     */
    public function GetChildren_MixedWithNonDavableObjects_ReturnOnlyDavableObjects()
    {
        // Arrange
        $number_of_children = 5;
        $number_of_davable_children = 3;

        $children_ref_ids = array();
        $children_titles = array();
        for ($i = 101; $i < $number_of_children + 101; $i++) {
            $children_ref_ids[] = $i;
            $children_titles[] = "abc" . $i;
        }

        $this->mocked_repo_helper->shouldReceive('getChildrenOfRefId')->andReturn($children_ref_ids);
        $this->mocked_dav_helper->shouldReceive('isDAVableObject')->andReturn(true, true, true, false, false);
        $this->mocked_repo_helper->shouldReceive('getObjectTitleFromRefId')->andReturnValues($children_titles);
        $this->mocked_repo_helper->shouldReceive('checkAccess')->andReturn(true);
        $this->mocked_repo_helper->shouldReceive('getObjectTypeFromRefId')->andReturn(false);
        $this->mocked_dav_helper->shouldReceive('isDAVableObjType')->andReturn(false);
        $this->mocked_dav_helper->shouldReceive('createDAVObjectForRefId')->andReturn(new ilObjDummyDAV());

        // Act
        $dav_children = $this->container_dav->getChildren();

        // Assert
        $this->mocked_dav_helper->shouldHaveReceived('isDAVableObject')->times(5);
        $this->mocked_repo_helper->shouldHaveReceived('checkAccess')->times(6);
        $this->assertTrue(count($dav_children) == $number_of_davable_children);
    }

    /**
     * @test
     * @small
     */
    public function ChildExists_ChildExistsButIsNotDAVable_returnFalse()
    {
        // Arrange
        $number_of_children = 5;

        $children_ref_ids = array();
        $children_titles = array();
        for ($i = 101; $i < $number_of_children + 101; $i++) {
            $children_ref_ids[] = $i;
            $children_titles[] = "abc" . $i;
        }

        $this->mocked_repo_helper->shouldReceive('getChildrenOfRefId')->andReturn($children_ref_ids);
        $this->mocked_dav_helper->shouldReceive('isDAVableObject')->andReturn(false);

        // Act
        $child_exists = $this->container_dav->childExists('dummy');

        // Assert
        $this->assertTrue(!$child_exists);
    }

    /**
     * @test
     * @small
     */
    public function ChildExists_ChildExistsButUserHasNoVisibleAndNoReadPermission_returnFalse()
    {
        // Arrange
        $number_of_children = 5;
        $searched_title = 'dummy';

        $children_ref_ids = array();
        for ($i = 101; $i < $number_of_children + 101; $i++) {
            $children_ref_ids[] = $i;
        }

        $this->mocked_repo_helper->shouldReceive('getChildrenOfRefId')->andReturn($children_ref_ids);
        $this->mocked_dav_helper->shouldReceive('isDAVableObject')->andReturn(true);
        $this->mocked_repo_helper->shouldReceive('getObjectTitleFromRefId')->andReturn('t1', 't2', $searched_title, 't4', 't5');
        $this->mocked_repo_helper->shouldReceive('checkAccess')->andReturnUsing(function ($permission, $ref) {
            if ($permission == 'visible' || $permission == 'read') {
                return false; // No Visible
            } else {
                throw new UnexpectedValueException($permission . ' was not expected as argument in this test case');
            }
        });

        // Act
        $child_exists = $this->container_dav->childExists($searched_title);

        // Assert
        $this->mocked_repo_helper->shouldHaveReceived('checkAccess')->once();
        $this->assertTrue(!$child_exists);
    }

    /**
     * @test
     * @small
     */
    public function ChildExists_ChildExistsButUserHasOnlyVisiblePermission_returnFalse()
    {
        // Arrange
        $number_of_children = 5;
        $searched_title = 'dummy';

        $children_ref_ids = array();
        for ($i = 101; $i < $number_of_children + 101; $i++) {
            $children_ref_ids[] = $i;
        }

        $this->mocked_repo_helper->shouldReceive('getChildrenOfRefId')->andReturn($children_ref_ids);
        $this->mocked_dav_helper->shouldReceive('isDAVableObject')->andReturn(true);
        $this->mocked_repo_helper->shouldReceive('getObjectTitleFromRefId')->andReturn('t1', 't2', $searched_title, 't4', 't5');
        $this->mocked_repo_helper->shouldReceive('checkAccess')->andReturnUsing(function ($permission, $ref) {
            if ($permission == 'visible') {
                return true; // With Visible
            } elseif ($permission == 'read') {
                return false; // No Read
            } else {
                throw new UnexpectedValueException($permission . ' was not expected as argument in this test case');
            }
        });

        // Act
        $child_exists = $this->container_dav->childExists($searched_title);

        // Assert
        $this->mocked_repo_helper->shouldHaveReceived('checkAccess')->twice();
        $this->assertTrue(!$child_exists);
    }

    /**
     * @test
     * @small
     */
    public function ChildExists_ChildExistsButUserHasOnlyReadPermission_returnFalse()
    {
        // Arrange
        $number_of_children = 5;
        $searched_title = 'dummy';

        $children_ref_ids = array();
        for ($i = 101; $i < $number_of_children + 101; $i++) {
            $children_ref_ids[] = $i;
        }

        $this->mocked_repo_helper->shouldReceive('getChildrenOfRefId')->andReturn($children_ref_ids);
        $this->mocked_dav_helper->shouldReceive('isDAVableObject')->andReturn(true);
        $this->mocked_repo_helper->shouldReceive('getObjectTitleFromRefId')->andReturn('t1', 't2', $searched_title, 't4', 't5');
        $this->mocked_repo_helper->shouldReceive('checkAccess')->andReturnUsing(function ($permission, $ref) {
            if ($permission == 'visible') {
                return false; // No Visible
            } elseif ($permission == 'read') {
                return true; // With Read
            } else {
                throw new UnexpectedValueException($permission . ' was not expected as argument in this test case');
            }
        });

        // Act
        $child_exists = $this->container_dav->childExists($searched_title);

        // Assert
        $this->mocked_repo_helper->shouldHaveReceived('checkAccess')->once();
        $this->assertTrue(!$child_exists);
    }

    /**
     * @test
     * @small
     */
    public function ChildExists_ChildExists_returnTrue()
    {
        // Arrange
        $number_of_children = 5;
        $searched_title = 'dummy';

        $children_ref_ids = array();
        for ($i = 101; $i < $number_of_children + 101; $i++) {
            $children_ref_ids[] = $i;
        }

        $this->mocked_repo_helper->shouldReceive('getChildrenOfRefId')->andReturn($children_ref_ids);
        $this->mocked_dav_helper->shouldReceive('isDAVableObject')->andReturn(true);
        $this->mocked_repo_helper->shouldReceive('getObjectTitleFromRefId')->andReturn('1', '2', $searched_title, '4', '5');
        $this->mocked_repo_helper->shouldReceive('checkAccess')->andReturn(true);

        // Act
        $child_exists = $this->container_dav->childExists($searched_title);

        // Assert
        $this->assertTrue($child_exists
            && $this->mocked_repo_helper->shouldHaveReceived('checkAccess')->twice());
    }

    /**
     * @test
     * @small
     */
    public function GetChild_ChildExistsAndIsDavable_ReturnChild()
    {
        // Arrange
        $number_of_children = 5;
        $searched_title = 'dummy';
        $dummy_obj_dav = new ilObjDummyDAV();

        $children_ref_ids = array();
        for ($i = 101; $i < $number_of_children + 101; $i++) {
            $children_ref_ids[] = $i;
        }

        $this->mocked_repo_helper->shouldReceive('getChildrenOfRefId')->andReturn($children_ref_ids);
        $this->mocked_dav_helper->shouldReceive('isDAVableObject')->andReturn(true);
        $this->mocked_repo_helper->shouldReceive('getObjectTitleFromRefId')->andReturn('1', '2', $searched_title, '4', '5');
        $this->mocked_repo_helper->shouldReceive('checkAccess')->andReturn(true);
        $this->mocked_dav_helper->shouldReceive('createDAVObjectForRefId')->andReturn($dummy_obj_dav);

        // Act
        $returned_child = $this->container_dav->getChild($searched_title);

        // Assert
        $this->mocked_repo_helper->shouldHaveReceived('checkAccess')->twice();
        $this->assertTrue($returned_child === $dummy_obj_dav);
    }

    /**
     * @test
     * @small
     */
    public function GetChild_ChildExistsButIsNotDavable_ThrowNotFound()
    {
        // Arrange
        $number_of_children = 5;
        $searched_title = 'dummy';
        $exception_thrown = false;

        $children_ref_ids = array();
        for ($i = 101; $i < $number_of_children + 101; $i++) {
            $children_ref_ids[] = $i;
        }

        $this->mocked_repo_helper->shouldReceive('getChildrenOfRefId')->andReturn($children_ref_ids);
        $this->mocked_dav_helper->shouldReceive('isDAVableObject')->andReturn(false);

        // Act
        try {
            $this->container_dav->getChild($searched_title);
        } catch (\Sabre\DAV\Exception\NotFound $e) {
            $exception_thrown = true;
        }

        // Assert
        $this->assertTrue($exception_thrown);
    }

    /**
     * @test
     * @small
     */
    public function GetChild_ChildDoesNotExist_ThrowNotFound()
    {
        // Arrange
        $number_of_children = 5;
        $searched_title = 'dummy';

        $children_ref_ids = array();
        for ($i = 101; $i < $number_of_children + 101; $i++) {
            $children_ref_ids[] = $i;
        }

        $this->mocked_repo_helper->shouldReceive('getChildrenOfRefId')->andReturn($children_ref_ids);
        $this->mocked_dav_helper->shouldReceive('isDAVableObject')->andReturn(true);
        $this->mocked_repo_helper->shouldReceive('getObjectTitleFromRefId')->andReturn('1', '2', '3', '4', '5');

        // Act
        try {
            $this->container_dav->getChild($searched_title);
        } catch (\Sabre\DAV\Exception\NotFound $e) {
            $exception_thrown = true;
        }

        // Assert
        $this->assertTrue($exception_thrown);
    }

    /**
     * @test
     * @small
     */
    public function CreateFile_NoCreateAccess_ThrowForbidden()
    {
        // Arrange
        $file_title = 'some_file.txt';

        $this->mocked_repo_helper->shouldReceive('checkCreateAccessForType')->andReturn(false);

        // Act
        try {
            $this->container_dav->createFile($file_title);
        } catch (\Sabre\DAV\Exception\Forbidden $e) {
            if ($e->getMessage() == 'No write access') {
                $exception_thrown = true;
            }
        }

        // Assert
        $this->assertTrue($exception_thrown);
    }

    /**
     * @test
     * @small
     */
    public function CreateFile_InvalidFileExtension_ThrowForbidden()
    {
        // Arrange
        $file_title = 'some_file.exe';
        $exception_thrown = false;

        $this->mocked_repo_helper->shouldReceive('checkCreateAccessForType')->andReturn(true);
        $this->mocked_dav_helper->shouldReceive('isValidFileNameWithValidFileExtension')->andReturn(false);

        // Act
        try {
            $this->container_dav->createFile($file_title);
        } catch (\Sabre\DAV\Exception\Forbidden $e) {
            if ($e->getMessage() == 'Invalid file name or file extension') {
                $exception_thrown = true;
            }
        }

        // Assert
        $this->assertTrue($exception_thrown);
    }
}
