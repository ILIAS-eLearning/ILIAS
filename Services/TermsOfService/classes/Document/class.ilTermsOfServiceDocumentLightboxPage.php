<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceDocumentLightboxPage
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentLightboxPage implements \ILIAS\UI\Component\Modal\LightboxPage
{
	/**
	 * @var string 
	 */
	protected $title = '';

	/**
	 * @var string 
	 */
	protected $text = '';

	/**
	 * ilTermsOfServiceDocumentLightboxPage constructor.
	 * @param string $title
	 * @param string $text
	 */
	public function __construct(string $title, string $text)
	{
		$this->title = $title;
		$this->text = $text;
	}

	/**
	 * @inheritdoc
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @inheritdoc
	 */
	public function getDescription()
	{
		return '';
	}

	/**
	 * @inheritdoc
	 */
	public function getComponent()
	{
		return new \ILIAS\UI\Implementation\Component\Legacy\Legacy($this->text);
	}
}