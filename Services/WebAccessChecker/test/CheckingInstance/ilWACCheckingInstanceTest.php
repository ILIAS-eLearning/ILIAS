<?php
// declare(strict_types=1);

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\HTTP\Cookies\CookieFactory;
use ILIAS\HTTP\GlobalHttpState;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use org\bovigo\vfs;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * TestCase for the ilWACCheckingInstanceTest
 *
 * @author                 Fabian Schmid <fs@studer-raimann.ch>
 * @version                1.0.0
 *
 * @group                  needsInstalledILIAS
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class ilWACCheckingInstanceTest extends MockeryTestCase
{

    /**
     * @var vfs\vfsStreamFile
     */
    protected $file_one;
    /**
     * @var vfs\vfsStreamDirectory
     */
    protected $root;
    /**
     * @var GlobalHttpState|MockInterface
     */
    private $http;


    /**
     * Setup
     */
    protected function setUp()
    {
        //error_reporting(E_ALL);
        parent::setUp();
        require_once('./Services/WebAccessChecker/classes/class.ilWebAccessChecker.php');
        require_once('./Services/WebAccessChecker/classes/class.ilWACSignedPath.php');
        require_once('./Services/WebAccessChecker/classes/class.ilWACToken.php');
        require_once('./Services/WebAccessChecker/classes/class.ilWebAccessCheckerDelivery.php');
        $this->root = vfs\vfsStream::setup('ilias.de');
        $this->file_one = vfs\vfsStream::newFile('data/trunk/mobs/mm_123/dummy.jpg')
                                       ->at($this->root)->setContent('dummy');

        //setup container for HttpServiceAware classes
        $container = new \ILIAS\DI\Container();
        $container['http'] = function ($c) {
            return Mockery::mock(GlobalHttpState::class);
        };

        $this->http = $container['http'];


        $GLOBALS["DIC"] = $container;
        ilWACToken::setSALT('TOKEN');
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState    disabled
     * @backupGlobals          disabled
     * @backupStaticAttributes disabled
     */
    public function testDeliver()
    {
        self::markTestSkipped("WIP");
        return;
        $base64 = "iVBORw0KGgoAAAANSUhEUgAAAEgAAABICAYAAABV7bNHAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyhpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTExIDc5LjE1ODMyNSwgMjAxNS8wOS8xMC0wMToxMDoyMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUgKE1hY2ludG9zaCkiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6ODBEQTA1Rjk1NjYwMTFFNkE3RjBGNDkyNUNBOTg3NTkiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6ODBEQTA1RkE1NjYwMTFFNkE3RjBGNDkyNUNBOTg3NTkiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo4MERBMDVGNzU2NjAxMUU2QTdGMEY0OTI1Q0E5ODc1OSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo4MERBMDVGODU2NjAxMUU2QTdGMEY0OTI1Q0E5ODc1OSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PsPQ2nYAAAyXSURBVHja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";
        $image = vfs\vfsStream::newFile('data/trunk/mobs/mm_123/dummy.png')->at($this->root)
                              ->setContent($base64);
        $this->assertEquals($base64, $image->getContent());

        require_once('./Services/PHPUnit/classes/class.ilUnitUtil.php');
        try {
            $GLOBALS['DIC']['ilAuthSession']->setAuthenticated(true, ANONYMOUS_USER_ID);
            ilUnitUtil::performInitialisation();
        } catch (ErrorException $e) {
            //			echo $e->getMessage();
        }

        /**
         * @var ilObjUser $ilUser
         */
        $ilUser = $GLOBALS["DIC"]->user();
        $this->assertTrue($ilUser instanceof ilObjUser);

        ob_start(function ($buffer) {
            unset($buffer);

            return '';
        });

        /**
         * @var GlobalHttpState|\Mockery\MockInterface $httpService
         */
        $httpService = $this->http;
        /**
         * @var ServerRequestInterface|\Mockery\MockInterface $request
         */
        $request = Mockery::mock(ServerRequestInterface::class);
        /**
         * @var UriInterface|\Mockery\MockInterface $uri
         */
        $uri = Mockery::mock(UriInterface::class);

        $uri
            ->shouldReceive('getPath')
            ->once()
            ->withNoArgs()
            ->andReturn($image->url());

        $request
            ->shouldReceive('getUri')
            ->once()
            ->withNoArgs()
            ->andReturn($uri);

        $httpService
            ->shouldReceive('request')
            ->once()
            ->withNoArgs()
            ->andReturn($uri);

        ilWebAccessCheckerDelivery::run($httpService, Mockery::mock(CookieFactory::class));
        ob_end_clean();
        ob_end_flush();
        $this->assertEquals(404, $this->response()->getStatusCode());
    }


    public function testBasic()
    {
        self::markTestSkipped("Can't run test without db.");

        return;

        require_once('./Services/User/classes/class.ilObjUser.php');
        $ilWebAccessChecker = new ilWebAccessChecker($this->file_one->url());
        $check = false;
        try {
            $check = $ilWebAccessChecker->check();
        } catch (ilWACException $ilWACException) {
            $this->assertEquals($ilWACException->getCode(), ilWACException::ACCESS_DENIED_NO_PUB);
        }
        $this->assertFalse($check);
        $this->assertEquals(array(
            $ilWebAccessChecker::CM_CHECKINGINSTANCE,
        ), $ilWebAccessChecker->getAppliedCheckingMethods());
    }


    public function testBasicWithFileSigning()
    {
        self::markTestSkipped("WIP");
        return;
        $signed_path = ilWACSignedPath::signFile($this->file_one->url());

        $ilWebAccessChecker = new ilWebAccessChecker($this->http, Mockery::mock(CookieFactory::class));
        $check = false;
        try {
            $check = $ilWebAccessChecker->check();
        } catch (ilWACException $ilWACException) {
            $this->assertEquals($ilWACException->getCode(), ilWACException::ACCESS_DENIED_NO_PUB);
        }
        $this->assertTrue($check);
        $this->assertEquals(array(
            $ilWebAccessChecker::CM_FILE_TOKEN,
        ), $ilWebAccessChecker->getAppliedCheckingMethods());

        $headerName = 'X-ILIAS-WebAccessChecker';
        $response = $this->response();
        $this->assertTrue($response->hasHeader($headerName));
        $this->assertEquals([ 'checked using token' ], $response->getHeader($headerName));
    }


    public function testBasicWithFolderSigning()
    {
        self::markTestSkipped("WIP");
        return;
        ilWACSignedPath::signFolderOfStartFile($this->file_one->url());
        $ilWebAccessChecker = new ilWebAccessChecker($this->http, Mockery::mock(CookieFactory::class));
        $check = false;
        try {
            $check = $ilWebAccessChecker->check();
        } catch (ilWACException $ilWACException) {
            $this->assertEquals($ilWACException->getCode(), ilWACException::ACCESS_DENIED_NO_PUB);
        }
        $this->assertTrue($check);
        $this->assertEquals(array(
            $ilWebAccessChecker::CM_FOLDER_TOKEN,
        ), $ilWebAccessChecker->getAppliedCheckingMethods());

        $headerName = 'X-ILIAS-WebAccessChecker';
        $response = $this->response();
        $this->assertTrue($response->hasHeader($headerName));
        $this->assertEquals([ 'checked using secure folder' ], $response->getHeader($headerName));
    }


    /**
     * @Test
     */
    public function testNonCheckingInstanceNoSec()
    {
        self::markTestSkipped("Can't run test without db.");

        return;

        $file = vfs\vfsStream::newFile('data/trunk/dummy/mm_123/dummy.jpg')->at($this->root)
                             ->setContent('dummy');
        $ilWebAccessChecker = new ilWebAccessChecker($file->url());
        $check = false;
        try {
            if (!defined('IL_PHPUNIT_TEST')) {
                define('IL_PHPUNIT_TEST', true);
            }
            session_id('phpunittest');
            $_SESSION = array();
            include 'Services/PHPUnit/config/cfg.phpunit.php';

            $check = $ilWebAccessChecker->check();
        } catch (ilWACException $ilWACException) {
            $this->assertEquals($ilWACException->getCode(), ilWACException::ACCESS_DENIED_NO_PUB);
        }
        //		$this->assertTrue($check); // Currently not able to init ILIAS in WAC during PHPUnit
        //		$this->assertEquals(array(
        //			$ilWebAccessChecker::CM_SECFOLDER,
        //		), $ilWebAccessChecker->getAppliedCheckingMethods());
    }
}
