<?php declare(strict_types=1);

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
 * Class ilForumDerivedTaskProviderFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilForumDerivedTaskProviderFactory implements ilDerivedTaskProviderFactory
{
    protected ilTaskService $taskService;
    protected ilAccessHandler $accessHandler;
    protected ilSetting $settings;
    protected ilLanguage $lng;
    protected ilCtrlInterface $ctrl;

    public function __construct(
        ilTaskService $taskService,
        ilAccessHandler $accessHandler = null,
        ilSetting $settings = null,
        ilLanguage $lng = null,
        ilCtrlInterface $ctrl = null
    ) {
        global $DIC;

        $this->taskService = $taskService;
        $this->accessHandler = is_null($accessHandler)
            ? $DIC->access()
            : $accessHandler;

        $this->settings = is_null($settings)
            ? $DIC->settings()
            : $settings;

        $this->lng = is_null($lng)
            ? $DIC->language()
            : $lng;

        $this->ctrl = is_null($ctrl)
            ? $DIC->ctrl()
            : $ctrl;
    }

    public function getProviders() : array
    {
        return [
            new ilForumDraftsDerivedTaskProvider(
                $this->taskService,
                $this->accessHandler,
                $this->lng,
                $this->settings,
                $this->ctrl
            )
        ];
    }
}
