<?php
declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\KindlyTo;

use ILIAS\Refinery\KindlyTo\Transformation\BooleanTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\DictionaryTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\FloatTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\IntegerTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\ListTransformation;
use ILIAS\Refinery\To\Transformation\NewMethodTransformation;
use ILIAS\Refinery\To\Transformation\NewObjectTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\RecordTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\StringTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\TupleTransformation;
use ILIAS\Refinery\Transformation\Transformation;
use ILIAS\Refinery\Validation\Constraints\IsArrayOfSameType;

class Group
{
	/**
	 * Returns an object that allows to transform a value
	 * to a string value
	 * @return StringTransformation
	 */
	public function string(): StringTransformation
	{
		return new StringTransformation();
	}

	/**
	 * Returns an object that allows to transform a value
	 * to an integer value
	 * @return IntegerTransformation
	 */
	public function int(): IntegerTransformation
	{
		return new IntegerTransformation();
	}

	/**
	 * Returns an object that allows to transform a value
	 * to a float value
	 * @return FloatTransformation
	 */
	public function float(): FloatTransformation
	{
		return new FloatTransformation();
	}

	/**
	 * Returns an object that allows to transform a value
	 * to a boolean value
	 * @return BooleanTransformation
	 */
	public function bool(): BooleanTransformation
	{
		return new BooleanTransformation();
	}

	/**
	 * Returns an object that allows to transform an value in a given array
	 * with the given transformation object.
	 * The transformation will be executed on every element of the array.
	 *
	 * Using `ILIAS\Refinery\Factory::to()` will check if the value is identical
	 * to the value after the transformation.
	 *
	 * @param Transformation $transformation
	 * @return Transformation
	 */
	public function listOf(Transformation $transformation): Transformation
	{
		return new ListTransformation($transformation);
	}

	/**
	 * Returns an object that allows to transform the values of an associative array
	 * with the given transformation object.
	 * The keys of an associative array MUST be of type `String`
	 *
	 * Using `ILIAS\Refinery\Factory::to()` will check if the value is identical
	 * to the value after the transformation.
	 *
	 * @param Transformation $transformation
	 * @return Transformation
	 */
	public function dictOf(Transformation $transformation): Transformation
	{
		return new DictionaryTransformation($transformation);
	}

	/**
	 * Returns an object that allows to transform the values of an array
	 * with the given array of transformations objects.
	 * The length of the array of transformations MUST be identical to the
	 * array of values to transform.
	 * The keys of the transformation array will be the same as the key
	 * from the value array e.g. Transformation on position 2 will transform
	 * value on position 2 of the value array.
	 *
	 * Using `ILIAS\Refinery\Factory::to()` will check if the value is identical
	 * to the value after the transformation.
	 *
	 * @param array $transformation
	 * @return Transformation
	 */
	public function tupleOf(array $transformation): Transformation
	{
		return new TupleTransformation($transformation);
	}

	/**
	 *
	 * Returns an object that allows to transform the values of an
	 * associative array with the given associative array of
	 * transformations objects.
	 * The length of the array of transformations MUST be identical to the
	 * array of values to transform.
	 * The keys of the transformation array will be the same as the key
	 * from the value array e.g. Transformation with the key "hello" will transform
	 * value with the key "hello" of the value array.
	 *
	 * Using `ILIAS\Refinery\Factory::to()` will check if the value is identical
	 * to the value after the transformation.
	 *
	 * @param array $transformations
	 * @return Transformation
	 * @throws \ilException
	 */
	public function recordOf(array $transformations): Transformation
	{
		return new RecordTransformation($transformations);
	}

	/**
	 * Returns either an transformation object to create objects of an
	 * existing class, with variations of constructor parameters OR returns
	 * an transformation object to execute a certain method with variation of
	 * parameters on the objects.
	 *
	 * @param $classNameOrArray
	 * @return Transformation
	 * @throws \ilException
	 */
	public function toNew($classNameOrArray): Transformation
	{
		if (is_array($classNameOrArray)) {
			if (2 !== count($classNameOrArray)) {
				throw new \InvalidArgumentException('The array MUST contain exactly two elements');
			}
			return new NewMethodTransformation($classNameOrArray[0], $classNameOrArray[1]);
		}
		return new NewObjectTransformation($classNameOrArray);
	}

	/**
	 * Returns a data factory to create a certain data type
	 * @return \ILIAS\Data\Factory
	 */
	public function data(): \ILIAS\Data\Factory
	{
		return new \ILIAS\Data\Factory();
	}
}
