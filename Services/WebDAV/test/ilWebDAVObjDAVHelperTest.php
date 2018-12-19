<?php
/**
 * Created by PhpStorm.
 * User: adm_her
 * Date: 15.10.18
 * Time: 15:32
 */

/**
 * Just some tests for ilWebDAVObjDAVHelper
 */
class ilWebDAVObjDAVHelperTest extends PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @small
     */
    public function isDAVableObjType_giveInvalidType_returnsFalse()
    {
        // Arrange
        $tested_type = 'invalid';
        $mock_repo_helper = \Mockery::mock('ilWebDAVRepositoryHelper');
        $dav_helper = new ilWebDAVObjDAVHelper($mock_repo_helper);

        // Act
        $is_davable = $dav_helper->isDAVableObjType($tested_type);

        // Assert
        $this->assertTrue($is_davable == false);
    }

    /**
     * @test
     * @small
     */
    public function isDAVableObjType_giveValidType_returnsTrue()
    {
        // Arrange
        $tested_type = 'file';
        $mock_repo_helper = \Mockery::mock('ilWebDAVRepositoryHelper');
        $dav_helper = new ilWebDAVObjDAVHelper($mock_repo_helper);

        // Act
        $is_davable = $dav_helper->isDAVableObjType($tested_type);

        // Assert
        $this->assertTrue($is_davable == true);
    }

    /**
     * @test
     * @small
     */
    public function isDAVableObjTitle_giveJustLetters_returnsTrue()
    {
        // Arrange
        $tested_title = 'hello';
        $mock_repo_helper = \Mockery::mock('ilWebDAVRepositoryHelper');
        $dav_helper = new ilWebDAVObjDAVHelper($mock_repo_helper);

        // Act
        $is_davable = $dav_helper->isDAVableObjTitle($tested_title);

        // Assert
        $this->assertTrue($is_davable == true);
    }
}
