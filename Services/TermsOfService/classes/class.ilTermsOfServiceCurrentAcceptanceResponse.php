<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/interfaces/interface.ilTermsOfServiceResponse.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceCurrentAcceptanceResponse implements ilTermsOfServiceResponse
{
	/**
	 * @var string
	 */
	protected $signed_text;

	/**
	 * @var string
	 */
	protected $language;

	/**
	 * @var string
	 */
	protected $path_to_file;

	/**
	 * @var boolean
	 */
	protected $has_current_acceptance = false;

	/**
	 * @param string $language
	 */
	public function setLanguage($language)
	{
		$this->language = $language;
	}

	/**
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * @param string $path_to_file
	 */
	public function setPathToFile($path_to_file)
	{
		$this->path_to_file = $path_to_file;
	}

	/**
	 * @return string
	 */
	public function getPathToFile()
	{
		return $this->path_to_file;
	}

	/**
	 * @param string $signed_text
	 */
	public function setSignedText($signed_text)
	{
		$this->signed_text = $signed_text;
	}

	/**
	 * @return string
	 */
	public function getSignedText()
	{
		return $this->signed_text;
	}

	/**
	 * @param boolean $has_current_acceptance
	 */
	public function setHasCurrentAcceptance($has_current_acceptance)
	{
		$this->has_current_acceptance = $has_current_acceptance;
	}

	/**
	 * @return boolean
	 */
	public function getHasCurrentAcceptance()
	{
		return $this->has_current_acceptance;
	}
}
