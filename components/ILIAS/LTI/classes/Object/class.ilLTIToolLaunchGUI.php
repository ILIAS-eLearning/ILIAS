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
 * Content screen of an LTI object: it launches the tool the object points to. The launch itself belongs
 * to the LTI version of the tool, this screen only decides which one runs it.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIToolLaunchGUI
{
    public const string CMD_LAUNCH = 'launch';
    public const string CMD_SHOW_EMBEDDED = 'showEmbedded';
    public const string CMD_START_ADVANTAGE_LAUNCH = 'startAdvantageLaunch';

    private readonly ILIAS\DI\Container $dic;

    public function __construct(private readonly ilObjLTITool $object)
    {
        global $DIC;

        $this->dic = $DIC;
    }

    /**
     * @throws ilCtrlException
     * @throws ilTemplateException
     */
    public function executeCommand(): void
    {
        match ($this->dic->ctrl()->getCmd(self::CMD_LAUNCH)) {
            self::CMD_SHOW_EMBEDDED => $this->showEmbedded(),
            self::CMD_START_ADVANTAGE_LAUNCH => $this->startAdvantageLaunch(),
            default => $this->launch(),
        };
    }

    /**
     * @throws ilCtrlException
     * @throws ilTemplateException
     */
    private function launch(): void
    {
        $this->showToolMessages();

        if ($this->object->getTool()->getLtiVersion() === ilLTITool::VERSION_ADVANTAGE) {
            ilLTIAdvantagePlatformLaunchRenderer::renderLaunch($this->object, $this, $this->dic);
            return;
        }

        ilLTI1p1ConsumerLaunchRenderer::renderLaunch($this->object, $this, $this->dic, $this->dic->language());
    }

    /**
     * A tool that sends the user back to the return URL of the launch may add a message and an error for the
     * user, as the LTI standard defines them.
     */
    private function showToolMessages(): void
    {
        $query = $this->dic->http()->wrapper()->query();
        $string = $this->dic->refinery()->kindlyTo()->string();
        foreach (['lti_msg' => 'info', 'lti_errormsg' => 'failure'] as $parameter => $type) {
            $message = $query->has($parameter) ? trim($query->retrieve($parameter, $string)) : '';
            if ($message !== '') {
                $this->dic->ui()->mainTemplate()->setOnScreenMessage($type, htmlspecialchars($message, ENT_QUOTES));
            }
        }
    }

    /**
     * @throws ilCtrlException
     */
    private function startAdvantageLaunch(): never
    {
        // a tool opened in the same window may send the user back here
        $return_url = !$this->object->isLaunchMethodOwnWin() ? '' : ilObjLTITool::getIliasHttpPath() . '/'
            . $this->dic->ctrl()->getLinkTarget($this, self::CMD_LAUNCH, '', false, false);

        ilLTIAdvantagePlatformLaunchRenderer::sendLaunchPage($this->object, $this->getCmixUser(), $return_url, $this->dic);
    }

    /**
     * @throws ilTemplateException
     */
    private function showEmbedded(): never
    {
        ilLTI1p1ConsumerLaunchRenderer::renderEmbeddedLaunch($this->object, $this->getCmixUser(), $this->dic);
    }

    /**
     * The identity the tool sees, generated on the first launch according to the privacy settings of the tool.
     */
    private function getCmixUser(): ilCmiXapiUser
    {
        $privacy_ident = $this->object->getTool()->getPrivacyIdent();
        $cmix_user = new ilCmiXapiUser($this->object->getId(), $this->dic->user()->getId(), $privacy_ident);

        if ($cmix_user->getUsrIdent() === '') {
            $cmix_user->setUsrIdent(ilCmiXapiUser::getIdent($privacy_ident, $this->dic->user()));
            $cmix_user->save();
        }

        return $cmix_user;
    }
}
