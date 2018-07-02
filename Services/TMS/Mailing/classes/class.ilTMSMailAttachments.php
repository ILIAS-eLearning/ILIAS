<?php

use ILIAS\TMS\Mailing;

/**
 * Class ilTMSMailContextAttachments
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class ilTMSMailAttachments implements Mailing\Attachments
{
	/**
	 * @var string
	 */
	protected $attachments = array();

	/**
	 * @inheritdoc
	 */
	public function getAttachments()
	{
		return $this->attachments;
	}

	/**
	 * @inheritdoc
	 */
	public function addAttachment(Mailing\Attachment $attachment)
	{
		if(!$this->hasAttachment($attachment)) {
			$this->attachments[] = $attachment;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function delAttachment(Mailing\Attachment $attachment)
	{
		$this->attachments = array_filter($this->attachments, function($a) use($attachment) {
			return $a->getAttachmentPath() !== $attachment->getAttachmentPath();
		});
	}

	/**
	 * Checks wheter an attachment already exists.
	 *
	 * @param 	Attachment
	 * @return 	bool
	 */
	protected function hasAttachment(Mailing\Attachment $attachment)
	{
		$exist = array_filter($this->attachments, function($a) use($attachment) {
			return $a->getAttachmentPath() === $attachment->getAttachmentPath();
		});
		if(empty($exist)) {
			return false;
		}
		return true;
	}
}