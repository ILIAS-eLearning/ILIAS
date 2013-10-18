<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceHelper
{
	/**
	 * @return bool
	 */
	public static function isEnabled()
	{
		/**
		 * @var $ilSetting ilSetting
		 */
		global $ilSetting;

		return (bool)$ilSetting->get('tos_status', 0);
	}

	/**
	 * @param bool $status
	 */
	public static function setStatus($status)
	{
		/**
		 * @var $ilSetting ilSetting
		 */
		global $ilSetting;

		$ilSetting->set('tos_status', (int)$status);
	}

	/**
	 * @param int $usr_id
	 */
	public static function deleteAcceptanceHistoryByUser($usr_id)
	{
		$entity       = self::getEntityFactory()->getByName('ilTermsOfServiceAcceptanceEntity');
		$data_gateway = self::getDataGatewayFactory()->getByName('ilTermsOfServiceAcceptanceDatabaseGateway');
		$entity->setUserId($usr_id);
		$data_gateway->deleteAcceptanceHistoryByUser($entity);
	}

	/**
	 * @param ilObjUser $user
	 * @return ilTermsOfServiceAcceptanceEntity
	 */
	public static function getCurrentAcceptanceForUser(ilObjUser $user)
	{
		$entity       = self::getEntityFactory()->getByName('ilTermsOfServiceAcceptanceEntity');
		$data_gateway = self::getDataGatewayFactory()->getByName('ilTermsOfServiceAcceptanceDatabaseGateway');
		$entity->setUserId($user->getId());
		return $data_gateway->loadCurrentAcceptanceOfUser($entity);
	}

	/**
	 * @param integer $id
	 * @return ilTermsOfServiceAcceptanceEntity
	 */
	public static function getById($id)
	{
		$entity       = self::getEntityFactory()->getByName('ilTermsOfServiceAcceptanceEntity');
		$data_gateway = self::getDataGatewayFactory()->getByName('ilTermsOfServiceAcceptanceDatabaseGateway');
		$entity->setId($id);
		return $data_gateway->loadById($entity);
	}

	/**
	 * @param ilObjUser                        $user
	 * @param ilTermsOfServiceSignableDocument $document
	 */
	public static function trackAcceptance(ilObjUser $user, ilTermsOfServiceSignableDocument $document)
	{
		if(self::isEnabled())
		{
			$entity       = self::getEntityFactory()->getByName('ilTermsOfServiceAcceptanceEntity');
			$data_gateway = self::getDataGatewayFactory()->getByName('ilTermsOfServiceAcceptanceDatabaseGateway');
			$entity->setUserId($user->getId());
			$entity->setTimestamp(time());
			$entity->setIso2LanguageCode($document->getIso2LanguageCode());
			$entity->setSource($document->getSource());
			$entity->setSourceType($document->getSourceType());
			$entity->setText($document->getContent());
			$entity->setHash(md5($document->getContent()));
			$data_gateway->trackAcceptance($entity);

			$user->writeAccepted(); // <- Has to be refactored in future releases

			$user->hasToAcceptTermsOfServiceInSession(false);
		}
	}

	/**
	 * @return ilTermsOfServiceEntityFactory
	 */
	private static function getEntityFactory()
	{
		require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceEntityFactory.php';
		return new ilTermsOfServiceEntityFactory();
	}

	/**
	 * @return ilTermsOfServiceDataGatewayFactory
	 */
	private static function getDataGatewayFactory()
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceDataGatewayFactory.php';
		$factory = new ilTermsOfServiceDataGatewayFactory();
		$factory->setDatabaseAdapter($ilDB);
		return $factory;
	}
}
