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
 
use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

/**
 * Class ilCourseMailTemplateTutorContextTest
 */
class ilCourseMailTemplateTutorContextTest extends TestCase
{
    private ?\ILIAS\DI\Container $dic_backup = null;

    protected function setUp() : void
    {
        global $DIC;
        $this->dic_backup = is_object($DIC) ? clone $DIC : $DIC;
        
        $DIC = new Container();
        $DIC['ilObjDataCache'] = $this->createMock(ilObjectDataCache::class);
        $DIC['ilDB'] = $this->createMock(ilDBInterface::class);
    }
    
    protected function tearDown() : void
    {
        global $DIC;
        $DIC = $this->dic_backup;
    }
    
    public function testNonExistingPlaceholderWontBeResolved() : void
    {
        $mailTemplateContext = new ilCourseMailTemplateTutorContext();

        $result = $mailTemplateContext->resolveSpecificPlaceholder('TEST_PLACEHOLDER', array());

        $this->assertEquals('', $result);
    }
}
