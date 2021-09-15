<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    protected ilCtrl $ctrl;

    public function __construct(
        ilTaskService $taskService,
        ilAccessHandler $accessHandler = null,
        ilSetting $settings = null,
        ilLanguage $lng = null,
        ilCtrl $ctrl = null
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
