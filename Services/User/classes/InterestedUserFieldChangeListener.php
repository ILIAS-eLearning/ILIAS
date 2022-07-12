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

namespace ILIAS\Services\User;

/**
 * Class InterestedUserFieldChangeListener
 * @author Marvin Beym <mbeym@databay.de>
 */
class InterestedUserFieldChangeListener
{
    private string $name;
    private string $fieldName;
    /**
     * @var InterestedUserFieldAttribute[] $attributes
     */
    private array $attributes = [];

    public function __construct(string $name, string $fieldName)
    {
        $this->name = $name;
        $this->fieldName = $fieldName;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getFieldName() : string
    {
        return $this->fieldName;
    }

    /**
     * @return InterestedUserFieldAttribute[]
     */
    public function getAttributes() : array
    {
        return $this->attributes;
    }

    public function addAttribute(string $attributeName) : InterestedUserFieldAttribute
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute->getAttributeName() === $attributeName) {
                return $attribute;
            }
        }

        $attribute = new InterestedUserFieldAttribute($attributeName, $this->fieldName);
        $this->attributes[] = $attribute;

        return $attribute;
    }
}
