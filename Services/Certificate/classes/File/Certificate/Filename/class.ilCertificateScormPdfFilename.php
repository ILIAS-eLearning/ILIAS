<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateScormPdfFilename implements ilCertificateFilename
{
    private ilSetting $scormSetting;
    private ilCertificateFilename $origin;
    private ilLanguage $lng;

    public function __construct(ilCertificateFilename $origin, ilLanguage $lng, ilSetting $scormSetting)
    {
        $this->scormSetting = $scormSetting;
        $this->origin = $origin;
        $this->lng = $lng;
    }

    public function createFileName(ilUserCertificatePresentation $presentation) : string
    {
        $fileName = $this->origin->createFileName($presentation);

        if (null === $presentation->getUserCertificate()) {
            $fileNameParts = implode('_', array_filter([
                $this->lng->txt('certificate_var_user_lastname'),
                $this->scormSetting->get('certificate_short_name_' . $presentation->getObjId()),
            ]));
        } else {
            $fileNameParts = implode('_', array_filter([
                $presentation->getUserName(),
                $presentation->getObjectTitle(),
            ]));
        }

        $fileName = implode('_', array_filter([
            strftime('%y%m%d', time()),
            $fileNameParts,
            $fileName
        ]));

        return $fileName;
    }
}
