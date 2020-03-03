<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceHelper
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceHelper
{
    /** @var \ilDBInterface */
    protected $database;

    /** @var \ilTermsOfServiceDataGatewayFactory */
    protected $dataGatewayFactory;

    /**
     * ilTermsOfServiceHelper constructor.
     * @param \ilDBInterface|null                     $database
     * @param \ilTermsOfServiceDataGatewayFactory|null $dataGatewayFactory
     */
    public function __construct(
        \ilDBInterface $database = null,
        \ilTermsOfServiceDataGatewayFactory $dataGatewayFactory = null
    ) {
        global $DIC;

        if (null === $database) {
            $database = $DIC->database();
        }
        $this->database = $database;

        if (null === $dataGatewayFactory) {
            $dataGatewayFactory = new \ilTermsOfServiceDataGatewayFactory();
            $dataGatewayFactory->setDatabaseAdapter($this->database);
        }
        $this->dataGatewayFactory = $dataGatewayFactory;
    }

    /**
     * @return bool
     */
    public static function isEnabled() : bool
    {
        global $DIC;

        return (bool) $DIC['ilSetting']->get('tos_status', false);
    }

    /**
     * @param bool $status
     */
    public static function setStatus(bool $status)
    {
        global $DIC;

        $DIC['ilSetting']->set('tos_status', (int) $status);
    }

    /**
     * @param int $userId
     * @throws \ilTermsOfServiceMissingDatabaseAdapterException
     */
    public function deleteAcceptanceHistoryByUser(int $userId)
    {
        $entity = $this->getEntityFactory()->getByName('ilTermsOfServiceAcceptanceEntity');
        $databaseGateway = $this->getDataGatewayFactory()->getByName('ilTermsOfServiceAcceptanceDatabaseGateway');

        $databaseGateway->deleteAcceptanceHistoryByUser($entity->withUserId($userId));
    }

    /**
     * @param \ilObjUser $user
     * @return \ilTermsOfServiceAcceptanceEntity
     * @throws \ilTermsOfServiceMissingDatabaseAdapterException
     */
    public function getCurrentAcceptanceForUser(\ilObjUser $user) : \ilTermsOfServiceAcceptanceEntity
    {
        $entity = $this->getEntityFactory()->getByName('ilTermsOfServiceAcceptanceEntity');
        $databaseGateway = $this->getDataGatewayFactory()->getByName('ilTermsOfServiceAcceptanceDatabaseGateway');

        return $databaseGateway->loadCurrentAcceptanceOfUser($entity->withUserId((int) $user->getId()));
    }

    /**
     * @param int $id
     * @return \ilTermsOfServiceAcceptanceEntity
     * @throws \ilTermsOfServiceMissingDatabaseAdapterException
     */
    public function getById(int $id) : \ilTermsOfServiceAcceptanceEntity
    {
        $entity = $this->getEntityFactory()->getByName('ilTermsOfServiceAcceptanceEntity');
        $databaseGateway = $this->getDataGatewayFactory()->getByName('ilTermsOfServiceAcceptanceDatabaseGateway');

        return $databaseGateway->loadById($entity->withId($id));
    }

    /**
     * @param \ilObjUser $user
     * @param \ilTermsOfServiceSignableDocument $document
     * @throws \ilTermsOfServiceMissingDatabaseAdapterException
     */
    public function trackAcceptance(\ilObjUser $user, \ilTermsOfServiceSignableDocument $document)
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

        $criteriaBag = new \ilTermsOfServiceAcceptanceHistoryCriteriaBag($document->criteria());
        $entity = $entity->withSerializedCriteria($criteriaBag->toJson());

        $databaseGateway->trackAcceptance($entity);

        $user->writeAccepted();

        $user->hasToAcceptTermsOfServiceInSession(false);
    }

    /**
     * @return \ilTermsOfServiceEntityFactory
     */
    private function getEntityFactory() : \ilTermsOfServiceEntityFactory
    {
        return new \ilTermsOfServiceEntityFactory();
    }

    /**
     * @return \ilTermsOfServiceDataGatewayFactory
     */
    private function getDataGatewayFactory() : \ilTermsOfServiceDataGatewayFactory
    {
        return $this->dataGatewayFactory;
    }
}
