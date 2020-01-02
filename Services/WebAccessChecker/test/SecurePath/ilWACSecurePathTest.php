<?php
// declare(strict_types=1);

use Mockery\Adapter\Phpunit\MockeryTestCase;

require_once('./libs/composer/vendor/autoload.php');

/**
 * Class ilWACSecurePathTest extends PHPUnit
 *
 * @author                 Fabian Schmid <fs@studer-raimann.ch>
 *
 * @group                  needsInstalledILIAS
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class ilWACSecurePathTest extends MockeryTestCase
{

    /**
     * @var bool
     */
    protected $backupGlobals = false;


    protected function setUp()
    {
        parent::setUp();
        require_once('./Services/PHPUnit/classes/class.ilUnitUtil.php');
        ilUnitUtil::performInitialisation();
        require_once('./Services/WebAccessChecker/classes/class.ilWACPath.php');
        require_once('./Services/WebAccessChecker/classes/class.ilWACSecurePath.php');
        require_once('./Services/WebAccessChecker/classes/class.ilWACSignedPath.php');
    }


    public function testPath()
    {
        /**
         * @var $obj ilWACSecurePath
         */
        $ilWACPath = new ilWACPath('http://www.ilias.de/docu/data/docu/mobs/mm_43803/test.png');
        $obj = ilWACSecurePath::find($ilWACPath->getSecurePathId());
        $this->assertEquals('./Services/MediaObjects', $obj->getComponentDirectory());
    }
}
