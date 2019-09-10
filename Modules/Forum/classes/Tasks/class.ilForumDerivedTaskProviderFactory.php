<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilForumDerivedTaskProviderFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilForumDerivedTaskProviderFactory implements \ilDerivedTaskProviderFactory
{
    /** @var ilTaskService */
    protected $taskService;

    /** @var \ilAccess */
    protected $accessHandler;

    /** @var \ilSetting */
    protected $settings;

    /** @var \ilLanguage */
    protected $lng;

    /** @var \ilCtrl */
    protected $ctrl;

    /**
     * ilForumDerivedTaskProviderFactory constructor.
     * @param \ilTaskService $taskService
     * @param \ilAccess|null $accessHandler
     * @param \ilSetting|null $settings
     * @param \ilLanguage|null $lng
     * @param ilCtrl|null $ctrl
     */
    public function __construct(
        \ilTaskService $taskService,
        \ilAccess $accessHandler = null,
        \ilSetting $settings = null,
        \ilLanguage $lng = null,
        \ilCtrl $ctrl = null
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

    /**
     * @inheritdoc
     */
    public function getProviders(): array
    {
        return [
            new \ilForumDraftsDerivedTaskProvider(
                $this->taskService,
                $this->accessHandler,
                $this->lng,
                $this->settings,
                $this->ctrl
            )
        ];
    }
}