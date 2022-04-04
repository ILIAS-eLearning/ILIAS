<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificatePdfFilename implements ilCertificateFilename
{
    private ilLanguage $lng;

    public function __construct(ilLanguage $lng)
    {
        $this->lng = $lng;

        $this->lng->loadLanguageModule('certificate');
    }

    public function createFileName(ilUserCertificatePresentation $presentation) : string
    {
        $basename = $this->lng->txt('certificate_file_basename');
        if ('' === trim($basename)) {
            $basename = 'Certificate';
        }

        return $basename . '.pdf';
    }
}
