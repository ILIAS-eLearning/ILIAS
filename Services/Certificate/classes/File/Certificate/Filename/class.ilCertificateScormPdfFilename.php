<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateScormPdfFilename implements ilCertificateFilename
{
    public function __construct(
        private ilCertificateFilename $origin,
        private ilLanguage $lng,
        private ilSetting $scormSetting
    ) {
    }

    public function createFileName(ilUserCertificatePresentation $presentation): string
    {
        $fileName = $this->origin->createFileName($presentation);

        if (null === $presentation->getUserCertificate()) {
            $fileNameParts = implode('_', array_filter([
                $this->lng->txt('certificate_var_user_lastname'),
                $this->scormSetting->get('certificate_short_name_' . $presentation->getObjId(), ''),
            ]));
        } else {
            $short_name = $this->scormSetting->get('certificate_short_name_' . $presentation->getObjId(), '');
            $fileNameParts = implode('_', array_filter([
                $presentation->getUserName(),
                $short_name ?: $presentation->getObjectTitle(),
            ]));
        }

        return implode('_', array_filter([
            strftime('%y%m%d', time()),
            $fileNameParts,
            $fileName
        ]));
    }
}
