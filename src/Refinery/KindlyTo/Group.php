<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\KindlyTo;


use ILIAS\Refinery\KindlyTo\Transformation\BooleanTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\FloatTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\IntegerTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\StringTransformation;
use ILIAS\Refinery\Transformation\Transformation;

interface Group
{
	/**
	 * Returns an object that allows to transform a value
	 * to a string value
	 *
	 * @return Transformation
	 */
	public function string() : StringTransformation;

	/**
	 * Returns an object that allows to transform a value
	 * to an integer value
	 *
	 * @return Transformation
	 */
	public function int() : IntegerTransformation;

	/**
	 * Returns an object that allows to transform a value
	 * to a float value
	 *
	 * @return Transformation
	 */
	public function float() : FloatTransformation;

	/**
	 * Returns an object that allows to transform a value
	 * to a boolean value
	 *
	 * @return Transformation
	 */
	public function bool() : BooleanTransformation;

	/**
	 * Returns an object that allows to transform an value in a given array
	 * with the given transformation object.
	 * The transformation will be executed on every element of the array.
	 *
	 * @param Transformation $transformation
	 * @return Transformation
	 */
	public function listOf(Transformation $transformation) : Transformation;

	/**
	 * Returns an object that allows to transform the values of an associative array
	 * with the given transformation object.
	 * The keys of an associative array MUST be of type `String`
	 *
	 * @param Transformation $transformation
	 * @return Transformation
	 */
	public function dictOf(Transformation $transformation) : Transformation;

	/**
	 * Returns an object that allows to transform the values of an array
	 * with the given array of transformations objects.
	 * The length of the array of transformations MUST be identical to the
	 * array of values to transform.
	 * The keys of the transformation array will be the same as the key
	 * from the value array e.g. Transformation on position 2 will transform
	 * value on position 2 of the value array.
	 *
	 * @param array $transformation
	 * @return Transformation
	 */
	public function tupleOf(array $transformation)  : Transformation;

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
	 * @param array $transformations
	 * @return Transformation
	 */
	public function recordOf(array $transformations) : Transformation;

	/**
	 * Returns either an transformation object to create objects of an
	 * existing class, with variations of constructor parameters OR returns
	 * an transformation object to execute a certain method with variation of
	 * parameters on the objects.
	 *
	 * @param string|array $className
	 * @return Transformation
	 */
	public function toNew($className)  : Transformation;

	/**
	 * Returns a data factory to create a certain data type
	 * @return \ILIAS\Data\Factory
	 */
	public function data() : \ILIAS\Data\Factory;
}
