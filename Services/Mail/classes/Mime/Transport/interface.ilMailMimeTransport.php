<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilMailMimeTransport
 */
interface ilMailMimeTransport
{
	/**
	 * @param ilMimeMail $mail
	 */
	public function send(ilMimeMail $mail);
}