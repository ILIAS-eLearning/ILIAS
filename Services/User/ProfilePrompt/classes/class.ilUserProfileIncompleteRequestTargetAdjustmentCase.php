<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/User/classes/class.ilUserRequestTargetAdjustmentCase.php';

/**
 * Class ilUserProfileIncompleteRequestTargetAdjustmentCase
 */
class ilUserProfileIncompleteRequestTargetAdjustmentCase extends ilUserRequestTargetAdjustmentCase
{
    /**
     * @var bool
     */
    protected $update_prompt = false;

    /**
     * @return boolean
     */
    public function shouldStoreRequestTarget()
    {
        return true;
    }

    /**
     * @return boolean
     */
    public function isInFulfillment()
    {
        if (!isset($_GET['baseClass']) || strtolower($_GET['baseClass']) != 'ilpersonaldesktopgui') {
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

    /**
     * @return boolean
     */
    public function shouldAdjustRequest()
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
                        $deadline->increment(IL_CAL_DAY, (int) $prompt_settings->getDays());
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
                        $deadline->increment(IL_CAL_DAY, (int) $prompt_settings->getDays());
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

    /**
     * @return void
     */
    public function adjust()
    {
        $user_log = ilLoggerFactory::getLogger("user");

        if ($this->update_prompt) {
            $user_log->debug("Update last prompt date for user :" . $this->user->getId());
            $user_prompt_service = new ilUserProfilePromptService();
            $user_prompt_service->data()->saveLastUserPrompt((int) $this->user->getId());
        }

        $_GET['baseClass'] = 'ilpersonaldesktopgui';
        // sm: directly redirect to personal desktop -> personal profile
        $this->ctrl->setTargetScript('ilias.php');
        $this->ctrl->setParameterByClass("ilpersonalprofilegui", "prompted", "1");
        ilUtil::redirect($this->ctrl->getLinkTargetByClass(array('ilpersonaldesktopgui', 'ilpersonalprofilegui'), 'showPersonalData', '', false, false));
    }
}
