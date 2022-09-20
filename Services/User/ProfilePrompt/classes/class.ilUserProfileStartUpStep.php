<?php

declare(strict_types=1);

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

use ILIAS\Init\StartupSequence\StartUpSequenceStep;

class ilUserProfileStartUpStep extends StartUpSequenceStep
{
    private ilObjUser $user;
    private ilCtrl $ctrl;
    protected \ILIAS\User\ProfileGUIRequest $profile_request;
    protected bool $update_prompt = false;

    public function __construct(ilObjUser $user, ilCtrl $ctrl)
    {
        global $DIC;

        $this->user = $user;
        $this->ctrl = $ctrl;
        $this->profile_request = new \ILIAS\User\ProfileGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );
    }

    public function shouldStoreRequestTarget(): bool
    {
        return true;
    }

    public function isInFulfillment(): bool
    {
        $baseClass = $this->profile_request->getBaseClass();
        if ($baseClass == "" || strtolower($baseClass) != 'ildashboardgui') {
            return false;
        }

        return (
            strtolower($this->ctrl->getCmdClass()) == 'ilpersonalprofilegui' &&
            in_array(strtolower($this->ctrl->getCmd()), array(
                'savepersonaldata',
                'showpersonaldata',
                'showprofile',
                'showpublicprofile',
                'savepublicprofile'))
        );
    }

    public function shouldInterceptRequest(): bool
    {
        $user_log = ilLoggerFactory::getLogger("user");

        $user_prompt_service = new ilUserProfilePromptService();
        $prompt_settings = $user_prompt_service->data()->getSettings();
        $user_prompt = $user_prompt_service->data()->getUserPrompt($this->user->getId());

        $user_log->debug("Check Profile");

        if (!$this->isInFulfillment()) {
            // profile incomplete
            if ($this->user->getProfileIncomplete()) {
                $user_log->debug("Is Incomplete");
                return true;
            }
            // if profile is not shared yet
            if (!in_array($this->user->getPref("public_profile"), array("y", "g"))) {
                $user_log->debug("Is not public");
                // x days after first login
                if ($prompt_settings->getMode() == ilProfilePromptSettings::MODE_ONCE_AFTER_LOGIN) {
                    $user_log->debug("Mode: X days after login");
                    // if user has logged in and not received a prompt yet
                    if ($user_prompt->getFirstLogin() != "" && $user_prompt->getLastPrompt() == "") {
                        $user_log->debug("User has logged in and not prompted yet");
                        // check if first login + days < now
                        $deadline = new ilDateTime($user_prompt->getFirstLogin(), IL_CAL_DATETIME);
                        $deadline->increment(IL_CAL_DAY, $prompt_settings->getDays());
                        $user_log->debug("Check Deadline: " . $deadline->get(IL_CAL_DATETIME) .
                            " < now: " . ilUtil::now());
                        if ($deadline->get(IL_CAL_DATETIME) < ilUtil::now()) {
                            $user_log->debug("Deadline is due");
                            $this->update_prompt = true;
                            return true;
                        }
                    }
                }

                // repeat every x days
                if ($prompt_settings->getMode() == ilProfilePromptSettings::MODE_REPEAT) {
                    $user_log->debug("Mode: Repeat all x days");
                    // check if max(first login,last prompted) + days < now
                    $deadline = max($user_prompt->getFirstLogin(), $user_prompt->getLastPrompt());
                    if ($deadline != "") {
                        $user_log->debug("User logged in already.");
                        $deadline = new ilDateTime($deadline, IL_CAL_DATETIME);
                        $deadline->increment(IL_CAL_DAY, $prompt_settings->getDays());
                        $user_log->debug("Check Deadline: " . $deadline->get(IL_CAL_DATETIME) .
                            " < now: " . ilUtil::now());
                        if ($deadline->get(IL_CAL_DATETIME) < ilUtil::now()) {
                            $user_log->debug("Deadline is due");
                            $this->update_prompt = true;
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    public function execute(): void
    {
        $user_log = ilLoggerFactory::getLogger("user");

        if ($this->update_prompt) {
            $user_log->debug("Update last prompt date for user :" . $this->user->getId());
            $user_prompt_service = new ilUserProfilePromptService();
            $user_prompt_service->data()->saveLastUserPrompt($this->user->getId());
        }

        $this->ctrl->setParameterByClass("ilpersonalprofilegui", "prompted", "1");
        $this->ctrl->redirectByClass(array('ildashboardgui', 'ilpersonalprofilegui'), 'showPersonalData');
    }
}
