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
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var array
	 */
	protected $data = array();

	/**
	 * @var array
	 */
	protected $source_directories = array();

	/**
	 * @param ilLanguage $lng
	 */
	public function __construct(ilLanguage $lng)
	{
		$this->setLanguageAdapter($lng);
		$this->initSourceDirectories();
	}

	/**
	 * @param array $terms_of_service_source_directories
	 */
	public function setSourceDirectories($terms_of_service_source_directories)
	{
		$this->source_directories = $terms_of_service_source_directories;
	}

	/**
	 * @return array
	 */
	public function getSourceDirectories()
	{
		return $this->source_directories;
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

	/**
	 *
	 */
	protected function initSourceDirectories()
	{
		$this->source_directories = array(
			implode('/', array('.', 'Customizing', 'clients', CLIENT_ID, 'agreement')),
			implode('/', array('.', 'Customizing', 'global', 'agreement'))
		);
	}

	/**
	 * {@inheritdoc}
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
	 *
	 */
	protected function collectData()
	{
		$i = 0;
		foreach($this->getLanguageAdapter()->getInstalledLanguages() as $iso2_language_code)
		{
			$this->data['items'][$i]['language']                           = $iso2_language_code;
			$this->data['items'][$i]['agreement']                          = false;
			$this->data['items'][$i]['agreement_document']                 = null;
			$this->data['items'][$i]['agreement_document_modification_ts'] = null;

			foreach($this->getSourceDirectories() as $directory)
			{
				$file = $directory . '/agreement_' . $iso2_language_code . '.html';
				if(is_file($file) && is_readable($file))
				{
					$this->data['items'][$i]['agreement_document']                 = $file;
					$this->data['items'][$i]['agreement_document_modification_ts'] = filemtime($file);
					$this->data['items'][$i]['agreement']                          = true;
					break;
				}
			}

			++$i;
		}

		$this->data['cnt'] = $i;
	}
}
