<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCertificateMigrationUIElements
 * @author Ralph Dittrich <dittrich@qualitus.de>
 */
class ilCertificateMigrationUIElements
{
    /** @var \ilObjUser */
    protected $user;

    /** @var \ILIAS\DI\UIServices */
    protected $ui;

    /** @var \ilLanguage */
    protected $lng;

    /** @var ilCertificateMigration */
    private $migrationHelper;

    /**
     * ilCertificateMigrationUIElements constructor.
     * @param \ilObjUser $user
     * @param \ILIAS\DI\UIServices $ui
     * @param \ilLanguage $lng
     * @param ilCertificateMigration|null $certificateMigration
     */
    public function __construct(
        \ilObjUser $user = null,
        \ILIAS\DI\UIServices $ui = null,
        \ilLanguage $lng = null,
        \ilCertificateMigration $migrationHelper = null
    ) {
        global $DIC;

        if (null === $user) {
            $user = $DIC->user();
        }

        if (null === $ui) {
            $ui = $DIC->ui();
        }

        if (null === $lng) {
            $lng = $DIC->language();
        }

        $lng->loadLanguageModule('cert');
        $this->user = $user;
        $this->ui = $ui;
        $this->lng = $lng;

        if (null === $migrationHelper) {
            $migrationHelper = new \ilCertificateMigration($this->user->getId());
        }
        $this->migrationHelper = $migrationHelper;
    }

    /**
     * Get confirmation messagebox for manual migration start
     *
     * @param string $link
     * @return string
     */
    public function getMigrationMessageBox(string $link) : string
    {
        $ui_factory = $this->ui->factory();
        $ui_renderer = $this->ui->renderer();

        $message_buttons = [
            $ui_factory->button()->standard($this->lng->txt("certificate_migration_go"), $link),
        ];

        if ($this->migrationHelper->isTaskFailed()) {
            $messageBox = $ui_factory->messageBox()
                ->failure($this->lng->txt('certificate_migration_lastrun_failed'))
                ->withButtons($message_buttons);
        } else {
            $messageBox = $ui_factory->messageBox()
                ->confirmation($this->lng->txt('certificate_migration_confirm_start'))
                ->withButtons($message_buttons);
        }

        return $ui_renderer->render($messageBox);
    }
}
