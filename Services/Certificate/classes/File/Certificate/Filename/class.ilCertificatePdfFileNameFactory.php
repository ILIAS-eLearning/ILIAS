<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificatePdfFileNameFactory
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
    }

    public function create(ilUserCertificatePresentation $presentation)
    {
        $objectType = $presentation->getObjType();
        $pdfFileGenerator = $this->fetchCertificateGenerator($objectType);

        return $pdfFileGenerator->createFileName($presentation);
    }

    /**
     * @param string $objectType
     * @return ilCertificateFilename
     */
    private function fetchCertificateGenerator(string $objectType) : ilCertificateFilename
    {
        $generator = new ilCertificatePdfFilename($this->lng);
        if ('sahs' === $objectType) {
            $generator = new ilCertificateScormPdfFilename($generator, $this->lng, new ilSetting('scorm'));
        }

        return $generator;
    }
}
