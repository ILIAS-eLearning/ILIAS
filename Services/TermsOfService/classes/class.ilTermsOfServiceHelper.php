<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ilTermsOfServiceHelper
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceHelper
{
    /** @var ilTermsOfServiceDataGatewayFactory */
    protected $dataGatewayFactory;
    /** @var ilTermsOfServiceDocumentEvaluation */
    protected $termsOfServiceEvaluation;
    /** @var ilTermsOfServiceCriterionTypeFactoryInterface */
    protected $criterionTypeFactory;
    /** @var ilObjTermsOfService */
    protected $tos;

    /**
     * ilTermsOfServiceHelper constructor.
     * @param ilTermsOfServiceDataGatewayFactory|null $dataGatewayFactory
     * @param ilTermsOfServiceDocumentEvaluation|null $termsOfServiceEvaluation
     * @param ilTermsOfServiceCriterionTypeFactoryInterface|null $criterionTypeFactory
     * @param ilObjTermsOfService|null $tos
     */
    public function __construct(
        ilTermsOfServiceDataGatewayFactory $dataGatewayFactory = null,
        ilTermsOfServiceDocumentEvaluation $termsOfServiceEvaluation = null,
        ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory = null,
        ilObjTermsOfService $tos = null
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

    /**
     * @return bool
     */
    public static function isEnabled() : bool
    {
        return (new static())->tos->getStatus();
    }

    /**
     * @param bool $status
     */
    public static function setStatus(bool $status) : void
    {
        (new static())->tos->setReevaluateOnLogin($status);
    }

    /**
     * @param int $userId
     * @throws ilTermsOfServiceMissingDatabaseAdapterException
     */
    public function deleteAcceptanceHistoryByUser(int $userId) : void
    {
        $entity = $this->getEntityFactory()->getByName('ilTermsOfServiceAcceptanceEntity');
        $databaseGateway = $this->getDataGatewayFactory()->getByName('ilTermsOfServiceAcceptanceDatabaseGateway');

        $databaseGateway->deleteAcceptanceHistoryByUser($entity->withUserId($userId));
    }

    /**
     * @param ilObjUser $user
     * @return ilTermsOfServiceAcceptanceEntity
     * @throws ilTermsOfServiceMissingDatabaseAdapterException
     */
    public function getCurrentAcceptanceForUser(ilObjUser $user) : ilTermsOfServiceAcceptanceEntity
    {
        $entity = $this->getEntityFactory()->getByName('ilTermsOfServiceAcceptanceEntity');
        $databaseGateway = $this->getDataGatewayFactory()->getByName('ilTermsOfServiceAcceptanceDatabaseGateway');

        return $databaseGateway->loadCurrentAcceptanceOfUser($entity->withUserId((int) $user->getId()));
    }

    /**
     * @param int $id
     * @return ilTermsOfServiceAcceptanceEntity
     * @throws ilTermsOfServiceMissingDatabaseAdapterException
     */
    public function getById(int $id) : ilTermsOfServiceAcceptanceEntity
    {
        $entity = $this->getEntityFactory()->getByName('ilTermsOfServiceAcceptanceEntity');
        $databaseGateway = $this->getDataGatewayFactory()->getByName('ilTermsOfServiceAcceptanceDatabaseGateway');

        return $databaseGateway->loadById($entity->withId($id));
    }

    /**
     * @param ilObjUser $user
     * @param ilTermsOfServiceSignableDocument $document
     * @throws ilTermsOfServiceMissingDatabaseAdapterException
     * @throws ilTermsOfServiceUnexpectedCriteriaBagContentException
     */
    public function trackAcceptance(ilObjUser $user, ilTermsOfServiceSignableDocument $document) : void
    {
        $entity = $this->getEntityFactory()->getByName('ilTermsOfServiceAcceptanceEntity');
        $databaseGateway = $this->getDataGatewayFactory()->getByName('ilTermsOfServiceAcceptanceDatabaseGateway');

        $entity = $entity
            ->withUserId((int) $user->getId())
            ->withTimestamp(time())
            ->withText((string) $document->content())
            ->withHash(md5($document->content()))
            ->withDocumentId((int) $document->id())
            ->withTitle((string) $document->title());

        $criteriaBag = new ilTermsOfServiceAcceptanceHistoryCriteriaBag($document->criteria());
        $entity = $entity->withSerializedCriteria($criteriaBag->toJson());

        $databaseGateway->trackAcceptance($entity);

        $user->writeAccepted();

        $user->hasToAcceptTermsOfServiceInSession(false);
    }

    /**
     * @param ilObjUser $user
     */
    public function resetAcceptance(ilObjUser $user) : void
    {
        $user->setAgreeDate(null);
        $user->update();
    }

    /**
     * @param ilObjUser $user
     * @param ilLogger $logger
     * @return bool
     */
    public function hasToResignAcceptance(ilObjUser $user, ilLogger $logger) : bool
    {
        $logger->debug(sprintf(
            'Checking reevaluation of Terms of Service for user "%s" (id: %s) ...',
            $user->getLogin(),
            $user->getId()
        ));

        if (!$this->tos->getStatus()) {
            $logger->debug(sprintf(
                'Terms of Service disabled, resigning not required ...'
            ));
            return false;
        }

        if (!$this->tos->shouldReevaluateOnLogin()) {
            $logger->debug(sprintf(
                'Reevaluation of documents is not enabled, resigning not required ...'
            ));
            return false;
        }

        if (!$user->getAgreeDate()) {
            $logger->debug(sprintf(
                'Terms of Service currently not accepted by user, resigning not required ...'
            ));
            return false;
        }

        $evaluator = $this->termsOfServiceEvaluation->withContextUser($user);
        if (!$evaluator->hasDocument()) {
            $logger->debug(sprintf(
                'No signable Terms of Service document found, resigning not required ...'
            ));
            return false;
        }

        $entity = $this->getCurrentAcceptanceForUser($user);
        if (!($entity->getId() > 0)) {
            $logger->debug(sprintf(
                'No signed Terms of Service document found, resigning not required ...'
            ));
            return false;
        }

        $historizedDocument = new ilTermsOfServiceHistorizedDocument(
            $entity,
            new ilTermsOfServiceAcceptanceHistoryCriteriaBag($entity->getSerializedCriteria()),
            $this->criterionTypeFactory
        );

        if ($evaluator->evaluateDocument($historizedDocument)) {
            $logger->debug(sprintf(
                'Current user values do still match historized criteria, resigning not required ...'
            ));
            return false;
        }

        $logger->debug(sprintf(
            'Current user values do not match historized criteria, resigning required ...'
        ));
        return true;
    }

    /**
     * @return ilTermsOfServiceEntityFactory
     */
    private function getEntityFactory() : ilTermsOfServiceEntityFactory
    {
        return new ilTermsOfServiceEntityFactory();
    }

    /**
     * @return ilTermsOfServiceDataGatewayFactory
     */
    private function getDataGatewayFactory() : ilTermsOfServiceDataGatewayFactory
    {
        return $this->dataGatewayFactory;
    }

    /**
     * @param ServerRequestInterface $httpRequest
     */
    public static function setWithdrawalInfo(ServerRequestInterface $httpRequest) : void
    {
        if (isset($httpRequest->getQueryParams()['wdtdel'])) {
            if ($httpRequest->getQueryParams()['wdtdel'] == 1) {
                ilUtil::sendInfo($GLOBALS['lng']->txt('withdrawal_complete_deleted'));
            } else {
                ilUtil::sendInfo($GLOBALS['lng']->txt('withdrawal_complete_redirect'));
            }
        }
    }

    public static function appendWithdrawalText(int $withdrawal_relogin) : string
    {
        $withdrawal_appendage_text = '';
        if ($withdrawal_relogin !== 0) {
            $withdrawal_appendage_text = '<br /><br />';
            if ($withdrawal_relogin == 'internal') {
                $withdrawal_appendage_text .= $GLOBALS['lng']->txt('withdraw_consent_description_internal');
            } else {
                $withdrawal_appendage_text .= $GLOBALS['lng']->txt('withdraw_consent_description_external');
            }
        }
        return $withdrawal_appendage_text;
    }

    public static function handleWithdrawalRequest(ilObjUser $user, object $gui_class) : void
    {
        global $DIC;
        $defaultAuth = AUTH_LOCAL;
        if ($DIC['ilSetting']->get('auth_mode')) {
            $defaultAuth = $DIC['ilSetting']->get('auth_mode');
        }

        $external = false;
        if (
            $user->getAuthMode() == AUTH_PROVIDER_LTI ||
            $user->getAuthMode() == AUTH_ECS ||
            ($user->getAuthMode() === 'default' && $defaultAuth == AUTH_PROVIDER_LTI) ||
            ($user->getAuthMode() === 'default' && $defaultAuth == AUTH_ECS)
        ) {
            $external = true;
        }

        $user->writePref('consent_withdrawal_requested', 1);

        if ($external) {
            $DIC->ctrl->setParameter($gui_class, 'withdrawal_relogin_content', 'external');
        } else {
            $DIC->ctrl->setParameter($gui_class, 'withdrawal_relogin_content', 'internal');
        }
    }
}
