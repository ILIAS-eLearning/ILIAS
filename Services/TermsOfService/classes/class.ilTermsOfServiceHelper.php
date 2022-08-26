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

/**
 * Class ilTermsOfServiceHelper
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceHelper
{
    protected ilTermsOfServiceDataGatewayFactory $dataGatewayFactory;
    protected ilTermsOfServiceDocumentEvaluation $termsOfServiceEvaluation;
    protected ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory;
    protected ilObjTermsOfService $tos;

    public function __construct(
        ?ilTermsOfServiceDataGatewayFactory $dataGatewayFactory = null,
        ?ilTermsOfServiceDocumentEvaluation $termsOfServiceEvaluation = null,
        ?ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory = null,
        ?ilObjTermsOfService $tos = null
    ) {
        global $DIC;

        if (null === $dataGatewayFactory) {
            $dataGatewayFactory = new ilTermsOfServiceDataGatewayFactory();
            $dataGatewayFactory->setDatabaseAdapter($DIC->database());
        }
        $this->dataGatewayFactory = $dataGatewayFactory;

        if (null === $termsOfServiceEvaluation) {
            $termsOfServiceEvaluation = $DIC['tos.document.evaluator'];
        }
        $this->termsOfServiceEvaluation = $termsOfServiceEvaluation;

        if (null === $criterionTypeFactory) {
            $criterionTypeFactory = $DIC['tos.criteria.type.factory'];
        }
        $this->criterionTypeFactory = $criterionTypeFactory;

        if (null === $tos) {
            $tos = new ilObjTermsOfService();
        }
        $this->tos = $tos;
    }

    public static function isEnabled(): bool
    {
        return (new self())->tos->getStatus();
    }

    public function isGloballyEnabled(): bool
    {
        return $this->tos->getStatus();
    }

    /**
     * @throws ilTermsOfServiceMissingDatabaseAdapterException
     */
    public function deleteAcceptanceHistoryByUser(int $userId): void
    {
        $entity = $this->getEntityFactory()->getByName('ilTermsOfServiceAcceptanceEntity');
        $databaseGateway = $this->getDataGatewayFactory()->getByName('ilTermsOfServiceAcceptanceDatabaseGateway');

        $databaseGateway->deleteAcceptanceHistoryByUser($entity->withUserId($userId));
    }

    /**
     * @throws ilTermsOfServiceMissingDatabaseAdapterException
     */
    public function getCurrentAcceptanceForUser(ilObjUser $user): ilTermsOfServiceAcceptanceEntity
    {
        $entity = $this->getEntityFactory()->getByName('ilTermsOfServiceAcceptanceEntity');
        $databaseGateway = $this->getDataGatewayFactory()->getByName('ilTermsOfServiceAcceptanceDatabaseGateway');

        return $databaseGateway->loadCurrentAcceptanceOfUser($entity->withUserId($user->getId()));
    }

    /**
     * @throws ilTermsOfServiceMissingDatabaseAdapterException
     */
    public function getById(int $id): ilTermsOfServiceAcceptanceEntity
    {
        $entity = $this->getEntityFactory()->getByName('ilTermsOfServiceAcceptanceEntity');
        $databaseGateway = $this->getDataGatewayFactory()->getByName('ilTermsOfServiceAcceptanceDatabaseGateway');

        return $databaseGateway->loadById($entity->withId($id));
    }

    /**
     * @throws ilTermsOfServiceMissingDatabaseAdapterException
     * @throws ilTermsOfServiceUnexpectedCriteriaBagContentException
     */
    public function trackAcceptance(ilObjUser $user, ilTermsOfServiceSignableDocument $document): void
    {
        $entity = $this->getEntityFactory()->getByName('ilTermsOfServiceAcceptanceEntity');
        $databaseGateway = $this->getDataGatewayFactory()->getByName('ilTermsOfServiceAcceptanceDatabaseGateway');

        $entity = $entity
            ->withUserId($user->getId())
            ->withTimestamp(time())
            ->withText($document->content())
            ->withHash(md5($document->content()))
            ->withDocumentId($document->id())
            ->withTitle($document->title());

        $criteriaBag = new ilTermsOfServiceAcceptanceHistoryCriteriaBag($document->criteria());
        $entity = $entity->withSerializedCriteria($criteriaBag->toJson());

        $databaseGateway->trackAcceptance($entity);

        $user->writeAccepted();

        $user->hasToAcceptTermsOfServiceInSession(false);
    }

    public function resetAcceptance(ilObjUser $user): void
    {
        $user->setAgreeDate(null);
        $user->update();
    }

    public function isIncludedUser(ilObjUser $user): bool
    {
        $excluded_roles = [];
        if (defined('ANONYMOUS_USER_ID')) {
            $excluded_roles[] = ANONYMOUS_USER_ID;
        }
        if (defined('SYSTEM_USER_ID')) {
            $excluded_roles[] = SYSTEM_USER_ID;
        }

        return (
            'root' !== $user->getLogin() &&
            !in_array($user->getId(), $excluded_roles, true) &&
            !$user->isAnonymous() &&
            $user->getId() > 0
        );
    }

    public function hasToResignAcceptance(ilObjUser $user, ilLogger $logger): bool
    {
        $logger->debug(sprintf(
            'Checking reevaluation of Terms of Service for user "%s" (id: %s) ...',
            $user->getLogin(),
            $user->getId()
        ));

        if (!$this->isGloballyEnabled()) {
            $logger->debug('Terms of Service disabled, resigning not required ...');
            return false;
        }

        if (!$this->isIncludedUser($user)) {
            $logger->debug('User is not included for Terms of Service acceptance, resigning not required ...');
            return false;
        }

        if (!$this->tos->shouldReevaluateOnLogin()) {
            $logger->debug('Reevaluation of documents is not enabled, resigning not required ...');
            return false;
        }

        if (!$user->getAgreeDate()) {
            $logger->debug('Terms of Service currently not accepted by user, resigning not required ...');
            return false;
        }

        $evaluator = $this->termsOfServiceEvaluation->withContextUser($user);
        if (!$evaluator->hasDocument()) {
            $logger->debug('No signable Terms of Service document found, resigning not required ...');
            return false;
        }

        $entity = $this->getCurrentAcceptanceForUser($user);
        if ($entity->getId() <= 0) {
            $logger->debug('No signed Terms of Service document found, resigning not required ...');
            return false;
        }

        $historizedDocument = new ilTermsOfServiceHistorizedDocument(
            $entity,
            new ilTermsOfServiceAcceptanceHistoryCriteriaBag($entity->getSerializedCriteria())
        );

        if ($evaluator->evaluateDocument($historizedDocument)) {
            $logger->debug('Current user values do still match historized criteria, resigning not required ...');
            return false;
        }

        $logger->debug('Current user values do not match historized criteria, resigning required ...');
        return true;
    }

    private function getEntityFactory(): ilTermsOfServiceEntityFactory
    {
        return new ilTermsOfServiceEntityFactory();
    }

    private function getDataGatewayFactory(): ilTermsOfServiceDataGatewayFactory
    {
        return $this->dataGatewayFactory;
    }
}
