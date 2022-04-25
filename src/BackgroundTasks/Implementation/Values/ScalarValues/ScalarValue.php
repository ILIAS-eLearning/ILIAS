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
 
namespace ILIAS\BackgroundTasks\Implementation\Values\ScalarValues;

use ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException;
use ILIAS\BackgroundTasks\Implementation\Values\AbstractValue;
use ILIAS\BackgroundTasks\Value;

class ScalarValue extends AbstractValue
{
    
    /**
     * @var mixed|bool|float|int|string|null is_scalar() == true;
     */
    protected $value;

    public function serialize() : string
    {
        return serialize($this);
    }

    public function unserialize($data) : void
    {
        /** @var self $unserialized */
        $unserialized = unserialize($data);

        $this->value = $unserialized->value;
    }
    
    public function __serialize() : array
    {
        return [
            'value' => $this->value
        ];
    }
    
    public function __unserialize(array $data) : void
    {
        $this->value = $data['value'];
    }
    
    /**
     * @return string Gets a hash for this IO. If two objects are the same the hash must be the
     *                same! if two objects are different you need to have as view collitions as
     *                possible.
     */
    public function getHash() : string
    {
        return md5($this->serialize());
    }
    
    public function equals(Value $other) : bool
    {
        if (!$other instanceof ScalarValue) {
            return false;
        }
        
        return $this->value == $other->getValue();
    }
    
    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * @param $value
     * @throws InvalidArgumentException
     */
    public function setValue($value) : void
    {
        if (!is_scalar($value)) {
            throw new InvalidArgumentException("The value given must be a scalar! See php-documentation is_scalar().");
        }
        
        $this->value = $value;
    }
}
