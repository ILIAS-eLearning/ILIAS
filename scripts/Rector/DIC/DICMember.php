<?php

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

declare(strict_types=1);

namespace ILIAS\scripts\Rector\DIC;

class DICMember
{
    protected array $alternative_classes = [];

    /**
     * @param mixed[] $dic_service_method
     */
    public function __construct(
        protected string $name,
        protected string $main_class,
        protected array $dic_service_method,
        protected string $property_name
    ) {
    }

    /**
     * @param mixed[] $alternative_classes
     */
    public function setAlternativeClasses(array $alternative_classes): void
    {
        $this->alternative_classes = $alternative_classes;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMainClass(): string
    {
        return $this->main_class;
    }

    /**
     * @return mixed[]
     */
    public function getAlternativeClasses(): array
    {
        return $this->alternative_classes;
    }

    /**
     * @return mixed[]
     */
    public function getDicServiceMethod(): array
    {
        return $this->dic_service_method;
    }

    public function getPropertyName(): string
    {
        return $this->property_name;
    }
}
