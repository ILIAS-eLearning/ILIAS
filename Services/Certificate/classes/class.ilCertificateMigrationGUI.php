<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCertificateMigrationGUI
 * @author Ralph Dittrich <dittrich@qualitus.de>
 *
 * @ilCtrl_IsCalledBy ilCertificateMigrationGUI: ilPersonalProfileGUI, ilUserCertificateGUI
 *
 */
class ilCertificateMigrationGUI
{
    /** @var ilLogger|null */
    private $logger;

    /** @var \ilCtrl|null */
    protected $ctrl;

    /** @var \ilLanguage|null */
    protected $lng;

    /** @var ilAccessHandler|null */
    protected $access;

    /** @var \ilTemplate|null */
    protected $tpl;

    /** @var \ilObjUser|null */
    protected $user;

    /** @var \ILIAS\DI\BackgroundTaskServices|null */
    protected $backgroundTasks;
    
    /** @var \ilLearningHistoryService|null */
    protected $learningHistoryService;

    /** @var ilCertificateMigrationValidator|null */
    private $migrationValidator;
    
    /** @var ilErrorHandling|null */
    private $errorHandler;

    /**
     * ilCertificateMigrationGUI constructor.
     * @param \ilCtrl                              $ctrl
     * @param \ilLanguage                          $lng
     * @param ilAccessHandler|null                 $access
     * @param \ILIAS\DI\BackgroundTaskServices     $backgroundTasks
     * @param \ilObjUser                           $user
     * @param \ilLearningHistoryService            $learningHistoryService
     * @param ilSetting|null                       $certificateSettings
     * @param ilCertificateMigrationValidator|null $migrationValidator
     * @param ilErrorHandling|null                 $errorHandler
     * @param ilLog|null                           $logger
     */
    public function __construct(
        \ilCtrl $ctrl = null,
        \ilLanguage $lng = null,
        \ilAccessHandler $access = null,
        \ILIAS\DI\BackgroundTaskServices $backgroundTasks = null,
        \ilObjUser $user = null,
        \ilLearningHistoryService $learningHistoryService = null,
        \ilSetting $certificateSettings = null,
        \ilCertificateMigrationValidator $migrationValidator = null,
        \ilErrorHandling $errorHandler = null,
        \ilLog $logger = null
    ) {
        global $DIC;

        if (null === $ctrl) {
            $ctrl = $DIC->ctrl();
        }
        if (null === $lng) {
            $lng = $DIC->language();
        }
        if (null === $access) {
            $access = $DIC->access();
        }
        if (null === $backgroundTasks) {
            $backgroundTasks = $DIC->backgroundTasks();
        }
        if (null === $user) {
            $user = $DIC->user();
        }
        if (null === $learningHistoryService) {
            $learningHistoryService = $DIC->learningHistory();
        }

        if (null === $certificateSettings) {
            $certificateSettings = new \ilSetting('certificate');
        }

        if (null === $migrationValidator) {
            $migrationValidator = new \ilCertificateMigrationValidator($certificateSettings);
        }
        $this->migrationValidator = $migrationValidator;

        if (null === $errorHandler) {
            $errorHandler = $DIC['ilErr'];
        }
        $this->errorHandler = $errorHandler;

        if (null === $logger) {
            $logger = $DIC->logger()->cert();
        }
        $this->logger = $logger;

        $this->ctrl = $ctrl;
        $lng->loadLanguageModule('cert');
        $this->lng = $lng;
        $this->access = $access;
        $this->user = $user;
        $this->backgroundTasks = $backgroundTasks;
        $this->learningHistoryService = $learningHistoryService;
    }

    /**
     * execute command
     * @return mixed
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);

        $cmd = $this->getCommand($cmd);
        switch ($next_class) {
            default:
                $ret = $this->$cmd();
                break;
        }

        return $ret;
    }

    /**
     * Retrieves the ilCtrl command
     * @param string $cmd
     * @return mixed
     */
    public function getCommand(string $cmd) : string
    {
        return $cmd;
    }

    /**
     * @return string
     * @throws \ilException
     */
    public function startMigrationAndReturnMessage() : string
    {
        $isMigrationAvailable = $this->migrationValidator->isMigrationAvailable(
            $this->user,
            new \ilCertificateMigration($this->user->getId())
        );
        if (false === $isMigrationAvailable) {
            $this->logger->error('Tried to execute user certificate migration, but the migration has already been executed');
            return '';
        }

        $factory = $this->backgroundTasks->taskFactory();
        $taskManager = $this->backgroundTasks->taskManager();

        $bucket = new \ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket();
        $bucket->setUserId($this->user->getId());

        $task = $factory->createTask(\ilCertificateMigrationJob::class, [(int) $this->user->getId()]);

        $interaction = \ilCertificateMigrationInteraction::class;
        if (!$this->learningHistoryService->isActive()) {
            $interaction = \ilCertificateMigrationReducedInteraction::class;
        }
        $certificates_interaction = $factory->createTask($interaction, [
            $task,
            (int) $this->user->getId()
        ]);

        $bucket->setTask($certificates_interaction);
        $bucket->setTitle('Certificate Migration');
        $bucket->setDescription('Migrates certificates for active user');

        $taskManager->run($bucket);

        return $this->lng->txt('certificate_migration_confirm_started');
    }
}
