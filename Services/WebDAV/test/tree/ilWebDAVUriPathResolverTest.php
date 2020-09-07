<?php

class ilWebDAVUriPathResolverTest extends PHPUnit_Framework_TestCase
{
    /** @var \Mockery\MockInterface */
    protected $mocked_repo_helper;

    /**
     * Setup
     */
    protected function setUp()
    {
        require_once('./Services/WebDAV/classes/tree/class.ilWebDAVUriPathResolver.php');
        require_once('./Services/WebDAV/classes/class.ilWebDAVRepositoryHelper.php');

        $this->mocked_repo_helper = \Mockery::mock('ilWebDAVRepositoryHelper');

        parent::setUp();
    }


    /**
     * @small
     * @test
     */
    public function getRefIdForWebDAVPath_pathHasOnlyOneElement_ThrowBadRequest()
    {
        $path_resolver = new ilWebDAVUriPathResolver($this->mocked_repo_helper);
        $too_short_path = "some_string_without_a_slash";
        $correct_exception_thrown = false;

        try {
            $path_resolver->getRefIdForWebDAVPath($too_short_path);
        } catch (\Sabre\DAV\Exception\BadRequest $e) {
            $correct_exception_thrown = $e->getMessage() == 'Path too short';
        }

        $this->assertTrue($correct_exception_thrown);
    }

    /**
     * @small
     * @test
     */
    public function getRefIdForWebDAVPath_pathHasNoValidMountPoint_ThrowBadRequest()
    {
        // Arrange
        $too_short_path = "some_string_with_a_slash/and_stuff";
        $correct_exception_thrown = false;

        $path_resolver = \Mockery::mock('ilWebDAVUriPathResolver')->makePartial();
        $path_resolver->shouldAllowMockingProtectedMethods();

        // Assert
        try {
            $ref_id = $path_resolver->getRefIdForWebDAVPath($too_short_path);
        } catch (\Sabre\DAV\Exception\BadRequest $e) {
            $correct_exception_thrown = $e->getMessage() == 'Invalid mountpoint given';
        }

        // Act
        $this->assertTrue($correct_exception_thrown);
    }

    /**
     * @small
     * @test
     */
    public function getRefIdForWebDAVPath_repoRootAsMountGiven_callAndReturnValueOfGetRefIdFromPathInRepositoryMount()
    {
        // Arrange
        $too_short_path = "some_string_with_a_slash/ILIAS";
        $expected_ref_id = 40;

        $path_resolver = \Mockery::mock('ilWebDAVUriPathResolver')->makePartial();
        $path_resolver->shouldAllowMockingProtectedMethods();
        $path_resolver->shouldReceive('getRefIdFromPathInRepositoryMount')->andReturn($expected_ref_id);

        // Assert
        $received_ref_id = $path_resolver->getRefIdForWebDAVPath($too_short_path);

        // Act
        $this->assertEquals($expected_ref_id, $received_ref_id);
    }


    /**
     * If a path like foo/bar/last/ is given, the return should be the ref_id of 'last'
     *
     * @small
     * @test
     */
    public function getRefIdFromGivenParentRefAndTitlePath_pathEndsWithSlash_returnElementBeforeSlash()
    {
        // Arrange
        $searched_ref_id = 40;
        $path_title_array = explode('/', "ref30/ref35/ref$searched_ref_id/");

        $path_resolver = \Mockery::mock('ilWebDAVUriPathResolver')->makePartial();
        $path_resolver->shouldAllowMockingProtectedMethods();
        $path_resolver->shouldReceive('getChildRefIdByGivenTitle')->andReturn(30, 35, $searched_ref_id);

        // Act
        $result_ref = $path_resolver->getRefIdFromGivenParentRefAndTitlePath(25, $path_title_array);

        // Assert
        $this->assertEquals($searched_ref_id, $result_ref);
    }

    /**
     * If a path like foo/bar/last is given, the return should be the ref_id of 'last'
     *
     * @small
     * @test
     */
    public function getRefIdFromGivenParentRefAndTitlePath_pathEndsWithoutSlash_returnLastElement()
    {
        // Arrange
        $searched_ref_id = 40;
        $path_title_array = explode('/', "ref30/ref35/ref$searched_ref_id");
        $path_resolver = \Mockery::mock('ilWebDAVUriPathResolver')->makePartial();
        $path_resolver->shouldAllowMockingProtectedMethods();
        $path_resolver->shouldReceive('getChildRefIdByGivenTitle')->andReturn(30, 35, $searched_ref_id);

        // Act
        $result_ref = $path_resolver->getRefIdFromGivenParentRefAndTitlePath(25, $path_title_array);

        // Assert
        $this->assertEquals($searched_ref_id, $result_ref);
    }

    /**
     * If a path like /foo//bar/last is given, the return should be the ref_id of 'last'
     *
     * @small
     * @test
     */
    public function getRefIdFromGivenParentRefAndTitlePath_pathStartsWithSlash_returnLastElement()
    {
        // Arrange
        $searched_ref_id = 40;
        $path_title_array = explode('/', "ref30///ref35/ref$searched_ref_id");
        $path_resolver = \Mockery::mock('ilWebDAVUriPathResolver')->makePartial();
        $path_resolver->shouldAllowMockingProtectedMethods();
        $path_resolver->shouldReceive('getChildRefIdByGivenTitle')->andReturn(30, 35, $searched_ref_id);

        // Act
        $result_ref = $path_resolver->getRefIdFromGivenParentRefAndTitlePath(25, $path_title_array);

        // Assert
        $this->assertEquals($searched_ref_id, $result_ref);
    }

    /**
     * If a path like foo//bar/last is given, the return should be the ref_id of 'last'
     *
     * @small
     * @test
     */
    public function getRefIdFromGivenParentRefAndTitlePath_pathWithDoubleSlashInBetween_returnLastElement()
    {
        // Arrange
        $searched_ref_id = 40;
        $path_title_array = explode('/', "ref30//ref35/ref$searched_ref_id");
        $path_resolver = \Mockery::mock('ilWebDAVUriPathResolver')->makePartial();
        $path_resolver->shouldAllowMockingProtectedMethods();
        $path_resolver->shouldReceive('getChildRefIdByGivenTitle')->andReturn(30, 35, $searched_ref_id);

        // Act
        $result_ref = $path_resolver->getRefIdFromGivenParentRefAndTitlePath(25, $path_title_array);

        // Assert
        $this->assertEquals($searched_ref_id, $result_ref);
    }

    /**
     * If a path like foo///bar/last is given, the return should be the ref_id of 'last'
     *
     * @small
     * @test
     */
    public function getRefIdFromGivenParentRefAndTitlePath_pathWithTrippleSlashInBetween_returnLastElement()
    {
        // Arrange
        $searched_ref_id = 40;
        $path_title_array = explode('/', "ref30///ref35/ref$searched_ref_id");
        $path_resolver = \Mockery::mock('ilWebDAVUriPathResolver')->makePartial();
        $path_resolver->shouldAllowMockingProtectedMethods();
        $path_resolver->shouldReceive('getChildRefIdByGivenTitle')->andReturn(30, 35, $searched_ref_id);

        // Act
        $result_ref = $path_resolver->getRefIdFromGivenParentRefAndTitlePath(25, $path_title_array);

        // Assert
        $this->assertEquals($searched_ref_id, $result_ref);
    }
}
