<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilMailBodyPurifier
{
	public function purify(string $content)
	{
		$sanitizedContent = \ilUtil::stripSlashes($content);

		if ($sanitizedContent !== $content) {
			$sanitizedContent = \ilUtil::stripSlashes(str_replace('<', '< ', $content));
		}
		$sanitizedContent = str_replace("\r", '', $sanitizedContent);

		return $sanitizedContent;
	}
}
