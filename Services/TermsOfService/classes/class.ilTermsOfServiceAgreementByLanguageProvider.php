<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/interfaces/interface.ilTermsOfServiceTableDataProvider.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceAgreementByLanguageProvider implements ilTermsOfServiceTableDataProvider
{
	/**
	 * @var ilLanguage|null
	 */
	protected $lng;

	/**
	 * @var array
	 */
	protected $data = array();

	/**
	 * @param array $params
	 * @param array $filter
	 */
	public function getList(array $params, array $filter)
	{
		$this->data = array(
			'items' => array(),
			'cnt'   => 0
		);

		$this->collectData();

		return $this->data;
	}

	/**
	 * @throws ilTermsOfServiceMissingLanguageAdapterException
	 */
	protected function collectData()
	{
		if(!($this->getLanguageAdapter() instanceof ilLanguage))
		{
			require_once 'Services/TermsOfService/exceptions/class.ilTermsOfServiceMissingLanguageAdapterException.php';
			throw new ilTermsOfServiceMissingLanguageAdapterException('Incomplete configuration. Please inject a language adapter.');
		}

		$i = 0;
		foreach($this->getLanguageAdapter()->getInstalledLanguages() as $lng)
		{
			$this->data['items'][$i]['language']                           = $lng;
			$this->data['items'][$i]['agreement']                          = false;
			$this->data['items'][$i]['agreement_document']                 = null;
			$this->data['items'][$i]['agreement_document_modification_ts'] = null;
			if(is_file('./Customizing/clients/' . CLIENT_ID . '/agreement/agreement_' . $lng . '.html'))
			{
				$this->data['items'][$i]['agreement_document']                 = './Customizing/clients/' . CLIENT_ID . '/agreement/agreement_' . $lng . '.html';
				$this->data['items'][$i]['agreement_document_modification_ts'] = filemtime('./Customizing/clients/' . CLIENT_ID . '/agreement/agreement_' . $lng . '.html');
				$this->data['items'][$i]['agreement']                          = true;
			}
			else if(is_file('./Customizing/global/agreement/agreement_' . $lng . '.html'))
			{
				$this->data['items'][$i]['agreement_document']                 = './Customizing/global/agreement/agreement_' . $lng . '.html';
				$this->data['items'][$i]['agreement_document_modification_ts'] = filemtime('./Customizing/global/agreement/agreement_' . $lng . '.html');
				$this->data['items'][$i]['agreement']                          = true;
			}

			++$i;
		}

		$this->data['cnt'] = $i;
	}

	/**
	 * @param ilLanguage|null $lng
	 */
	public function setLanguageAdapter($lng)
	{
		$this->lng = $lng;
	}

	/**
	 * @return ilLanguage|null
	 */
	public function getLanguageAdapter()
	{
		return $this->lng;
	}
}
