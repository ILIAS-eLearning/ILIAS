<?php

namespace ILIAS\AssessmentQuestion\CQRS\Aggregate;

use JsonSerializable;
use stdClass;

/**
 * Class AbstractValueObject
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class AbstractValueObject implements JsonSerializable {
	const VAR_CLASSNAME = "avo_class_name";

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
	public function jsonSerialize() {
		$vars = get_object_vars($this);
		$vars[self::VAR_CLASSNAME] = get_called_class();
		return $vars;
	}

	public static function deserialize(?string $data) : ?AbstractValueObject {
		if ($data === null) {
			return null;
		}

		$std_data = json_decode($data);

		if ($std_data === null) {
			return null;
		}

		/** @var AbstractValueObject $object */
		$object = new $std_data->{self::VAR_CLASSNAME}();
		$object->setFromStdClass($std_data);

		return $object;
	}

	private function setFromStdClass(StdClass $data) {
		foreach ($data as $property=>$value) {
			if ($value instanceof StdClass)
			{
				/** @var AbstractValueObject $object */
				$object = new $value->{self::VAR_CLASSNAME}();
				$object->setFromStdClass($value);
				$this->$property = $object;
			} else {
				$this->$property = $value;
			}
		}
	}
}