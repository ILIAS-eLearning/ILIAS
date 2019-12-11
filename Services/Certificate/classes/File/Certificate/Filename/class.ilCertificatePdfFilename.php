<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificatePdfFilename implements ilCertificateFilename
{
    public function createFileName(ilUserCertificatePresentation $presentation)
    {
        $pdfDownloadName = $presentation->getObjectTitle() . ' ' . $presentation->getUserName() . ' Certificate';

        return $pdfDownloadName;
    }
}
