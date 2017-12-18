<?php
/* Copyright (c) 2017 Daniel Weise <daniel.weise@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

/**
 * An attachment provides an filename.
 * It is used in the Clerk to get the mail attachment.
 */
interface Attachments
{
	/**
	 * Returns all attachments for specific mail.
	 *
	 * @return 	Attachment[]
	 */
	public function getAttachments();

	/**
	 * Add an attachment.
	 *
	 * @param 	Attachment
	 * @return 	void
	 */
	public function addAttachment(Attachment $attachment);

	/**
	 * Delete an attachment.
	 *
	 * @param 	Attachment
	 * @return 	void
	 */
	public function delAttachment(Attachment $attachment);
}
