<?php
namespace ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate;

use JsonSerializable;

abstract class AbstractValueObject implements JsonSerializable {
    /**
     * Compares ValueObjects to each other returns true if they are the same
     * 
     * @param AbstractValueObject $other
     * @return bool
     */
    abstract function equals(AbstractValueObject $other) : bool;

    /**
     * Compares if two nullable ValueObjects are equal and returns true if they are
     * 
     * @param AbstractValueObject $first
     * @param AbstractValueObject $second
     * @return bool
     */
    public static function isNullableEqual(?AbstractValueObject $first, ?AbstractValueObject $second) : bool
    {
        if ($first === null) {
            if ($second === null) {
                return true; //TODO some theorists say null is not equals null but for our purposes it is equal
            } else {
                return false;
            }
        }
        
        if ($second === null) {
            return false;
        }
        
        return $first->equals($second);
    }
    
    /**
	 * Specify data which should be serialized to JSON
	 *
	 * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	abstract public function jsonSerialize();
}