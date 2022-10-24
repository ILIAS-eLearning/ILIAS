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
    public function getPlaceHolderClassNameByType(string $type): string
    {
        if (false === $this->typeExistsInMap($type)) {
            throw new ilException('The given type ' . $type . 'is not mapped as a class on the class map');
        }

        return $this->typeClassMap[$type]['placeholder'];
    }

    public function typeExistsInMap(string $type): bool
    {
        return array_key_exists($type, $this->typeClassMap);
    }
}
