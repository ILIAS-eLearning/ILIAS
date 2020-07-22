<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCertificateAppEventListener
 *
 * @author Niels Theen <ntheen@databay.de>
 * @version $Id:$
 *
 * @package Services/Certificate
 */
class ilCertificateAppEventListener implements ilAppEventListener
{
    /** @var \ilDBInterface */
    protected $db;

    /** @var ilObjectDataCache */
    private $objectDataCache;

    /** @var ilLogger */
    private $logger;

    /** @var string */
    protected $component = '';

    /** @var string */
    protected $event = '';

    /** @var array */
    protected $parameters = [];

    /**
     * @var ilCertificateQueueRepository
     */
    private $certificateQueueRepository;

    /**
     * @var ilCertificateTypeClassMap
     */
    private $certificateClassMap;

    /**
     * @var ilCertificateTemplateRepository
     */
    private $templateRepository;

    /**
     * @var ilUserCertificateRepository
     */
    private $userCertificateRepository;

    /**
     * @var ilCertificateMigrationRepository
     */
    private $migrationRepository;

    /**
     * ilCertificateAppEventListener constructor.
     * @param \ilDBInterface $db
     * @param \ilObjectDataCache $objectDataCache
     * @param \ilLogger $logger
     */
    public function __construct(
        \ilDBInterface $db,
        \ilObjectDataCache $objectDataCache,
        \ilLogger $logger
    ) {
        $this->db = $db;
        $this->objectDataCache = $objectDataCache;
        $this->logger = $logger;
        $this->certificateQueueRepository = new \ilCertificateQueueRepository($this->db, $this->logger);
        $this->certificateClassMap = new \ilCertificateTypeClassMap();
        $this->templateRepository = new \ilCertificateTemplateRepository($this->db, $this->logger);
        $this->userCertificateRepository = new \ilUserCertificateRepository($this->db, $this->logger);
        $this->migrationRepository = new ilCertificateMigrationRepository($this->db, $this->logger);
    }

    /**
     * @param string $component
     * @return \ilCertificateAppEventListener
     */
    public function withComponent(string $component) : self
    {
        $clone = clone $this;

        $clone->component = $component;

        return $clone;
    }

    /**
     * @param string $event
     * @return \ilCertificateAppEventListener
     */
    public function withEvent(string $event) : self
    {
        $clone = clone $this;

        $clone->event = $event;

        return $clone;
    }

    /**
     * @param array $parameters
     * @return \ilCertificateAppEventListener
     */
    public function withParameters(array $parameters) : self
    {
        $clone = clone $this;

        $clone->parameters = $parameters;

        return $clone;
    }

    /**
     * @return bool
     */
    protected function isLearningAchievementEvent() : bool
    {
        return (
            'Services/Tracking' === $this->component &&
            'updateStatus' === $this->event
        );
    }

    /**
     * @return bool
     */
    protected function isMigratingCertificateEvent() : bool
    {
        return (
            'Services/Certificate' === $this->component &&
            'migrateUserCertificate' === $this->event
        );
    }

    /**
     * @return bool
     */
    protected function isUserDeletedEvent() : bool
    {
        return (
            'Services/User' === $this->component &&
            'deleteUser' === $this->event
        );
    }

