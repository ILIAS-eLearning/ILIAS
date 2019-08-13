<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificatePathFactory
{
    public function create(ilObject $object)
    {
        $type = $object->getType();

        switch ($type) {
            case 'tst':
                $certificatePath = ilCertificatePathConstants::TEST_PATH . $object->getId() . '/';
                break;
            case 'crs':
                $certificatePath = ilCertificatePathConstants::COURSE_PATH . $object->getId() . '/';
                break;
            case 'scrm':
                $certificatePath = ilCertificatePathConstants::SCORM_PATH . $object->getId() . '/';
                break;
            case 'sahs':
                $certificatePath = ilCertificatePathConstants::SCORM_PATH . $object->getId() . '/';
                break;
            case 'exc':
                $certificatePath = ilCertificatePathConstants::EXERCISE_PATH . $object->getId() . '/';
                break;
            default:
                throw new ilException(sprintf(
                    'The type "%s" is currently not supported for certificates',
                    $type
                ));
                break;
        }

        return $certificatePath;
    }
}
