<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceHelper
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceHelper
{
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
	public static function deleteAcceptanceHistoryByUser(int $userId)
	{
		$entity = self::getEntityFactory()->getByName('ilTermsOfServiceAcceptanceEntity');
		$data_gateway = self::getDataGatewayFactory()->getByName('ilTermsOfServiceAcceptanceDatabaseGateway');
		$entity->setUserId($userId);
		$data_gateway->deleteAcceptanceHistoryByUser($entity);
	}

	/**
	 * @param \ilObjUser $user
	 * @return \ilTermsOfServiceAcceptanceEntity
	 * @throws \ilTermsOfServiceMissingDatabaseAdapterException
	 */
	public static function getCurrentAcceptanceForUser(\ilObjUser $user): \ilTermsOfServiceAcceptanceEntity
	{
		$entity = self::getEntityFactory()->getByName('ilTermsOfServiceAcceptanceEntity');
		$data_gateway = self::getDataGatewayFactory()->getByName('ilTermsOfServiceAcceptanceDatabaseGateway');
		$entity->setUserId($user->getId());

		return $data_gateway->loadCurrentAcceptanceOfUser($entity);
	}

	/**
	 * @param int $id
	 * @return \ilTermsOfServiceAcceptanceEntity
	 * @throws \ilTermsOfServiceMissingDatabaseAdapterException
	 */
	public static function getById(int $id): \ilTermsOfServiceAcceptanceEntity
	{
		$entity = self::getEntityFactory()->getByName('ilTermsOfServiceAcceptanceEntity');
		$data_gateway = self::getDataGatewayFactory()->getByName('ilTermsOfServiceAcceptanceDatabaseGateway');
		$entity->setId($id);

		return $data_gateway->loadById($entity);
	}

	/**
	 * @param \ilObjUser $user
	 * @param \ilTermsOfServiceSignableDocument $document
	 * @throws \ilTermsOfServiceMissingDatabaseAdapterException
	 */
	public static function trackAcceptance(\ilObjUser $user, \ilTermsOfServiceSignableDocument $document)
	{
		$entity = self::getEntityFactory()->getByName('ilTermsOfServiceAcceptanceEntity');
		$data_gateway = self::getDataGatewayFactory()->getByName('ilTermsOfServiceAcceptanceDatabaseGateway');

		$entity->setUserId($user->getId());
		$entity->setTimestamp(time());
		$entity->setText($document->getText());
		$entity->setHash(md5($document->getText()));
		$entity->setDocumentId($document->getId());
		$entity->setTitle($document->getTitle());

		$criteriaBag = new \ilTermsOfServiceAcceptanceHistoryCriteriaBag($document->getCriteria());
		$entity->setCriteria($criteriaBag->toJson());

		$data_gateway->trackAcceptance($entity);

		$user->writeAccepted();

		$user->hasToAcceptTermsOfServiceInSession(false);
	}

	/**
	 * @return \ilTermsOfServiceEntityFactory
	 */
	private static function getEntityFactory(): \ilTermsOfServiceEntityFactory
	{
		return new \ilTermsOfServiceEntityFactory();
	}

	/**
	 * @return \ilTermsOfServiceDataGatewayFactory
	 */
	private static function getDataGatewayFactory(): \ilTermsOfServiceDataGatewayFactory
	{
		global $DIC;

		$factory = new \ilTermsOfServiceDataGatewayFactory();
		$factory->setDatabaseAdapter($DIC->database());

		return $factory;
	}
}
