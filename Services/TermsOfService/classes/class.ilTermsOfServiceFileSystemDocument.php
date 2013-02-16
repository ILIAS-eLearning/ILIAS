<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/interfaces/interface.ilTermsOfServiceSignableDocument.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceFileSystemDocument implements ilTermsOfServiceSignableDocument
{
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var bool
	 */
	protected $has_content = false;

	/**
	 * @var string
	 */
	protected $content = '';

	/**
	 * @var string
	 */
	protected $iso2_language_code = '';

	/**
	 * @var string
	 */
	protected $source = '';

	/**
	 * @var array
	 */
	protected $source_files = array();

	/**
	 * @param ilLanguage $lng
	 */
	public function __construct(ilLanguage $lng)
	{
		$this->setLanguageAdapter($lng);
		$this->initSourceFiles();
	}

	/**
	 * @throws ilTermsOfServiceNoSignableDocumentFoundException
	 */
	public function determine()
	{
		foreach($this->getSourceFiles() as $file => $iso2_language_code)
		{
			if(is_file($file) && is_readable($file))
			{
				$lines         = file($file);
				$this->content = '';
				foreach($lines as $line)
				{
					$this->content .= trim(nl2br($line));
				}
				$this->source             = $file;
				$this->has_content        = (bool)strlen($this->content);
				$this->iso2_language_code = $iso2_language_code;
				return;
			}
		}

		require_once 'Services/TermsOfService/exceptions/class.ilTermsOfServiceNoSignableDocumentFoundException.php';
		throw new ilTermsOfServiceNoSignableDocumentFoundException('Could not find any terms of service document for the passed language object');
	}

	/**
	 *
	 */
	protected function initSourceFiles()
	{
		$this->source_files = array(
			implode('/', array('.', 'Customizing', 'clients', CLIENT_ID, 'agreement', 'agreement_' . $this->getLanguageAdapter()->getLangKey() . '.html'))         => $this->getLanguageAdapter()->getLangKey(),
			implode('/', array('.', 'Customizing', 'clients', CLIENT_ID, 'agreement', 'agreement_' . $this->getLanguageAdapter()->getDefaultLanguage() . '.html')) => $this->getLanguageAdapter()->getDefaultLanguage(),
			implode('/', array('.', 'Customizing', 'clients', CLIENT_ID, 'agreement', 'agreement_en.html'))                                                        => 'en',
			implode('/', array('.', 'Customizing', 'global', 'agreement', 'agreement_' . $this->getLanguageAdapter()->getLangKey() . '.html'))                     => $this->getLanguageAdapter()->getLangKey(),
			implode('/', array('.', 'Customizing', 'global', 'agreement', 'agreement_' . $this->getLanguageAdapter()->getDefaultLanguage() . '.html'))             => $this->getLanguageAdapter()->getDefaultLanguage(),
			implode('/', array('.', 'Customizing', 'global', 'agreement', 'agreement_en.html'))                                                                    => 'en'
		);
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
	 * @param array $source_directories
	 */
	public function setSourceFiles($source_directories)
	{
		$this->source_files = $source_directories;
	}

	/**
	 * @return array
	 */
	public function getSourceFiles()
	{
		return $this->source_files;
	}

	/**
	 * @return bool
	 */
	public function hasContent()
	{
		return $this->has_content;
	}

	/**
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * @return mixed
	 */
	public function getSource()
	{
		return $this->source;
	}

	/**
	 * @return int
	 */
	public function getSourceType()
	{
		return self::SRC_TYPE_FILE_SYSTEM_PATH;
	}

	/**
	 * @return string
	 */
	public function getIso2LanguageCode()
	{
		return $this->iso2_language_code;
	}
}
