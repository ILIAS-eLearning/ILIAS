<?php

use ILIAS\TMS\Mailing;

/**
 * Class ilTMSMailContextAttachment
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class ilTMSMailAttachment implements Mailing\Attachment
{
	/**
	 * @var string
	 */
	protected $attachment_path;

	/**
	 * @inheritdoc
	 */
	public function getAttachmentPath()
	{
		return $this->attachment_path;
	}

	/**
	 * @inheritdoc
	 */
	public function withAttachmentPath($path)
	{
		assert('is_string($path)');
		$clone = clone $this;
		$clone->attachment_path = $path;
		return $clone;
	}
}