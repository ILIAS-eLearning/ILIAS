<?php

/* Copyright (c) 2017 Daniel Weise <daniel.weise@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

/**
 * An attachment provides an filename.
 * It is used in the Clerk to get the mail attachment.
 */
interface Attachment
{
	/**
	 * Returns the file path to an attachment.
	 *
	 * @return 	string
	 */
	public function getAttachmentPath();

	/**
	 * Sets the path of an attachment.
	 *
	 * @param 	string 	$value
	 * @return 	self
	 */
	public function withAttachmentPath($path);
}
