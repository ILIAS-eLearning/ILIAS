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

declare(strict_types=1);

namespace ILIAS\Tests\Refinery\Decode\Transformation;

use ILIAS\Data\Result\Ok;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\Decode\Transformation\Json;
use ILIAS\Refinery\Encode\Transformation\Json as EncodeJson;
use ILIAS\Refinery\Transformation;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;
use stdClass;
use ValueError;

class JsonTest extends TestCase
{
    public function testConstruct(): void
    {
        self::assertInstanceOf(Transformation::class, new Json());
    }

    public function testDefaultMaxDepthIsTheDepthUsedByPhpItself(): void
    {
        $depth_of_json_decode = new ReflectionFunction('json_decode')->getParameters()[2];

        self::assertSame('depth', $depth_of_json_decode->getName());
        self::assertSame($depth_of_json_decode->getDefaultValue(), Json::DEFAULT_MAX_DEPTH);
    }

    public function testMaxDepthBoundsAreTheRangeAcceptedByJsonDecode(): void
    {
        $this->expectException(ValueError::class);

        json_decode('1', true, Json::MAX_DEPTH_UPPER_BOUND + 1, JSON_THROW_ON_ERROR);
    }

    #[DataProvider('provideDecodableValues')]
    public function testTransformDecodesValidJson(mixed $expected, string $json): void
    {
        self::assertSame($expected, new Json()->transform($json));
    }

    /**
     * @return array<string, array{0: mixed, 1: string}>
     */
    public static function provideDecodableValues(): array
    {
        return [
            'null literal' => [null, 'null'],
            'true literal' => [true, 'true'],
            'false literal' => [false, 'false'],
            'integer' => [42, '42'],
            'negative integer' => [-42, '-42'],
            'float' => [4.25, '4.25'],
            'zero' => [0, '0'],
            'empty string' => ['', '""'],
            'string' => ['ILIAS', '"ILIAS"'],
            'escaped unicode string' => ['안녕하서ㅣ요', '"\uc548\ub155\ud558\uc11c\u3163\uc694"'],
            'escaped html special chars' => ['<>\'"&', '"\u003C\u003E\u0027\u0022\u0026"'],
            'empty array' => [[], '[]'],
            'empty object' => [[], '{}'],
            'list' => [[1, 2, 3], '[1,2,3]'],
            'object as associative array' => [['a' => 1, 'b' => 2], '{"a":1,"b":2}'],
            'nested object' => [['a' => ['b' => ['c' => 1]]], '{"a":{"b":{"c":1}}}'],
            'mixed structure' => [['a' => [1, null, true]], '{"a":[1,null,true]}'],
            'surrounding whitespace' => [1, " \n\t1 "],
        ];
    }

    public function testTransformDecodesJsonObjectsIntoAssociativeArraysInsteadOfObjects(): void
    {
        $decoded = new Json()->transform('{"a":{"b":1}}');

        self::assertIsArray($decoded);
        self::assertIsArray($decoded['a']);
        self::assertSame(1, $decoded['a']['b']);
    }

    public function testTransformRoundTripsValuesEncodedByTheEncodeGroup(): void
    {
        $value = ['title' => '<b>ILIAS</b>', 'tags' => ['a', 'b'], 'count' => 3];

        $encoded = new EncodeJson()->transform($value);

        self::assertSame($value, new Json()->transform($encoded));
    }

