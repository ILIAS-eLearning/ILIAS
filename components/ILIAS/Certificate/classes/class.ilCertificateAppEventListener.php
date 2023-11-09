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

use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Certificate\API\UserCertificateApiInterface;

class ilCertificateAppEventListener implements ilAppEventListener
{
    private string $component = '';
    private string $event = '';
    /** @var array<string, mixed> */
    private array $parameters = [];

    private readonly ilCertificateQueueRepository $certificateQueueRepository;
    private readonly ilCertificateTemplateRepository $templateRepository;
    private readonly ilUserCertificateRepository $userCertificateRepository;

    public function __construct(
        private readonly UserCertificateApiInterface $user_certificate_api,
        private readonly ilDBInterface $db,
        private readonly ilObjectDataCache $objectDataCache,
        private readonly ilLogger $logger
    ) {
        $this->certificateQueueRepository = new ilCertificateQueueRepository($this->db, $this->logger);
        $this->templateRepository = new ilCertificateTemplateDatabaseRepository($this->db, $this->logger);
        $this->userCertificateRepository = new ilUserCertificateRepository($this->db, $this->logger);
    }

    public function withComponent(string $component): self
    {
        $clone = clone $this;

        $clone->component = $component;

        return $clone;
    }

    public function withEvent(string $event): self
    {
        $clone = clone $this;

        $clone->event = $event;

        return $clone;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function withParameters(array $parameters): self
    {
        $clone = clone $this;

        $clone->parameters = $parameters;

        return $clone;
    }

    protected function isLearningAchievementEvent(): bool
    {
        return (
            $this->component === 'components/ILIAS/Tracking' &&
            $this->event === 'updateStatus'
        );
    }

    protected function isUserDeletedEvent(): bool
    {
        return (
            $this->component === 'components/ILIAS/User' &&
            $this->event === 'deleteUser'
        );
    }

    /**
     * @throws IOException
     */
    public function handle(): void
    {
        try {
            if ($this->isLearningAchievementEvent()) {
                $this->handleLPUpdate();
            } elseif ($this->isUserDeletedEvent()) {
                $this->handleDeletedUser();
            }
        } catch (ilException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public static function handleEvent(string $a_component, string $a_event, array $a_parameter): void
    {
        global $DIC;

        $listener = new self(
            $DIC->certificate()->userCertificates(),
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

    private function handleLPUpdate(): void
    {
        $status = (int) ($this->parameters['status'] ?? ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM);
        if ($status !== ilLPStatus::LP_STATUS_COMPLETED_NUM) {
            return;
        }

        $object_id = (int) ($this->parameters['obj_id'] ?? 0);
        $usr_id = (int) ($this->parameters['usr_id'] ?? 0);
        $type = $this->objectDataCache->lookupType($object_id);

        $this->logger->debug(sprintf(
            "Certificate evaluation triggered, received 'completed' learning progress for: usr_id: %s/obj_id: %s/type: %s",
            $usr_id,
            $object_id,
            $type
        ));

        try {
            $this->user_certificate_api->certificateCriteriaMet(
                $usr_id,
                $object_id
            );
        } catch (ilCouldNotFindCertificateTemplate | ilCertificateConsumerNotSupported) {
            $this->logger->debug(sprintf(
                'Consumer not supported, or did not find an active certificate template for case: usr_id: %s/obj_id: %s/type: %s',
                $usr_id,
                $object_id,
                $type
            ));
        } catch (ilException $e) {
            $this->logger->warning($e->getMessage());
        }

        if ($type === 'crs') {
            $this->logger->debug(
                'Skipping handling for course, because courses cannot be certificate trigger ' .
                '(with globally disabled learning progress) for other certificate enabled objects'
            );
            return;
        }

        $this->logger->debug(
            'Triggering certificate evaluation of possible depending course objects ...'
        );

        $progressEvaluation = new ilCertificateCourseLearningProgressEvaluation(
            new ilCachedCertificateTemplateRepository(
                $this->templateRepository
            )
        );
        foreach (ilObject::_getAllReferences($object_id) as $refId) {
            $templatesOfCompletedCourses = $progressEvaluation->evaluate($refId, $usr_id);
            if ([] === $templatesOfCompletedCourses) {
                $this->logger->debug(sprintf(
                    'No dependent course certificate template configuration found for child object: usr_id: %s/obj_id: %s/ref_id: %s/type: %s',
                    $usr_id,
                    $object_id,
                    $refId,
                    $type
                ));
                continue;
            }

            foreach ($templatesOfCompletedCourses as $courseTemplate) {
                // We do not check if we support the type anymore, because the type 'crs' is always supported
                try {
                    $this->user_certificate_api->certificateCriteriaMetForGivenTemplate(
                        $usr_id,
                        $courseTemplate
                    );
                } catch (ilException $e) {
                    $this->logger->warning($e->getMessage());
                    continue;
                }
            }
        }

        $this->logger->debug(
            'Finished certificate evaluation'
        );
    }

    private function handleDeletedUser(): void
    {
        if (!array_key_exists('usr_id', $this->parameters)) {
            $this->logger->error('User ID is not added to the event. Abort.');
            return;
        }

        $this->logger->debug('User has been deleted. Try to delete user certificates');

        $userId = (int) $this->parameters['usr_id'];

        $this->userCertificateRepository->deleteUserCertificates($userId);
        $this->certificateQueueRepository->removeFromQueueByUserId($userId);

        $portfolioFileService = new ilPortfolioCertificateFileService();
        $portfolioFileService->deleteUserDirectory($userId);

        $this->logger->debug(sprintf(
            'All relevant data sources for the user certificates for user (usr_id: "%s" deleted)',
            $userId
        ));
    }
}