    /**
     *
     */
    public function handle()
    {
        try {
            if ($this->isLearningAchievementEvent()) {
                $this->handleLPUpdate();
            } elseif ($this->isMigratingCertificateEvent()) {
                $this->handleNewMigratedUserCertificate();
            } elseif ($this->isUserDeletedEvent()) {
                $this->handleDeletedUser();
            }
        } catch (\ilException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public static function handleEvent($a_component, $a_event, $a_parameter)
    {
        global $DIC;

        $listener = new static(
            $DIC->database(),
            $DIC['ilObjDataCache'],
            $DIC->logger()->cert()
        );

        $listener
            ->withComponent($a_component)
            ->withEvent($a_event)
            ->withParameters($a_parameter)
            ->handle();
    }

    /**
     * @throws \ilException
     */
    private function handleLPUpdate()
    {
        $status = $this->parameters['status'] ?? \ilLpStatus::LP_STATUS_NOT_ATTEMPTED_NUM;

        $settings = new ilSetting('certificate');

        if ($status == \ilLPStatus::LP_STATUS_COMPLETED_NUM) {
            $objectId = $this->parameters['obj_id'] ?? 0;
            $userId = $this->parameters['usr_id'] ?? 0;

            $type = $this->objectDataCache->lookupType($objectId);

            $this->logger->info(sprintf(
                "Certificate evaluation triggered, received 'completed' learning progress for: usr_id: %s/obj_id: %s/type: %s",
                $userId,
                $objectId,
                $type
            ));

            if ($this->certificateClassMap->typeExistsInMap($type)) {
                try {
                    $template = $this->templateRepository->fetchCurrentlyActiveCertificate($objectId);

                    if (true === $template->isCurrentlyActive()) {
                        $this->logger->info(sprintf(
                            "Trigger persisting certificate achievement for: usr_id: %s/obj_id: %s/type: %s/template_id: %s",
                            $userId,
                            $objectId,
                            $type,
                            $template->getId()
                        ));
                        $this->processEntry($type, $objectId, $userId, $template, $settings);
                    } else {
                        $this->logger->info(sprintf(
                            "Did not trigger certificate achievement for inactive template: usr_id: %s/obj_id: %s/type: %s/template_id: %s",
                            $userId,
                            $objectId,
                            $type,
                            $template->getId()
                        ));
                    }
                } catch (ilException $exception) {
                    $this->logger->info(sprintf(
                        "Did not find an active certificate template for case: usr_id: %s/obj_id: %s/type: %s",
                        $userId,
                        $objectId,
                        $type
                    ));
                }
            } else {
                $this->logger->info(sprintf(
                    "Object type is not of interest, skipping certificate evaluation for this object"
                ));
            }

            if ($type === 'crs') {
                $this->logger->info(
                    'Skipping handling for course, because courses cannot be certificate trigger ' .
                    '(with globally disabled learning progress) for other certificate enabled objects'
                );
                return;
            }

            $this->logger->info(
                'Triggering certificate evaluation of possible depending course objects ...'
            );

            foreach (\ilObject::_getAllReferences($objectId) as $refId) {
                $templateRepository = new \ilCertificateTemplateRepository($this->db, $this->logger);
                $progressEvaluation = new \ilCertificateCourseLearningProgressEvaluation($templateRepository);

                $templatesOfCompletedCourses = $progressEvaluation->evaluate($refId, $userId);
                if (0 === count($templatesOfCompletedCourses)) {
                    $this->logger->info(sprintf(
                        "No dependent course certificate template configuration found for child object: usr_id: %s/obj_id: %s/ref_id: %s/type: %s",
                        $userId,
                        $objectId,
                        $refId,
                        $type
                    ));
                    continue;
                }

                foreach ($templatesOfCompletedCourses as $courseTemplate) {
                    // We do not check if we support the type anymore, because the type 'crs' is always supported
                    try {
                        $courseObjectId = $courseTemplate->getObjId();

                        if (true === $courseTemplate->isCurrentlyActive()) {
                            $type = $this->objectDataCache->lookupType($courseObjectId);

                            $this->logger->info(sprintf(
                                "Trigger persisting certificate achievement for: usr_id: %s/obj_id: %s/type: %s/template_id: %s",
                                $userId,
                                $courseObjectId,
                                'crs',
                                $courseTemplate->getId()
                            ));
                            $this->processEntry($type, $courseObjectId, $userId, $courseTemplate, $settings);
                        } else {
                            $this->logger->info(sprintf(
                                "Did not trigger certificate achievement for inactive template: usr_id: %s/obj_id: %s/type: %s/template_id: %s",
                                $userId,
                                $objectId,
                                $type,
                                $courseTemplate->getId()
                            ));
                        }
                    } catch (ilException $exception) {
                        $this->logger->warning($exception->getMessage());
                        continue;
                    }
                }
            }

            $this->logger->info(
                'Finished certificate evaluation'
            );
        }
    }

    /**
     * @throws \ilDatabaseException
     * @throws \ilException
     */
    private function handleNewMigratedUserCertificate()
    {
        $this->logger->info('Try to create new certificates based on event');

        if (false === array_key_exists('obj_id', $this->parameters)) {
            $this->logger->error('Object ID is not added to the event. Abort.');
            return;
        }

        if (false === array_key_exists('user_id', $this->parameters)) {
            $this->logger->error('User ID is not added to the event. Abort.');
            return;
        }

        if (false === array_key_exists('background_image_path', $this->parameters)) {
            $this->logger->error('Background Image Path is not added to the event. Abort.');
            return;
        }

        if (false === array_key_exists('acquired_timestamp', $this->parameters)) {
            $this->logger->error('Acquired Timestamp is not added to the event. Abort.');
            return;
        }

        if (false === array_key_exists('ilias_version', $this->parameters)) {
            $this->logger->error('ILIAS version is not added to the event. Abort.');
            return;
        }

        if (false === array_key_exists('certificate_content', $this->parameters)) {
            $this->logger->error('Certificate content is not added to the event. Abort.');
            return;
        }

        $objId = $this->parameters['obj_id'] ?? 0;
        $userId = $this->parameters['user_id'] ?? 0;
        $backgroundImagePath = $this->parameters['background_image_path'] ?? '';
        $acquiredTimestamp = $this->parameters['acquired_timestamp'] ?? '';
        $iliasVersion = $this->parameters['ilias_version'] ?? '';
        $certificateContent = $this->parameters['certificate_content'] ?? '';

        if ('' === $certificateContent) {
            $this->logger->error('Certificate content is empty. Abort.');
            return;
        }

        if ('' === $acquiredTimestamp || $acquiredTimestamp <= 0) {
            $this->logger->error('Acquired Timestamp is empty. Abort.');
            return;
        }

        $templateRepository = new \ilCertificateTemplateRepository($this->db, $this->logger);
        $template = $templateRepository->fetchFirstCreatedTemplate($objId);

        try {
            $certificate = $this->userCertificateRepository->fetchActiveCertificate($userId, $objId);
            $this->logger->error(sprintf('There are already certificates generated for user_id "%s" and object_id "%s". Abort.', $userId, $objId));
            return;
        } catch (ilException $exception) {
            $this->logger->info('No active user certificate found. Resume migration.');
        }

        $type = $this->objectDataCache->lookupType($objId);

        $classMap = new ilCertificateTypeClassMap();
        if (!$classMap->typeExistsInMap($type)) {
            $this->logger->error(sprintf('Migrations for type "%s" not supported. Abort.', $type));
            return;
        }

        $user = \ilObjectFactory::getInstanceByObjId($userId, false);
        if (!$user || !($user instanceof \ilObjUser)) {
            throw new \ilException(sprintf('The given user ID("%s") is not a user', $userId));
        }

        $userCertificate = new \ilUserCertificate(
            $template->getId(),
            $objId,
            $type,
            $userId,
            $user->getFullname(),
            $acquiredTimestamp,
            $certificateContent,
            '',
            null,
            1,
            $iliasVersion,
            true,
            $backgroundImagePath,
            ''
        );

        $this->userCertificateRepository->save($userCertificate);
    }

    /**
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    private function handleDeletedUser()
    {
        $portfolioFileService = new ilPortfolioCertificateFileService();

        if (false === array_key_exists('usr_id', $this->parameters)) {
            $this->logger->error('User ID is not added to the event. Abort.');
            return;
        }

        $this->logger->info('User has been deleted. Try to delete user certificates');

        $userId = $this->parameters['usr_id'];

        $this->userCertificateRepository->deleteUserCertificates((int) $userId);

        $this->certificateQueueRepository->removeFromQueueByUserId((int) $userId);

        $this->migrationRepository->deleteFromMigrationJob((int) $userId);

        $portfolioFileService->deleteUserDirectory($userId);

        $this->logger->info(sprintf('All relevant data sources for the user certificates for user(user_id: "%s" deleted)', $userId));
    }

    /**
     * @param $type
     * @param $objectId
     * @param int $userId
     * @param ilCertificateTemplate $template
     * @param ilSetting $settings
     * @throws ilDatabaseException
     * @throws ilException
     * @throws ilInvalidCertificateException
     */
    private function processEntry($type, $objectId, int $userId, ilCertificateTemplate $template, ilSetting $settings)
    {
        $className = $this->certificateClassMap->getPlaceHolderClassNameByType($type);

        $entry = new \ilCertificateQueueEntry(
            $objectId,
            $userId,
            $className,
            \ilCronConstants::IN_PROGRESS,
            $template->getId(),
            time()
        );

        $mode = $settings->get('persistent_certificate_mode', 'persistent_certificate_mode_cron');
        if ($mode === 'persistent_certificate_mode_instant') {
            $cronjob = new ilCertificateCron();
            $cronjob->init();
            $cronjob->processEntry(0, $entry, array());
            return;
        }

        $this->certificateQueueRepository->addToQueue($entry);
    }
}
