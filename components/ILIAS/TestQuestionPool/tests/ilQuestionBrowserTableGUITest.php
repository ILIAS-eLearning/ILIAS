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

/**
* Unit tests
*
* @author Matheus Zych <mzych@databay.de>
*
* @ingroup components\ILIASTestQuestionPool
*
* This test was automatically generated.
*/
class ilQuestionBrowserTableGUITest extends assBaseTestCase
{
    protected $backupGlobals = false;

    private ilQuestionBrowserTableGUI $object;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_uiRenderer();
        $this->addGlobal_uiFactory();

        $object = new class {
            public object $object;

            public function __construct()
            {
                $this->object = new class {
                    public function getRefId(): int
                    {
                        return 0;
                    }

                    public function getShowTaxonomies(): bool
                    {
                        return false;
                    }
                };
            }
        };

        $this->object = new ilQuestionBrowserTableGUI($object, '', false, false, [], false);
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilQuestionBrowserTableGUI::class, $this->object);
    }
}