<?php
// declare(strict_types=1);

use Mockery\Adapter\Phpunit\MockeryTestCase;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ilWACSecurePathTest extends PHPUnit
 *
 * @author                 Fabian Schmid <fs@studer-raimann.ch>
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class ilWACSecurePathTest //extends MockeryTestCase
{

    /**
     * @var bool
     */
    protected $backupGlobals = false;


    protected function setUp() : void
    {
    }


    public function testPath() : void
    {
        /**
         * @var $obj ilWACSecurePath
         */
        $ilWACPath = new ilWACPath('http://www.ilias.de/docu/data/docu/mobs/mm_43803/test.png');
        $obj = ilWACSecurePath::find($ilWACPath->getSecurePathId());
        $this->assertEquals('./Services/MediaObjects', $obj->getComponentDirectory());
    }
}
