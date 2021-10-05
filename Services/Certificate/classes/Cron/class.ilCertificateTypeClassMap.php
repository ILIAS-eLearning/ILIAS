<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTypeClassMap
{
    /**
     * @var array<string, array{placeholder: string}>
     */
    private array $typeClassMap = [
        'crs' => ['placeholder' => ilCoursePlaceholderValues::class],
        'tst' => ['placeholder' => ilTestPlaceholderValues::class],
        'exc' => ['placeholder' => ilExercisePlaceholderValues::class],
        'cmix' => ['placeholder' => ilCmiXapiPlaceholderValues::class],
        'lti' => ['placeholder' => ilLTIConsumerPlaceholderValues::class],
        'sahs' => ['placeholder' => ilScormPlaceholderValues::class],
        'prg' => ['placeholder' => ilStudyProgrammePlaceholderValues::class]
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

    public function typeExistsInMap(string $type) : bool
    {
        return array_key_exists($type, $this->typeClassMap);
    }
}
