<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateScormPdfFilename implements ilCertificateFilename
{
    /**
     * @var ilSetting
     */
    private $scormSetting;

    /**
     * @param ilSetting $scormSetting
     */
    public function __construct(ilSetting $scormSetting)
    {
        $this->scormSetting = $scormSetting;
    }

    public function createFileName(ilUserCertificatePresentation $presentation)
    {
        $short_title = $this->scormSetting->get('certificate_short_name_' . $presentation->getUserCertificate()->getObjId());

        $pdfDownloadName = strftime('%y%m%d', time()) . '_' . $presentation->getUserName() . '_' . $short_title . '_certificate';

        return $pdfDownloadName;
    }
}
