<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificatePdfFilename implements ilCertificateFilename
{
    /** @var ilLanguage */
    private $lng;

    /**
     * ilCertificatePdfFileNameFactory constructor.
     * @param ilLanguage $lng
     */
    public function __construct(\ilLanguage $lng)
    {
        $this->lng = $lng;

        $this->lng->loadLanguageModule('certificate');
    }

    /**
     * @inheritDoc
     */
    public function createFileName(ilUserCertificatePresentation $presentation) : string
    {
        $basename = $this->lng->txt('certificate_file_basename');
        if (!is_string($basename) || 0 === trim($basename)) {
            $basename = 'Certificate';
        }

        return $basename . '.pdf';
    }
}
