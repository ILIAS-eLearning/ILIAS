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
 
namespace ILIAS\BackgroundTasks\Implementation\Values;

use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;

//use ILIAS\BackgroundTasks\ValueType;
/**
 * Class ThunkValue
 * @package ILIAS\BackgroundTasks\Implementation\Values
 * Represents a value that has not yet been calculated.
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 */
class ThunkValue extends AbstractValue
{
    protected Type $type;
    
    public function getType() : Type
    {
        return $this->parentTask->getOutputType();
    }
    
    /**
     * String representation of object
     * @link  http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return '';
    }
    
    /**
     * Constructs the object
     * @link  http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     *                           The string representation of the object.
     *                           </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        // Nothing to do.
    }
    
    /**
     * @return string Gets a hash for this IO. If two objects are the same the hash must be the
     * same! if two objects are different you need to have as view collitions as
     * possible.
     */
    public function getHash() : string
    {
        return '';
    }
    
    public function equals(Value $other) : bool
    {
        return false;
    }
    
    /**
     * @param $value
     */
    public function setValue($value) : void
    {
        // TODO: Implement setValue() method.
    }
}
