<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

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

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return string
     */
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

    /**
     * @param string $attributeName
     * @return InterestedUserFieldAttribute
     */
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
