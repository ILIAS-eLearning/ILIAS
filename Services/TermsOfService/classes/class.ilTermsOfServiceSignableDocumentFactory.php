<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @abstract
 */
class ilTermsOfServiceSignableDocumentFactory
{
	/**
	 * @param ilLanguage $lng
	 * @return ilTermsOfServiceSignableDocument
	 * @throws ilTermsOfServiceNoSignableDocumentFoundException
	 */
	public static function getByLanguageObject(ilLanguage $lng)
	{
		require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceFileSystemDocument.php';
		$document = new ilTermsOfServiceFileSystemDocument($lng);
		$document->determine();
		return $document;
	}
}
