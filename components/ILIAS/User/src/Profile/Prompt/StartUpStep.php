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

namespace ILIAS\User\Profile\Prompt;

use ILIAS\Init\StartupSequence\StartUpSequenceStep;
use ILIAS\User\Profile\GUIRequest;

use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Language\Language;

class StartUpStep extends StartUpSequenceStep
{
    private const LOGGING_DATE_TIME_FORMAT = 'Y-m-d H:i:s';
    private \ilLogger $logger;

    private readonly GUIRequest $profile_request;
    private readonly Repository $repository;
    private bool $update_prompt = false;

    public function __construct(
        private readonly \ilObjUser $user,
        private readonly \ilCtrl $ctrl,
        \ilDBInterface $db,
        Language $lng,
        HTTPServices $http,
        Refinery $refinery
    ) {
        $this->logger = \ilLoggerFactory::getLogger('user');
        $this->profile_request = new GUIRequest(
            $http,
            $refinery
        );
        $this->repository = new Repository(
            $db,
            $lng,
            new \ilSetting('user')
        );
    }

    public function shouldStoreRequestTarget(): bool
    {
        return true;
    }

    public function isInFulfillment(): bool
    {
        $baseClass = $this->profile_request->getBaseClass();
        if ($baseClass === '' || strtolower($baseClass) !== 'ildashboardgui') {
            return false;
        }

        return (
            strtolower($this->ctrl->getCmdClass()) === 'ilpersonalprofilegui'
            && in_array(
                strtolower($this->ctrl->getCmd()),
                [
                    'savepersonaldata',
                    'showpersonaldata',
                    'showprofile',
                    'showpublicprofile',
                    'savepublicprofile'
                ]
            )
        );
    }

    public function shouldInterceptRequest(): bool
    {
        $prompt_settings = $this->repository->getSettings();
        $user_prompt = $this->repository->getUserPrompt($this->user->getId());

        $this->logger->debug('Check Profile');

        if ($this->isInFulfillment()) {
            return false;
        }

        if ($this->user->getProfileIncomplete()) {
            $this->logger->debug('Is Incomplete');
            return true;
        }

        if (in_array($this->user->getPref('public_profile'), ['y', 'g'])) {
            return false;
        }

        $this->logger->debug('Is not public');
        if ($prompt_settings->getMode() === Settings::MODE_ONCE_AFTER_LOGIN) {
            $this->logger->debug('Mode: X days after login');
            if ($user_prompt->getFirstLogin() === null || $user_prompt->getLastPrompt() !== null) {
                return false;
            }
            $this->logger->debug('User has logged in and not prompted yet');
            $deadline = $user_prompt->getFirstLogin()
                ->add(new \DateInterval("P{$prompt_settings->getDays()}D"));
            if ($deadline->getTimestamp() < time()) {
                $this->logger->debug('Deadline is due');
                $this->update_prompt = true;
                return true;
            }
        }

        if ($prompt_settings->getMode() === Settings::MODE_REPEAT) {
            $this->logger->debug('Mode: Repeat all x days');

            $last_interaction_date = max($user_prompt->getFirstLogin(), $user_prompt->getLastPrompt());
            if ($last_interaction_date === null) {
                return false;
            }
            $this->logger->debug('User logged in already.');
            $deadline = $last_interaction_date->add(new \DateInterval("P{$prompt_settings->getDays()}D"));
            if ($deadline->getTimestamp() < time()) {
                $this->logger->debug('Deadline is due');
                $this->update_prompt = true;
                return true;
            }
        }
        return false;
    }

    public function execute(): void
    {
        if ($this->update_prompt) {
            $this->logger->debug('Update last prompt date for user :' . $this->user->getId());
            $this->repository->updateLastUserPrompt($this->user->getId());
        }

        $this->ctrl->setParameterByClass('ilpersonalprofilegui', 'prompted', '1');
        $this->ctrl->redirectByClass(['ildashboardgui', 'ilpersonalprofilegui'], 'showPersonalData');
    }
}
