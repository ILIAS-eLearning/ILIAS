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

/**
 * Abstract parent class for all signature plugin classes.
 *
 * @author  Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 *
 * @ingroup ModulesTest
 */
abstract class ilTestSignaturePlugin extends ilPlugin
{
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;

    protected ilTestSignatureGUI $GUIObject;

    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC['ilCtrl'];
        $this->tpl = $DIC['ilUser'];
    }

    public function setGUIObject(ilTestSignatureGUI $GUIObject): void
    {
        $this->GUIObject = $GUIObject;
    }

    public function getGUIObject(): ilTestSignatureGUI
    {
        return $this->GUIObject;
    }

    protected function getLinkTargetForCmd(string $cmd): string
    {
        return
            '//' . $_SERVER['HTTP_HOST']
            . substr($_SERVER['PHP_SELF'], 0, strlen($_SERVER['PHP_SELF']) - 10)
            . '/' . $this->ctrl->getLinkTarget($this->getGUIObject(), $cmd, '', false, false);
    }

    protected function getLinkTargetForRessource(string $cmd, string $ressource): string
    {
        $link = 'http://' . $_SERVER['HTTP_HOST']
            . substr($_SERVER['PHP_SELF'], 0, strlen($_SERVER['PHP_SELF']) - 10)
            . '/'
            . $this->ctrl->getLinkTarget($this->getGUIObject(), $cmd, '', false, false) . '&ressource=' . $ressource;
        return $link;
    }

    protected function getFormAction(string $default_cmd): string
    {
        return $this->ctrl->getFormAction($this, $default_cmd);
    }

    protected function populatePluginCanvas(string $content): void
    {
        $this->tpl->setVariable($this->getGUIObject()->getTestOutputGUI()->getContentBlockName(), $content);
        return;
    }

    /**
     * Hands in a file from the signature process associated with a given user and pass for archiving. (See docs, pls.)
     *
     * Please note: This method checks if archiving is enabled. The test needs to be set to archive data in order
     * to do something meaningful with the signed files. Still, the plugin will work properly if the signed materials
     * are not used afterwards. Since the processing in an archive is in fact not the only option to deal with the
     * files, this possibility of a corrupt settings constellation will not be closed. If your plugin wants to post the
     * files away to a non-ILIAS-DMS, or the like, you still want to sign files, even if archiving in ILIAS is switched
     * off.
     *
     * @param $active_fi	integer		Active-Id of the user.
     * @param $pass			integer		Pass-number of the tests submission.
     * @param $filename		string		Filename that is going to be saved.
     * @param $filepath		string		Path with the current location of the file.
     *
     * @return void
     */
    protected function handInFileForArchiving(int $active_fi, int $pass, string $filename, string $filepath): void
    {
        $archiver = new ilTestArchiver($this->getGUIObject()->getTest()->getId());
        $archiver->handInParticipantMisc($active_fi, $pass, $filename, $filepath);
    }

    protected function redirectToTest($success): void
    {
        $this->getGUIObject()->redirectToTest($success);
    }

    /**
     * Method all commands are forwarded to.
     *
     * This splits the control flow between the ilTestSignatureGUI, which is the command-class at the end of the
     * command-forwarding process, and the actual command-execution-class, which is the plugin instance. The plugin will
     * be called with an eventual command as parameter on this invoke-method and ... makes sense out of it. Whatever
     * that will be.
     *
     * What you see here is called "The Arab Pattern". You will agree, that "Command-Class-Execution-Separation" would
     * have be to bulky as a name.
     *
     * @param ?string $cmd Optional command for the plugin
     *
     * @return void
     */
    abstract public function invoke(?string $cmd = null): void;
}
