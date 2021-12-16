<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilMailBodyPurifier
{
    public function purify(string $content) : string
    {
        $sanitizedContent = ilUtil::stripSlashes($content);

        if ($sanitizedContent !== $content) {
            $sanitizedContent = ilUtil::stripSlashes(str_replace('<', '< ', $content));
        }
        $sanitizedContent = str_replace(chr(13), '', $sanitizedContent);

        return $sanitizedContent;
    }
}
