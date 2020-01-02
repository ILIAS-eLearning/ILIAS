<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTypeClassMap
{
    private $typeClassMap = array(
        'crs'  => array('placeholder' => ilCoursePlaceholderValues::class),
        'tst'  => array('placeholder' => ilTestPlaceholderValues::class),
        'exc'  => array('placeholder' => ilExercisePlaceholderValues::class),
        'sahs' => array('placeholder' => ilScormPlaceholderValues::class),
    );

    /**
     * @param string $type
     * @return string
     * @throws ilException
     */
    public function getPlaceHolderClassNameByType($type) : string
    {
        if (false === $this->typeExistsInMap($type)) {
            throw new ilException('The given type ' . $type . 'is not mapped as a class on the class map');
        }

        return $this->typeClassMap[$type]['placeholder'];
    }

    /**
     * @param string $type
     * @return bool
     */
    public function typeExistsInMap($type) : bool
    {
        return array_key_exists($type, $this->typeClassMap);
    }
}
