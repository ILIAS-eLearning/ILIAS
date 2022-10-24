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
 * Signature Plugin Class
 * @author       Maximilian Becker <mbecker@databay.de>
 *
 * @version      $Id$
 *
 * @ingroup      ModulesTest
 */
class ilTestSignatureGUI
{
    /** @var $lng \ilLanguage */
    protected $lng;

    /** @var $ilCtrl ilCtrl */
    protected $ilCtrl;

    /** @var $tpl \ilTemplate  */
    protected $tpl;

    /** @var $testGUI \ilObjTestGUI */
    protected $testGUI;

    /** @var $ilTestOutputGUI \ilTestOutputGUI */
    protected $ilTestOutputGUI;

    /** @var $test \ilObjTest */
    protected $test;

    /** @var \ilTestSignaturePlugin */
    protected $plugin;

    public function __construct(ilTestOutputGUI $testOutputGUI)
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $component_factory = $DIC["component.factory"];

        $this->lng = $lng;
        $this->ilCtrl = $ilCtrl;
        $this->tpl = $tpl;

        $this->ilTestOutputGUI = $testOutputGUI;
        $this->test = $this->ilTestOutputGUI->object;

        $plugins = $component_factory->getActivePluginsInSlot("tsig");
        $this->plugin = current($plugins);
        $this->plugin->setGUIObject($this);
    }

    public function executeCommand()
    {
        $next_class = $this->ilCtrl->getNextClass($this);

        switch ($next_class) {
            default:
                $ret = $this->dispatchCommand();
                break;
        }
        return $ret;
    }

    protected function dispatchCommand()
    {
        /** @var $ilUser ilObjUser */
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $active = $this->test->getActiveIdOfUser($ilUser->getId());
        $pass = $this->test->_getMaxPass($active);
        $key = 'signed_' . $active . '_' . $pass;
        ilSession::set($key, null);

        $cmd = $this->ilCtrl->getCmd();
        switch ($cmd) {
            default:
                $ret = $this->plugin->invoke($cmd);
        }
        return $ret;
    }

    /**
     * @param \ilObjTest $test
     */
    public function setTest($test)
    {
        $this->test = $test;
    }

    /**
     * @return \ilObjTest
     */
    public function getTest(): ilObjTest
    {
        return $this->test;
    }

    /**
     * @param \ilObjTestGUI $testGUI
     */
    public function setTestGUI($testGUI)
    {
        $this->testGUI = $testGUI;
    }

    /**
     * @return \ilObjTestGUI
     */
    public function getTestGUI(): ilObjTestGUI
    {
        return $this->testGUI;
    }

    /**
     * @param \ilTestOutputGUI $testOutputGUI
     */
    public function setTestOutputGUI($testOutputGUI)
    {
        $this->ilTestOutputGUI = $testOutputGUI;
    }

    /**
     * @return \ilTestOutputGUI
     */
    public function getTestOutputGUI(): ilTestOutputGUI
    {
        return $this->ilTestOutputGUI;
    }

    /**
     * This is to be called by the plugin at the end of the signature process to redirect the user back to the test.
     */
    public function redirectToTest($success)
    {
        /** @var $ilCtrl ilCtrl */
        /** @var $ilUser ilObjUser */
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilUser = $DIC['ilUser'];
        $active = $this->test->getActiveIdOfUser($ilUser->getId());
        $pass = $this->test->_getMaxPass($active);
        $key = 'signed_' . $active . '_' . $pass;
        ilSession::set($key, $success);
        $ilCtrl->redirect($this->ilTestOutputGUI, 'afterTestPassFinished');
        return;
    }
}