    #[DataProvider('provideUndecodableJson')]
    public function testTransformRejectsUndecodableJson(string $expected_reason, string $json): void
    {
        $this->expectException(ConstraintViolationException::class);
        $this->expectExceptionMessage(\sprintf('The value cannot be decoded as JSON: %s.', $expected_reason));

        new Json()->transform($json);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function provideUndecodableJson(): array
    {
        return [
            'empty string' => ['Syntax error', ''],
            'blank string' => ['Syntax error', '   '],
            'unclosed object' => ['Syntax error', '{'],
            'unquoted text' => ['Syntax error', 'not json'],
            'trailing comma' => ['Syntax error', '[1,]'],
            'single quotes' => ['Syntax error', "{'a':1}"],
            'malformed utf8' => ['Malformed UTF-8 characters, possibly incorrectly encoded', "\"\xB1\x31\""],
        ];
    }

    #[DataProvider('provideNonStringValues')]
    public function testTransformRejectsNonStringValues(string $expected_type, mixed $value): void
    {
        $this->expectException(ConstraintViolationException::class);
        $this->expectExceptionMessage(
            \sprintf('The value of type "%s" is not a string and cannot be decoded as JSON.', $expected_type)
        );

        new Json()->transform($value);
    }

    /**
     * @return array<string, array{0: string, 1: mixed}>
     */
    public static function provideNonStringValues(): array
    {
        return [
            'null' => ['null', null],
            'integer' => ['int', 1],
            'float' => ['float', 1.5],
            'boolean' => ['bool', true],
            'array' => ['array', ['{"a":1}']],
            'object' => [stdClass::class, new stdClass()],
        ];
    }

    public function testTransformRejectsStructuresExceedingTheDefaultMaxDepth(): void
    {
        $transformation = new Json();

        $this->expectException(ConstraintViolationException::class);
        $this->expectExceptionMessage('The value cannot be decoded as JSON: Maximum stack depth exceeded.');

        $transformation->transform($this->nestedArrays(Json::DEFAULT_MAX_DEPTH));
    }

    public function testTransformAcceptsStructuresAtTheDefaultMaxDepth(): void
    {
        $decoded = new Json()->transform($this->nestedArrays(Json::DEFAULT_MAX_DEPTH - 1));

        self::assertIsArray($decoded);
    }

    public function testTransformAcceptsStructuresAtTheConfiguredMaxDepth(): void
    {
        self::assertSame(1, new Json(1)->transform('1'));
        self::assertSame([1], new Json(2)->transform('[1]'));
        self::assertSame([[1]], new Json(3)->transform('[[1]]'));
    }

    #[DataProvider('provideStructuresExceedingTheConfiguredMaxDepth')]
    public function testTransformRejectsStructuresExceedingTheConfiguredMaxDepth(int $max_depth, string $json): void
    {
        $this->expectException(ConstraintViolationException::class);
        $this->expectExceptionMessage('The value cannot be decoded as JSON: Maximum stack depth exceeded.');

        new Json($max_depth)->transform($json);
    }

    /**
     * @return array<string, array{0: int, 1: string}>
     */
    public static function provideStructuresExceedingTheConfiguredMaxDepth(): array
    {
        return [
            'empty list with depth 1' => [1, '[]'],
            'list with depth 2' => [2, '[[1]]'],
            'object with depth 2' => [2, '{"a":{"b":1}}'],
            'list with depth 3' => [3, '[[[1]]]'],
        ];
    }

    #[DataProvider('provideMaxDepthOutOfBounds')]
    public function testConstructRejectsMaxDepthOutOfBounds(int $max_depth): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            \sprintf('Maximum depth must be between 1 and 2147483647, got %d.', $max_depth)
        );

        new Json($max_depth);
    }

    /**
     * @return array<string, array{0: int}>
     */
    public static function provideMaxDepthOutOfBounds(): array
    {
        return [
            'zero' => [0],
            'negative' => [-1],
            'far below the lower bound' => [PHP_INT_MIN],
            'above the upper bound' => [2147483648],
            'far above the upper bound' => [PHP_INT_MAX],
        ];
    }

    #[DataProvider('provideMaxDepthAtBounds')]
    public function testConstructAcceptsMaxDepthAtBounds(int $max_depth): void
    {
        self::assertSame(1, new Json($max_depth)->transform('1'));
    }

    /**
     * @return array<string, array{0: int}>
     */
    public static function provideMaxDepthAtBounds(): array
    {
        return [
            'lower bound' => [Json::MAX_DEPTH_LOWER_BOUND],
            'default' => [Json::DEFAULT_MAX_DEPTH],
            'upper bound' => [Json::MAX_DEPTH_UPPER_BOUND],
        ];
    }

    public function testApplyToWrapsTheDecodedValue(): void
    {
        $result = new Json()->applyTo(new Ok('{"a":1}'));

        self::assertTrue($result->isOK());
        self::assertSame(['a' => 1], $result->value());
    }

    public function testApplyToReifiesTheViolation(): void
    {
        $result = new Json()->applyTo(new Ok('{'));

        self::assertTrue($result->isError());
        self::assertInstanceOf(ConstraintViolationException::class, $result->error());
    }

    public function testInvokeBehavesLikeTransform(): void
    {
        $transformation = new Json();

        self::assertSame(['a' => 1], $transformation('{"a":1}'));
    }

    private function nestedArrays(int $levels): string
    {
        return str_repeat('[', $levels) . str_repeat(']', $levels);
    }
}
