<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateValueReplacement
{
    public function replace(array $placeholderValues, string $certificateContent) : string
    {
        foreach ($placeholderValues as $placeholder => $value) {
            $certificateContent = str_replace('[' . $placeholder . ']', $value, $certificateContent);
        }

        return $certificateContent;
    }
}
