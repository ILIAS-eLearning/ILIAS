<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateValueReplacement
{
    private string $clientWebDirectory;

    /**
     * @param string $clientWebDirectory   - Replacement for the placeholder [CLIENT_WEB_DIR], if the string is empty
     *                                     the constant CLIENT_WEB_DIR will be tried as default value.
     *                                     If CLIENT_WEB_DIR is not defined the default value will be an empty string.
     */
    public function __construct(string $clientWebDirectory = '')
    {
        if ('' === $clientWebDirectory && true === defined('CLIENT_WEB_DIR')) {
            $clientWebDirectory = CLIENT_WEB_DIR;
        }
        $this->clientWebDirectory = $clientWebDirectory;
    }

    public function replace(array $placeholderValues, string $certificateContent) : string
    {
        foreach ($placeholderValues as $placeholder => $value) {
            $certificateContent = str_replace('[' . $placeholder . ']', $value, $certificateContent);
        }

        return $certificateContent;
    }
}
