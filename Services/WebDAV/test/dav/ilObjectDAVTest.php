<?php
use\PHPUnit\Framework\TestCase;
use Sabre\DAV\Exception\Forbidden;

require_once('./libs/composer/vendor/autoload.php');

/**
 * TestCase for the ilObjectDAVTest
 *
 * I name the test-methods like this: MethodName_TestedBehavior_Expectation
 *
 * For example setName_NoWriteAccess_ThrowForbidden means:
 * - I test the method setName
 * - I will test the behavior if I dont have write access to this object
 * - I expect a Forbidden-Exception
 *
 * @author                 Raphael Heer <raphael.heer@hslu.ch>
 * @version                1.0.0
 *
 * @group                  needsInstalledILIAS
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class ilObjectDAVTest extends PHPUnit_Framework_TestCase
{
    /** @var int */
    protected $ref_id;

    /** @var \Mockery\MockInterface */
    protected $mocked_obj;

    /** @var ilWebDAVRepositoryHelper */
    protected $mocked_dav_repo_helper;

    /** @var ilWebDAVObjDAVHelper */
    protected $mocked_dav_obj_helper;

    /** @var ilObject */
    protected $dav_obj;


    /**
     * Setup
     */
    protected function setUp()
    {
        require_once('./Services/WebDAV/classes/dav/class.ilObjectDAV.php');
        require_once('./Services/WebDAV/classes/class.ilWebDAVRepositoryHelper.php');
        require_once('./Services/WebDAV/classes/class.ilWebDAVObjDAVHelper.php');

        $this->ref_id = 100;
        $this->mocked_obj = \Mockery::mock('ilObject');
        $this->mocked_obj->shouldReceive(['getRefId' => $this->ref_id]);

        $this->mocked_dav_obj_helper = \Mockery::mock('ilWebDAVObjDAVHelper');

        $this->mocked_dav_repo_helper = \Mockery::mock('ilWebDAVRepositoryHelper');

        $this->dav_obj = $this->setUpObjectDAV($this->mocked_obj, $this->mocked_dav_repo_helper, $this->mocked_dav_obj_helper);

        parent::setUp();
    }
    
    /**
     * Setup instance for ilObjectDAV
     */
    protected function setUpObjectDAV($mocked_obj, $mocked_repo_helper, $mockes_dav_helper)
    {
        return new class($mocked_obj, $mocked_repo_helper, $mockes_dav_helper) extends ilObjectDAV {
        };
    }

    /**
     * @test
     * @small
     */
    public function setName_NoWriteAccess_ThrowForbidden()
    {
        // Arrange
        $exception_thrown = false;
        $title = 'Test';

        $this->mocked_dav_repo_helper->shouldReceive('checkAccess')->withArgs(['write', $this->ref_id])->andReturn(false);

        $this->mocked_obj->shouldNotReceive('setTitle');


        // Act
        try {
            $this->dav_obj->setName($title);
        } catch (Forbidden $e) {
            $exception_thrown = $e->getMessage() == 'Permission denied';
        }

        // Assert
        $this->assertTrue($exception_thrown);
    }
    
    /**
     * @test
     * @small
     */
    public function setName_ForbiddenCharacters_ThrowForbidden()
    {
        // Arrange
        $exception_thrown = false;
        $title = 'Test';

        $this->mocked_dav_repo_helper->shouldReceive('checkAccess')->withAnyArgs()->andReturn(true);
        $this->mocked_dav_obj_helper->shouldReceive('isDAVableObjTitle')->with($title)->andReturn(false);

        $this->mocked_obj = \Mockery::mock('ilObject');
        $this->mocked_obj->shouldNotReceive('setTitle');

        // Act
        try {
            $this->dav_obj->setName($title);
        } catch (Forbidden $e) {
            $exception_thrown = $e->getMessage() == 'Forbidden characters in title';
        }

        // Assert
        $this->assertTrue($exception_thrown);
    }

    /**
     * Requirements:
     * - Write permission for this object
     * - No forbidden characters in title
     *
     * @test
     * @small
     */
    public function setName_EverythingFine_SetTitleForObject()
    {
        // Arrange
        $title = 'Test';

        $this->mocked_dav_repo_helper->shouldReceive('checkAccess')->withAnyArgs()->andReturn(true);
        $this->mocked_dav_obj_helper->shouldReceive('isDAVableObjTitle')->with($title)->andReturn(true);

        $this->mocked_obj->shouldReceive('setTitle')->withArgs([$title]);
        $this->mocked_obj->shouldReceive('update');

        // Act
        $this->dav_obj->setName($title);

        // Assert
        $this->assertTrue($this->mocked_obj->shouldHaveReceived('setTitle')->withArgs([$title])
            && $this->mocked_obj->shouldHaveReceived('update'));
    }

    /**
     * @test
     * @small
     */
    public function delete_WithoutPermission_ThrowForbidden()
    {
        // Arrange
        $exception_thrown = false;

        $this->mocked_dav_repo_helper->shouldReceive('checkAccess')->withAnyArgs()->andReturn(false);

        // Act
        try {
            $this->dav_obj->delete();
        } catch (Forbidden $e) {
            $exception_thrown = $e->getMessage() == "Permission denied";
        }

        // Assert
        $this->assertTrue($exception_thrown);
    }
}
