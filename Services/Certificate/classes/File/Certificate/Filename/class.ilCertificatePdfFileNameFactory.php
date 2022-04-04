<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificatePdfFileNameFactory
{
    private ilLanguage $lng;

    public function __construct(ilLanguage $lng)
    {
        $this->lng = $lng;
    }

    public function create(ilUserCertificatePresentation $presentation) : string
    {
        $objectType = $presentation->getObjType();

        return $this->fetchCertificateGenerator($objectType)->createFileName($presentation);
    }

    private function fetchCertificateGenerator(string $objectType) : ilCertificateFilename
    {
        $generator = new ilCertificatePdfFilename($this->lng);
        if ('sahs' === $objectType) {
            $generator = new ilCertificateScormPdfFilename($generator, $this->lng, new ilSetting('scorm'));
        }

        return $generator;
    }
}
