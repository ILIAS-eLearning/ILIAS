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

namespace ILIAS\Refinery\KindlyTo;

use ILIAS\Refinery\KindlyTo\Transformation\StringTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\BooleanTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\DateTimeTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\IntegerTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\FloatTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\ListTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\RecordTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\TupleTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\DictionaryTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\NullTransformation;
use ILIAS\Refinery\Transformation;
use ILIAS\Data\Factory;

/**
 * Transformations in this group transform data to primitive types to establish
 * a baseline for more complex transformation. They use [Postels Law of robustness](https://en.wikipedia.org/wiki/Robustness_principle)
 * and thus will be useful when communicating with other systems. Look into the
 * single transformations for more information about the exact behaviour.
 *
 * They don't try to mimic PHPs type cast, but instead follow more sophisticated
 * rules devised in a series of workshops with interested developers from the
 * community. Thanks Michael Jansen, Fabian Schmid, Alex Killing, Stephan Winiker,
 * Timon Amstutz and Nils Haagen.
 */
class Group
{
    private Factory $dataFactory;

    public function __construct(Factory $dataFactory)
    {
        $this->dataFactory = $dataFactory;
    }

    /**
     * Get a kind transformation to an `int`.
     *
     * This supports:
     *   - strings matching \s*(0|(-?[1-9]\d*))\s*, trimming is supported
     *   - floats, which will be rounded naturally
     *   - bools, where true maps to 1 and false to 0
     * This doesn't support:
     *   - "" will be discarded, as well as null, because null or empty are not 0
     *   - delimiters per mill, like 1'000, because these depend on locales
     *   - written variants of "true", "false" or "null", because these definitely
     *     aren't ints
     *   - no leading zeros, because these sometimes are used to mark octals
     *   - no strings in various encodings, like octal/hex/binary/floating point,
     *     because there are so many possible formats and interpretations
     *   - strings containing numbers bigger than PHP_INT_MAX, because what would
     *     we do with them
     *
     * All other data will be discarded.
     */
    public function int() : Transformation
    {
        return new IntegerTransformation();
    }

    /**
     * Get a kind transformation to a `float`.
     *
     * This supports:
     *   - strings in natural notation matching \s*(0|(-?[1-9]\d*([.,]\d+)?))\s*,
     *     trimming is supported
     *   - strings in floating point representation matching \s*-?\d+[eE]-?\d+\s*,
     *     trimming is supported
     *   - ints, which will be typecasted to float
     *   - bools, where true maps to 1.0 and false to 0.0
     * This doesn't support:
     *   - "" will be discarded, as well as null, because null or empty are not 0
     *   - delimiters per mill, like 1'000, because they can't reliably be told from
     *     the decimal delimiter
     *   - written variants of "true", "false" or "null", because these definitely are
     *     no floats
     *   - "NaN", NaN, "INF" and INF, because these will be introducing problems in
     *     subsequent calculations. Do you really want to do math with floats?
     *
     * All other data will be discarded.
     */
    public function float() : Transformation
    {
        return new FloatTransformation();
    }

    /**
     * Get a kind transformation to a `string`.
     *
     * This supports:
     *   - ints, which will be serialized naturally to string
     *   - bool, where true maps to "true" and false to "false"
     *   - float, which will be serialized to the floating point representation
     *   - All other data will be transformed using __toString.
     *
     * Regarding the usage of __toString: Transformations in this group are not
     * meant to provide ways to reliably serialize data. For these type of
     * transformations, two new groups `serializeTo` and `serializeFrom` would
     * be a better fit and could deal with intricacies of various serialization
     * formats way better. Instead, these transformations try to offer a forgiving
     * way to treat incoming data. So by using kindlyTo()->string(), I tell the
     * Refinery that I expect a certain piece of data to be some string, and expect
     * that the Refinery tries to produce one. A transformation to string is a
     * lossy transformation most of the time anyway, if we don't talk about e.g.
     * serialization formats. So we don't loose much if we, e.g., transform an
     * array to "Array".
     */
    public function string() : Transformation
    {
        return new StringTransformation();
    }


    /**
     * Get a kind transformation to a `bool`.
     *
     * This supports:
     *   - "true" and "false" in all kinds of capitalization
     *   - 0 and "0", mapping to false, as well as 1 and "1", mapping to true
     * This doesn't support:
     *   - "null" or null, since the absence of some data is something else then
     *     true or false
     *   - 1.0 or 0.0, because we don't expect that someone really wants to transmit
     *     booleans disguised as floats.
     *
     * All other data will be discarded. We could have decided to use a much more
     * liberal approach by e.g. interpreting "existence of data" as true and absence
     * of data as false. However, these transformations here are not meant to
     * interpret incoming data as desired at all costs, but instead try to be
     * forgiving regarding various quirks in encoding when different systems talk
     * to each other. There seem to be some more or less sane ways to encode bools,
     * but writing "some data" to represent true, or an empty list to represent
     * is something else. Also, being more liberal introduces a various odd places
     * in the mapping. If, e.g., we'd map a null to false, an empty list to false
     * as well, would we map [null] to false or to true? Why? All this problems
     * seem to introduce more problems than they solve, so we decided to not be
     * very liberal here.
     */
    public function bool() : Transformation
    {
        return new BooleanTransformation();
    }

    /**
     * Get a kind transformation to a `DateTimeImmutable`.
     *
     * This supports:
     *   - all formats mentioned in DateTimeInterface, which are probed in a
     *     sensible order
     *   - integers and float, which will be interpreted as Unix timestamps.
     *
     * All other data will be discarded.
     */
    public function dateTime() : Transformation
    {
        return new DateTimeTransformation();
    }

    /**
     * Get a kind transformation to a list.
     *
     * This supports all data represented as PHP array, which will be used via
     * array_values($v). Non-arrays will be wrapped in one.
     */
    public function listOf(Transformation $transformation) : Transformation
    {
        return new ListTransformation($transformation);
    }

    /**
     * Get a kind transformation to a dictionary.
     *
     * This supports all data represented as PHP array.
     */
    public function dictOf(Transformation $transformation) : Transformation
    {
        return new DictionaryTransformation($transformation);
    }

    /**
     * Get a kind transformation to a tuple.
     *
     * This supports all data represented as PHP array, which will be used via
     * array_values($V). Non-arrays will be wrapped in one.
     * This will accept array with more fields than expected, but drop the extra fields.
     *
     * @param Transformation[] $transformation
     */
    public function tupleOf(array $transformation) : Transformation
    {
        return new TupleTransformation($transformation);
    }

    /**
     * Get a kind transformation to a record.
     *
     * This supports all data represented as PHP array.
     * This will accept array with more fields than expected, but drop the extra fields.
     *
     * @param array<string, Transformation> $transformations
     */
    public function recordOf(array $transformations) : Transformation
    {
        return new RecordTransformation($transformations);
    }

    /**
     * Get a kind transformation to null.
     *
     * Transforms an empty string to null; e.g.in the case of optional numeric inputs,
     * an empty string is being relayed to the server: This is rather the absence
     * of input than an invalid number.
     */
    public function null() : Transformation
    {
        return new NullTransformation();
    }
}
