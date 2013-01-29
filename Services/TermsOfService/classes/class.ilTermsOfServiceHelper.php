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
	 * @param ilObjUser $user
	 * @return ilTermsOfServiceCurrentAcceptanceResponse
	 */
	public static function getCurrentAcceptanceForUser(ilObjUser $user)
	{
		$interactor = self::getInteractorFactory()->getByName('ilTermsOfServiceCurrentAcceptanceInteractor');
		$request    = self::getRequestFactory()->getByName('ilTermsOfServiceCurrentAcceptanceRequest');
		self::setFactoriesToRequest($request);
		$request->setUserId($user->getId());
		return $interactor->invoke($request);
	}

	/**
	 * @param ilObjUser $user
	 * @param string    $agreement_file
	 */
	public static function trackAcceptance(ilObjUser $user, $agreement_file)
	{
		$user->writeAccepted();

		$user->hasToAcceptTermsOfServiceInSession(false);

		$interactor = self::getInteractorFactory()->getByName('ilTermsOfServiceAcceptanceInteractor');
		$request    = self::getRequestFactory()->getByName('ilTermsOfServiceAcceptanceRequest');
		self::setFactoriesToRequest($request);
		$request->setUserId($user->getId());
		$request->setTimestamp(time());
		$request->setPathToFile($agreement_file);
		$interactor->invoke($request);
	}

	/**
	 * @return ilTermsOfServiceInteractorFactory
	 */
	private static function getInteractorFactory()
	{
		require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceInteractorFactory.php';
		return new ilTermsOfServiceInteractorFactory();
	}

	/**
	 * @return ilTermsOfServiceRequestFactory
	 */
	private static function getRequestFactory()
	{
		require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceRequestFactory.php';
		return new ilTermsOfServiceRequestFactory();
	}

	/**
	 * @return ilTermsOfServiceEntityFactory
	 */
	private static function getEntityFactory()
	{
		require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceEntityFactory.php';
		$entity_factory = new ilTermsOfServiceEntityFactory();
		$entity_factory->setDataGatewayFactory(self::getDataGatewayFactory());
		return $entity_factory;
	}

	/**
	 * @return ilTermsOfServiceDataGatewayFactory
	 */
	private static function getDataGatewayFactory()
	{
		require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceDataGatewayFactory.php';
		return new ilTermsOfServiceDataGatewayFactory();
	}

	/**
	 * @return ilTermsOfServiceResponseFactory
	 */
	private static function getResponseFactory()
	{
		require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceResponseFactory.php';
		return new ilTermsOfServiceResponseFactory();
	}

	/**
	 * @param ilTermsOfServiceRequest $request
	 */
	private static function setFactoriesToRequest(ilTermsOfServiceRequest $request)
	{
		$request->setDataGatewayFactory(self::getDataGatewayFactory());
		$request->setEntityFactory(self::getEntityFactory());
		$request->setInteractorFactory(self::getInteractorFactory());
		$request->setResponseFactory(self::getResponseFactory());
	}
}
