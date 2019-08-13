<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateFactory
{
    /**
     * @var ilCertificatePathFactory
     */
    private $pathFactory;

    public function __construct(ilCertificatePathFactory $pathFactory)
    {
        $this->pathFactory = $pathFactory;
    }

    /**
     * @param ilObject $object
     * @return ilCertificate
     * @throws ilException
     */
    public function create(ilObject $object) : ilCertificate
    {
        $type = $object->getType();

        switch ($type) {
            case 'tst':
                $adapter = new ilTestCertificateAdapter($object);
                $placeholderDescriptionObject = new ilTestPlaceholderDescription();
                $placeholderValuesObject = new ilTestPlaceHolderValues();
                break;
            case 'crs':
                $adapter = new ilCourseCertificateAdapter($object);
                $placeholderDescriptionObject = new ilCoursePlaceholderDescription();
                $placeholderValuesObject = new ilCoursePlaceholderValues();
                break;
            case 'scrm':
                $adapter = new ilSCORMCertificateAdapter($object);
                $placeholderDescriptionObject = new ilScormPlaceholderDescription($object);
                $placeholderValuesObject = new ilScormPlaceholderValues();
                break;
            case 'exc':
                $adapter = new ilExerciseCertificateAdapter($object);
                $placeholderDescriptionObject = new ilExercisePlaceholderDescription();
                $placeholderValuesObject = new ilExercisePlaceHolderValues();
                break;
            default:
                throw new ilException(sprintf(
                    'The type "%s" is currently not supported for certificates',
                    $type
                ));
                break;
        }

        $certificatePath = $this->pathFactory->create($object);

        $certificate = new ilCertificate(
            $adapter,
            $placeholderDescriptionObject,
            $placeholderValuesObject,
            $object->getId(),
            $certificatePath
        );

        return $certificate;
    }
}
