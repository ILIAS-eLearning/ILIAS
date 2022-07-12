<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use Sabre\DAV\Exception\BadRequest;
use Sabre\DAV\Exception\NotFound;

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

require_once "./Services/WebDAV/test/ilWebDAVTestHelper.php";
 
/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ilWebDAVLockUriPathResolverTest extends TestCase
{
    protected ilWebDAVTestHelper $webdav_test_helper;
    
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        $this->webdav_test_helper = new ilWebDAVTestHelper();
        parent::__construct($name, $data, $dataName);
    }
    
    public function setUp() : void
    {
        define('CLIENT_ID', $this->webdav_test_helper->getClientId());
        define('ROOT_FOLDER_ID', 1);
    }
   
    public function testGetRefIdForWebDAVPathWhenPathHasNoValidStartElementThrowsBadRequestError() : void
    {
        $invalid_path = [
            "",
            "invalid_client_id",
            "invalid_client_id/and_some_path"
        ];
        $path_resolver = $this->getPathResolverWithoutExpectationForFunctionsInHelper();

        foreach ($invalid_path as $current_path) {
            try {
                $path_resolver->getRefIdForWebDAVPath($current_path);
                $this->assertFalse('This should not happen!');
            } catch (BadRequest $e) {
                $this->assertEquals('Invalid client id given', $e->getMessage());
            }
        }
    }
    
    public function testGetRefIdForWebDAVWithValidClientIdReturnsRootFolderId() : void
    {
        $path_only_clientid = $this->webdav_test_helper->getClientId();
        $path_resolver = $this->getPathResolverWithoutExpectationForFunctionsInHelper();
        
        $this->assertEquals(1, $path_resolver->getRefIdForWebDAVPath($path_only_clientid));
    }
    
    public function testGetRefIdForValidRefMountReturnsRefId() : void
    {
        $path_with_valid_ref_id = $this->webdav_test_helper->getClientId() . '/ref_50';
        $path_resolver = $this->getPathResolverWithoutExpectationForFunctionsInHelper();
        
        $this->assertEquals(50, $path_resolver->getRefIdForWebDAVPath($path_with_valid_ref_id));
    }
    
    public function testGetRefIdForInvalidRefMountThrowsNotFoundError() : void
    {
        $invalid_refmount_path = [
            $this->webdav_test_helper->getClientId() . '/ref_0',
            $this->webdav_test_helper->getClientId() . '/ref_-23',
            $this->webdav_test_helper->getClientId() . '/ref_adfadf'
        ];
        $path_resolver = $this->getPathResolverWithoutExpectationForFunctionsInHelper();
        
        foreach ($invalid_refmount_path as $current_path) {
            try {
                $path_resolver->getRefIdForWebDAVPath($current_path);
                $this->assertFalse('This should not happen!');
            } catch (NotFound $e) {
                $this->assertEquals('Mount point not found', $e->getMessage());
            }
        }
    }

    public function testGetRefIdForWebDAVPathWithPathReturnsRefIdOfLastElement() : void
    {
        // Arrange
        $path = $this->webdav_test_helper->getClientId() . '/Last Child/Last Third Child';
        $expected_ref_id = 2335634322;
        $path_resolver = $this->getPathResolverWithExpectationForFunctionsInHelper(2, 8);
        
        $this->assertEquals($expected_ref_id, $path_resolver->getRefIdForWebDAVPath($path));
    }
    
    public function testGetRefIdForWebDAVPathWithPathPointingToElementWithIdenticalTitleReturnsRefIdOfLastIdenticalElement() : void
    {
        // Arrange
        $path = $this->webdav_test_helper->getClientId() . '/Second Child/Second First Child';
        $expected_ref_id = 72;
        $path_resolver = $this->getPathResolverWithExpectationForFunctionsInHelper(2, 11);
        
        $this->assertEquals($expected_ref_id, $path_resolver->getRefIdForWebDAVPath($path));
    }
    
    public function testGetRefIdForWebDAVPathWithInvalidPathThrowsNotFoundError() : void
    {
        // Arrange
        $pathes = [
            $this->webdav_test_helper->getClientId() . '/Non exitent First Child/Last Third Child' => [
                'parameters' => [1, 4],
                'error_message' => 'Node not found'
            ],
            $this->webdav_test_helper->getClientId() . '/Last Child/Non existent Last Child' => [
                'parameters' => [2, 8],
                'error_message' => 'Last node not found'
            ]
        ];
        
        foreach ($pathes as $path => $additional_info) {
            $path_resolver = $this->getPathResolverWithExpectationForFunctionsInHelper(
                ...$additional_info['parameters']
            );
            try {
                $path_resolver->getRefIdForWebDAVPath($path);
                $this->assertFalse('This should not happen!');
            } catch (NotFound $e) {
                $this->assertEquals($additional_info['error_message'], $e->getMessage());
            }
        }
    }
    
    protected function getPathResolverWithoutExpectationForFunctionsInHelper() : ilWebDAVLockUriPathResolver
    {
        $mocked_repo_helper = $this->createMock(ilWebDAVRepositoryHelper::class);
        return new ilWebDAVLockUriPathResolver($mocked_repo_helper);
    }
    
    protected function getPathResolverWithExpectationForFunctionsInHelper(int $expects_children, int $expects_ref_id) : ilWebDAVLockUriPathResolver
    {
        $webdav_test_helper = new ilWebDAVTestHelper();
        $tree = $webdav_test_helper->getTree();
        $mocked_repo_helper = $this->createMock(ilWebDAVRepositoryHelper::class);
        $mocked_repo_helper->expects($this->exactly($expects_children))
            ->method('getChildrenOfRefId')->willReturnCallback(
                function (int $parent_ref) use ($tree) : array {
                    return $tree[$parent_ref]['children'];
                }
            );
        $mocked_repo_helper->expects($this->exactly($expects_ref_id))
            ->method('getObjectTitleFromRefId')->willReturnCallback(
                function (int $ref_id) use ($tree) : string {
                    return $tree[$ref_id]['title'];
                }
            );
        return new ilWebDAVLockUriPathResolver($mocked_repo_helper);
    }
}
