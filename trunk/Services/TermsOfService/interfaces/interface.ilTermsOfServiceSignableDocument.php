<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
interface ilTermsOfServiceSignableDocument
{
	/**
	 * @var int
	 */
	const SRC_TYPE_FILE_SYSTEM_PATH = 0;

	/**
	 * @var int
	 */
	const SRC_TYPE_OBJECT = 1;

	/**
	 * @return bool
	 */
	public function hasContent();

	/**
	 * @return string
	 */
	public function getContent();

	/**
	 * @return mixed
	 */
	public function getSource();

	/**
	 * @return int
	 */
	public function getSourceType();

	/**
	 * @return string
	 */
	public function getIso2LanguageCode();

	/**
	 * Called from client to initiate the content determination
	 */
	public function determine();
}
