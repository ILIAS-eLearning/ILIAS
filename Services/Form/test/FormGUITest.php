<?php

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
