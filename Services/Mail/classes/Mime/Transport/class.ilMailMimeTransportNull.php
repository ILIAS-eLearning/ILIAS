<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Mail/classes/Mime/Transport/interface.ilMailMimeTransport.php';
require_once 'Services/Logging/classes/public/class.ilLoggerFactory.php';

/**
 * Class ilMailMimeTransportNull
 */
class ilMailMimeTransportNull implements ilMailMimeTransport
{
	/**
	 * @inheritdoc
	 */
	public function send(ilMimeMail $mail)
	{
		ilLoggerFactory::getLogger('mail')->debug(sprintf(
			'Suppressed delegation of external email delivery according to global setting.'
		));

		return true;
	}
}