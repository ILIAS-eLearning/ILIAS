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
	 * @param ilLanguage $lng
	 */
	public function __construct(ilLanguage $lng)
	{
		$this->lng = $lng;
	}

	/**
	 * @throws ilTermsOfServiceNoSignableDocumentFoundException
	 */
	public function init()
	{
		$files = array(
			'./Customizing/clients/' . CLIENT_ID . '/agreement/agreement_' . $this->lng->lang_key . '.html'     => $this->lng->lang_key,
			'./Customizing/clients/' . CLIENT_ID . '/agreement/agreement_' . $this->lng->lang_default . '.html' => $this->lng->lang_default,
			'./Customizing/clients/' . CLIENT_ID . '/agreement/agreement_en.html'                               => 'en',
			'./Customizing/global/agreement/agreement_' . $this->lng->lang_key . '.html'                        => $this->lng->lang_key,
			'./Customizing/global/agreement/agreement_' . $this->lng->lang_default . '.html'                    => $this->lng->lang_default,
			'./Customizing/global/agreement/agreement_en.html'                                                  => 'en'
		);

		foreach($files as $file => $iso2_language_code)
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
