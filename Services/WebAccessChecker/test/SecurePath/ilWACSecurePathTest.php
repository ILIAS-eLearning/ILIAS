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


    protected function setUp() : void
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
