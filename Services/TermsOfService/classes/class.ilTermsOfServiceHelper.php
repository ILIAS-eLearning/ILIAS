<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceHelper
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceHelper
{
	/**
	 * @var \ilDBInterface
	 */
	protected $database;

	/**
	 * ilTermsOfServiceHelper constructor.
	 * @param \ilDBInterface|null $database
	 */
	public function __construct(\ilDBInterface $database = null)
	{
		global $DIC;

		if (null === $database) {
			$database = $DIC->database();
		}

		$this->database = $database;
	}

	/**
	 * @return bool
	 */
	public static function isEnabled(): bool 
	{
		global $DIC;

		return (bool)$DIC['ilSetting']->get('tos_status', false);
	}

	/**
	 * @param bool $status
	 */
	public static function setStatus(bool $status)
	{
		global $DIC;

		$DIC['ilSetting']->set('tos_status', (int)$status);
	}

	/**
	 * @param int $userId
	 * @throws \ilTermsOfServiceMissingDatabaseAdapterException
	 */
	public function deleteAcceptanceHistoryByUser(int $userId)
	{
		$entity = $this->getEntityFactory()->getByName('ilTermsOfServiceAcceptanceEntity');
		$databaseGateway = $this->getDataGatewayFactory()->getByName('ilTermsOfServiceAcceptanceDatabaseGateway');

		$entity->setUserId($userId);
		$databaseGateway->deleteAcceptanceHistoryByUser($entity);
	}

	/**
	 * @param \ilObjUser $user
	 * @return \ilTermsOfServiceAcceptanceEntity
	 * @throws \ilTermsOfServiceMissingDatabaseAdapterException
	 */
	public function getCurrentAcceptanceForUser(\ilObjUser $user): \ilTermsOfServiceAcceptanceEntity
	{
		$entity = $this->getEntityFactory()->getByName('ilTermsOfServiceAcceptanceEntity');
		$databaseGateway = $this->getDataGatewayFactory()->getByName('ilTermsOfServiceAcceptanceDatabaseGateway');

		$entity->setUserId($user->getId());

		return $databaseGateway->loadCurrentAcceptanceOfUser($entity);
	}

	/**
	 * @param int $id
	 * @return \ilTermsOfServiceAcceptanceEntity
	 * @throws \ilTermsOfServiceMissingDatabaseAdapterException
	 */
	public function getById(int $id): \ilTermsOfServiceAcceptanceEntity
	{
		$entity = $this->getEntityFactory()->getByName('ilTermsOfServiceAcceptanceEntity');
		$databaseGateway = $this->getDataGatewayFactory()->getByName('ilTermsOfServiceAcceptanceDatabaseGateway');

		$entity->setId($id);

		return $databaseGateway->loadById($entity);
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

		$entity->setUserId($user->getId());
		$entity->setTimestamp(time());
		$entity->setText($document->getText());
		$entity->setHash(md5($document->getText()));
		$entity->setDocumentId($document->getId());
		$entity->setTitle($document->getTitle());

		$criteriaBag = new \ilTermsOfServiceAcceptanceHistoryCriteriaBag($document->getCriteria());
		$entity->setCriteria($criteriaBag->toJson());

		$databaseGateway->trackAcceptance($entity);

		$user->writeAccepted();

		$user->hasToAcceptTermsOfServiceInSession(false);
	}

	/**
	 * @return \ilTermsOfServiceEntityFactory
	 */
	private function getEntityFactory(): \ilTermsOfServiceEntityFactory
	{
		return new \ilTermsOfServiceEntityFactory();
	}

	/**
	 * @return \ilTermsOfServiceDataGatewayFactory
	 */
	private function getDataGatewayFactory(): \ilTermsOfServiceDataGatewayFactory
	{
		$factory = new \ilTermsOfServiceDataGatewayFactory();
		$factory->setDatabaseAdapter($this->database);

		return $factory;
	}
}
