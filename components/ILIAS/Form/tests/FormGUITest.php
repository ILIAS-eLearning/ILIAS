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

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class FormGUITest extends TestCase
{
    //protected $backupGlobals = false;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
    }

    /**
     * Test get HTML return an array
     */
    public function testFormGUIProperties(): void
    {
        $form_gui = new ilFormGUI();

        $form_gui->setId("myid");
        $this->assertEquals(
            "myid",
            $form_gui->getId()
        );

        $form_gui->setCloseTag(true);
        $this->assertEquals(
            true,
            $form_gui->getCloseTag()
        );

        $form_gui->setName("myname");
        $this->assertEquals(
            "myname",
            $form_gui->getName()
        );
    }
}
