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

namespace ILIAS\Refinery\To;

use ILIAS\Refinery\To\Transformation\BooleanTransformation;
use ILIAS\Refinery\To\Transformation\DictionaryTransformation;
use ILIAS\Refinery\To\Transformation\FloatTransformation;
use ILIAS\Refinery\To\Transformation\IntegerTransformation;
use ILIAS\Refinery\To\Transformation\ListTransformation;
use ILIAS\Refinery\To\Transformation\NewMethodTransformation;
use ILIAS\Refinery\To\Transformation\NewObjectTransformation;
use ILIAS\Refinery\To\Transformation\RecordTransformation;
use ILIAS\Refinery\To\Transformation\StringTransformation;
use ILIAS\Refinery\To\Transformation\TupleTransformation;
use ILIAS\Refinery\To\Transformation\DateTimeTransformation;
use ILIAS\Refinery\Transformation;
use ILIAS\Data\Factory;
use InvalidArgumentException;

class Group
{
    private Factory $dataFactory;

    public function __construct(Factory $dataFactory)
    {
        $this->dataFactory = $dataFactory;
    }

    /**
     * Returns an object that allows to transform a value
     * to a string value
     */
    public function string() : Transformation
    {
        return new StringTransformation();
    }

    /**
     * Returns an object that allows to transform a value
     * to an integer value
     */
    public function int() : Transformation
    {
        return new IntegerTransformation();
    }

    /**
     * Returns an object that allows to transform a value
     * to a float value
     */
    public function float() : Transformation
    {
        return new FloatTransformation();
    }

    /**
     * Returns an object that allows to transform a value
     * to a boolean value
     */
    public function bool() : Transformation
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
     */
    public function listOf(Transformation $transformation) : Transformation
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
     */
    public function dictOf(Transformation $transformation) : Transformation
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
     * @param Transformation[] $transformation
     * @return Transformation
     */
    public function tupleOf(array $transformation) : Transformation
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
     * @param array<string, Transformation> $transformations
     * @return Transformation
     */
    public function recordOf(array $transformations) : Transformation
    {
        return new RecordTransformation($transformations);
    }

    /**
     * Returns either an transformation object to create objects of an
     * existing class, with variations of constructor parameters OR returns
     * an transformation object to execute a certain method with variation of
     * parameters on the objects.
     * @param string|array{0: object, 1: string} $classNameOrArray
     * @return Transformation
     */
    public function toNew($classNameOrArray) : Transformation
    {
        if (is_array($classNameOrArray)) {
            if (2 !== count($classNameOrArray)) {
                throw new InvalidArgumentException('The array MUST contain exactly two elements');
            }
            return new NewMethodTransformation($classNameOrArray[0], $classNameOrArray[1]);
        }
        return new NewObjectTransformation($classNameOrArray);
    }

    /**
     * @param string $dataType - Name of the data type, this value MUST much
     *                           with the methods provided by the `\ILIAS\Data\Factory`
     * @return Transformation
     */
    public function data(string $dataType) : Transformation
    {
        return $this->toNew([$this->dataFactory, $dataType]);
    }

    public function dateTime() : Transformation
    {
        return new DateTimeTransformation();
    }
}
