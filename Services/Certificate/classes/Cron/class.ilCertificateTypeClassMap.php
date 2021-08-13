<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTypeClassMap
{
    private array $typeClassMap = [
        'crs' => ['placeholder' => ilCoursePlaceholderValues::class],
        'tst' => ['placeholder' => ilTestPlaceHolderValues::class],
        'exc' => ['placeholder' => ilExercisePlaceHolderValues::class],
        'cmix' => ['placeholder' => ilCmiXapiPlaceholderValues::class],
        'lti' => ['placeholder' => ilLTIConsumerPlaceholderValues::class],
        'sahs' => ['placeholder' => ilScormPlaceholderValues::class]
    ];

    /**
     * @param string $type
     * @return string
     * @throws ilException
     */
    public function getPlaceHolderClassNameByType(string $type) : string
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
    public function typeExistsInMap(string $type) : bool
    {
        return array_key_exists($type, $this->typeClassMap);
    }
}
