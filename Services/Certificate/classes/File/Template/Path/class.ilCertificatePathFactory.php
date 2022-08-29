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
class ilCertificatePathFactory
{
    public function create(ilObject $object): string
    {
        $type = $object->getType();

        switch ($type) {
            case 'tst':
                $certificatePath = ilCertificatePathConstants::TEST_PATH . $object->getId() . '/';
                break;
            case 'crs':
                $certificatePath = ilCertificatePathConstants::COURSE_PATH . $object->getId() . '/';
                break;
            case 'sahs':
                $certificatePath = ilCertificatePathConstants::SCORM_PATH . $object->getId() . '/';
                break;
            case 'exc':
                $certificatePath = ilCertificatePathConstants::EXERCISE_PATH . $object->getId() . '/';
                break;
            case 'lti':
                $certificatePath = ilCertificatePathConstants::LTICON_PATH . $object->getId() . '/';
                break;
            case 'cmix':
                $certificatePath = ilCertificatePathConstants::CMIX_PATH . $object->getId() . '/';
                break;
            case 'prg':
                $certificatePath = ilCertificatePathConstants::STUDY_PROGRAMME_PATH . $object->getId() . '/';
                break;
            default:
                throw new ilException(sprintf(
                    'The type "%s" is currently not supported for certificates',
                    $type
                ));
        }

        return $certificatePath;
    }
}
