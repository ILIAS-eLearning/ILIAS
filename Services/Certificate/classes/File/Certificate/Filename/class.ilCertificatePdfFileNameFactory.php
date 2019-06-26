<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificatePdfFileNameFactory
{
    public function create(ilUserCertificatePresentation $presentation)
    {
        $objectType = $presentation->getUserCertificate()->getObjType();
        $pdfFileGenerator = $this->fetchCertificateGenerator($objectType);

        $fileName = $pdfFileGenerator->createFileName($presentation);

        return $fileName . '.pdf';
    }

    /**
     * @param $objectType
     * @return ilCertificatePdfFilename|ilCertificateScormPdfFilename
     */
    private function fetchCertificateGenerator(string $objectType)
    {
        if ($objectType === 'sahs') {
            return new ilCertificateScormPdfFilename(new ilSetting('scorm'));
        }

        return new ilCertificatePdfFilename();
    }
}
