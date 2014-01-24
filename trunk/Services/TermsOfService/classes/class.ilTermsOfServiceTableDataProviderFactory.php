<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceTableDataProviderFactory
{
	/**
	 * @var ilLanguage|null
	 */
	protected $lng;

	/**
	 * @var ilDB|null
	 */
	protected $db;

	/**
	 * @var string
	 */
	const CONTEXT_AGRREMENT_BY_LANGUAGE = 'agreements_by_language';

	/**
	 * @var string
	 */
	const CONTEXT_ACCEPTANCE_HISTORY = 'acceptance_history';

	/**
	 * @param string $context
	 * @return ilTermsOfServiceAgreementByLanguageProvider|ilTermsOfServiceAgreementByLanguageProvider|ilTermsOfServiceAcceptanceHistoryProvider
	 * @throws ilTermsOfServiceMissingLanguageAdapterException
	 * @throws InvalidArgumentException
	 */
	public function getByContext($context)
	{
		switch($context)
		{
			case self::CONTEXT_AGRREMENT_BY_LANGUAGE:
				$this->validateConfiguration(array('lng'));
				require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceAgreementByLanguageProvider.php';
				return new ilTermsOfServiceAgreementByLanguageProvider($this->lng);

			case self::CONTEXT_ACCEPTANCE_HISTORY:
				$this->validateConfiguration(array('db'));
				require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceAcceptanceHistoryProvider.php';
				return new ilTermsOfServiceAcceptanceHistoryProvider($this->getDatabaseAdapter());

			default:
				throw new InvalidArgumentException('Provider not supported');
		}
	}

	/**
	 * @param array $mandatory_members
	 * @throws ilTermsOfServiceMissingLanguageAdapterException
	 * @throws ilTermsOfServiceMissingDatabaseAdapterException
	 */
	protected function validateConfiguration(array $mandatory_members)
	{
		foreach($mandatory_members as $member)
		{
			if(null == $this->{$member})
			{
				$exception = $this->getExceptionByMember($member);
				throw $exception;
			}
		}
	}

	/**
	 * @param string $member
	 * @return ilTermsOfServiceMissingDatabaseAdapterException|ilTermsOfServiceMissingLanguageAdapterException
	 * @throws InvalidArgumentException
	 */
	protected function getExceptionByMember($member)
	{
		switch($member)
		{
			case 'lng':
				require_once 'Services/TermsOfService/exceptions/class.ilTermsOfServiceMissingLanguageAdapterException.php';
				return new ilTermsOfServiceMissingLanguageAdapterException('Incomplete factory configuration. Please inject a language adapter.');

			case 'db':
				require_once 'Services/TermsOfService/exceptions/class.ilTermsOfServiceMissingDatabaseAdapterException.php';
				return new ilTermsOfServiceMissingDatabaseAdapterException('Incomplete factory configuration. Please inject a database adapter.');

			default:
				throw new InvalidArgumentException("Exveption for member {$member} not supported");
		}
	}

	/**
	 * @param ilLanguage|null $lng
	 */
	public function setLanguageAdapter($lng)
	{
		$this->lng = $lng;
	}

	/**
	 * @return ilLanguage
	 */
	public function getLanguageAdapter()
	{
		return $this->lng;
	}

	/**
	 * @param ilDB|null $db
	 */
	public function setDatabaseAdapter($db)
	{
		$this->db = $db;
	}

	/**
	 * @return ilDB|null
	 */
	public function getDatabaseAdapter()
	{
		return $this->db;
	}
}
