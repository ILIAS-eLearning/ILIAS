<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceTableDataProviderFactory
{
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var string
	 */
	const CONTEXT_AGRREMENT_BY_LANGUAGE = 'agreements_by_language';

	/**
	 * @param $context
	 * @return ilTermsOfServiceAgreementByLanguageProvider
	 * @throws ilTermsOfServiceMissingLanguageAdapterException
	 * @throws InvalidArgumentException
	 */
	public function getByContext($context)
	{
		if(null === $this->lng)
		{
			require_once 'Services/TermsOfService/exceptions/class.ilTermsOfServiceMissingLanguageAdapterException.php';
			throw new ilTermsOfServiceMissingLanguageAdapterException('Incomplete factory configuration. Please inject a language adapter.');
		}
		
		switch($context)
		{
			case self::CONTEXT_AGRREMENT_BY_LANGUAGE:
				require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceAgreementByLanguageProvider.php';
				$provider = new ilTermsOfServiceAgreementByLanguageProvider();
				$provider->setLanguageAdapter($this->lng);
				return $provider;

			default:
				throw new InvalidArgumentException('Provider not supported');
		}
	}

	/**
	 * @param ilLanguage $lng
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
}
