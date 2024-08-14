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

namespace ILIAS\User\Profile\ChangeListeners;

/**
 * Class InterestedUserFieldChangeListener
 * @author Marvin Beym <mbeym@databay.de>
 */
class InterestedUserFieldChangeListener
{
    /**
     * @var array<InterestedUserFieldAttribute> $attributes
     */
    private array $attributes = [];

    public function __construct(
        private readonly \ilLanguage $lng,
        private readonly string $name,
        private readonly string $field_name
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFieldName(): string
    {
        return $this->field_name;
    }

    /**
     * @return array<InterestedUserFieldAttribute>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function addAttribute(string $attribute_name): InterestedUserFieldAttribute
    {
        if (!isset($this->attributes[$attribute_name])) {
            $this->attributes[$attribute_name] = new InterestedUserFieldAttribute(
                $this->lng,
                $attribute_name,
                $this->field_name
            );
        }
        return $this->attributes[$attribute_name];
    }
}
