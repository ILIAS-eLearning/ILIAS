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

		if ($objectType === 'sahs') {
			$pdfFileGenerator = new ilCertificateScormPdfFilename(new ilSetting('scorm'));
		} else {
			$pdfFileGenerator = new ilCertificatePdfFilename();
		}

		$fileName = $pdfFileGenerator->createFileName($presentation);

		return $fileName . '.pdf';
	}
}
