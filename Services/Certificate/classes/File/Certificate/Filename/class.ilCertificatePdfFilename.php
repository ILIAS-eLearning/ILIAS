<?php declare(strict_types=1);

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
