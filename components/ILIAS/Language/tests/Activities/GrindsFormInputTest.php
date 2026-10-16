<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Language\Tests\Activities;

use ILIAS\Language\Tests\Activities\RealFieldsUiFactory;
use ILIAS\Language\Tests\Activities\GrindsFormInputTestHost;
use ILIAS\Language\Activities\InvalidInputException;
use ILIAS\Language\Activities\SafeToDisplayActivityError;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * GrindsFormInput in isolation from any concrete Activity, via GrindsFormInputTestHost.
 */
class GrindsFormInputTest extends TestCase
{
    use RealFieldsUiFactory;

    private function host(): GrindsFormInputTestHost
    {
        return new GrindsFormInputTestHost();
    }

    /**
     * getInputDescription() returns FormInput, which doesn't guarantee InputInternal: a FormInput lacking it must
     * give a clean Result\Error, never a TypeError.
     */
    public function testGrindReturnsResultErrorInsteadOfCrashingWhenDescriptionIsNotInputInternal(): void
    {
        $description = $this->createMock(FormInput::class);

        $result = $this->host()->callGrind($description, []);

        $this->assertTrue($result->isError());
        $this->assertInstanceOf(\LogicException::class, $result->error());
    }

    public static function checkboxWhitelistAcceptsRawValueProvider(): array
    {
        return [
            'bool true' => [true, true],
            'int 1' => [1, true],
            'string "1"' => ['1', true],
            'string "true"' => ['true', true],
            'string "checked"' => ['checked', true],
            'string "on"' => ['on', true],
            'bool false' => [false, false],
            'int 0' => [0, false],
            'string "0"' => ['0', false],
            'string "false"' => ['false', false],
            'empty string' => ['', false],
            'null (field omitted entirely)' => [null, false],
        ];
    }

    // real Checkbox field to cover 'checked'/'on', the raw values an HTML checkbox submits
    #[DataProvider('checkboxWhitelistAcceptsRawValueProvider')]
    public function testGrindNormalizesEveryWhitelistedCheckboxRawValue(mixed $raw_value, bool $expected): void
    {
        $description = $this->checkboxGroupDescription();

        $raw_parameters = $raw_value === null ? [] : ['enabled' => $raw_value];

        $result = $this->host()->callGrind($description, $raw_parameters);

        $this->assertTrue($result->isOk());
        $this->assertSame($expected, $result->value()['enabled']);
    }

    /**
     * Real Activities nest every field in a top-level Group: collectRawValues() narrows $raw_value only when
     * recursing into a Group, so a bare top-level field would receive the whole array.
     */
    private function checkboxGroupDescription(): FormInput
    {
        $checkbox = $this->createRealFieldsUiFactory()->input()->field()->checkbox('Enabled', '')
            ->withDedicatedName('enabled');

        return $this->createRealFieldsUiFactory()->input()->field()->group(['enabled' => $checkbox]);
    }

    public static function checkboxWhitelistRejectsRawValueProvider(): array
    {
        return [
            'array' => [[true]],
            'float' => [1.0],
            'unrecognized string' => ['maybe'],
        ];
    }

    /**
     * Values outside the whitelist must become an InvalidInputException (SafeToDisplayActivityError), which
     * RendersActivityErrors shows to the user instead of logging an internal failure.
     */
    #[DataProvider('checkboxWhitelistRejectsRawValueProvider')]
    public function testGrindRejectsRawValueOutsideTheCheckboxWhitelist(mixed $raw_value): void
    {
        $result = $this->host()->callGrind($this->checkboxGroupDescription(), ['enabled' => $raw_value]);

        $this->assertTrue($result->isError());
        $error = $result->error();
        $this->assertInstanceOf(InvalidInputException::class, $error);
        $this->assertInstanceOf(SafeToDisplayActivityError::class, $error);
    }

    /**
     * Same conversion, reached via Text::isClientSideValueOk() instead of normalizeCheckboxRawValue().
     */
    public function testGrindConvertsAUiFrameworkInvalidArgumentExceptionFromANonStringTextValueIntoInvalidInputException(): void
    {
        $text = $this->createRealFieldsUiFactory()->input()->field()->text('Language key', '')
            ->withDedicatedName('language_key');
        $description = $this->createRealFieldsUiFactory()->input()->field()->group([
            'language_key' => $text,
        ]);

        // a bool is passed through collectRawValues() unmodified and reaches Text::withValue() as-is
        $result = $this->host()->callGrind($description, ['language_key' => true]);

        $this->assertTrue($result->isError());
        $error = $result->error();
        $this->assertInstanceOf(InvalidInputException::class, $error);
        $this->assertInstanceOf(SafeToDisplayActivityError::class, $error);
    }

    /**
     * A validation failure must read "<field>: <error>", not Group::withInput()'s generic "ui_error_in_group".
     */
    public function testGrindReportsAFieldAttributedInvalidInputExceptionOnValidationFailure(): void
    {
        $language_key = $this->createRealFieldsUiFactory()->input()->field()->text('Language key', '')
            ->withRequired(true)
            ->withDedicatedName('language_key');
        $description = $this->createRealFieldsUiFactory()->input()->field()->group([
            'language_key' => $language_key,
        ]);

        $result = $this->host()->callGrind($description, []);

        $this->assertTrue($result->isError());
        $error = $result->error();
        $this->assertInstanceOf(InvalidInputException::class, $error);
        $this->assertInstanceOf(SafeToDisplayActivityError::class, $error);
        $this->assertStringStartsWith('language_key: ', $error->getMessage());
        $this->assertNotSame('language_key: ', $error->getMessage());
    }

    /**
     * A raw array of strings (as sent by class.ilObjLanguageFolderGUI.php for 'language_keys') is joined into one
     * comma-separated string.
     */
    public function testGrindJoinsAnArrayOfStringsRawValueForATextFieldIntoACommaSeparatedString(): void
    {
        $language_keys = $this->createRealFieldsUiFactory()->input()->field()->text('Language keys', '')
            ->withDedicatedName('language_keys');
        $description = $this->createRealFieldsUiFactory()->input()->field()->group([
            'language_keys' => $language_keys,
        ]);

        $result = $this->host()->callGrind($description, ['language_keys' => ['de', 'fr']]);

        $this->assertTrue($result->isOk());
        $this->assertSame('de,fr', $result->value()['language_keys']);
    }

    /**
     * The outermost group does not enforce known keys: an unrelated top-level key is ignored.
     */
    public function testGrindSilentlyIgnoresAnUnknownKeyAtTheTopLevelOfRawParameters(): void
    {
        $name = $this->createRealFieldsUiFactory()->input()->field()->text('Name', '')
            ->withDedicatedName('name');
        $description = $this->createRealFieldsUiFactory()->input()->field()->group([
            'name' => $name,
        ]);

        $result = $this->host()->callGrind($description, [
            'name' => 'Foo',
            'some_unrelated_form_field' => 'bar',
        ]);

        $this->assertTrue($result->isOk());
        $this->assertSame('Foo', $result->value()['name']);
    }

    /**
     * The unknown-key message embeds the key unescaped: escaping is RendersActivityErrors::activityErrorMessage()'s
     * job, escaping here would double-escape.
     */
    public function testGrindReportsUnknownNestedGroupKeysRawAndUnescaped(): void
    {
        $de = $this->createRealFieldsUiFactory()->input()->field()->text('German', '')
            ->withDedicatedName('de');
        $translations = $this->createRealFieldsUiFactory()->input()->field()->group([
            'de' => $de,
        ])->withDedicatedName('translations');
        $description = $this->createRealFieldsUiFactory()->input()->field()->group([
            'translations' => $translations,
        ]);

        $dangerous_unknown_key = '<script>alert(1)</script>';

        $result = $this->host()->callGrind($description, [
            'translations' => [
                'de' => 'Hallo',
                $dangerous_unknown_key => 'whatever',
            ],
        ]);

        $this->assertTrue($result->isError());
        $error = $result->error();
        $this->assertInstanceOf(InvalidInputException::class, $error);
        $this->assertInstanceOf(SafeToDisplayActivityError::class, $error);
        $this->assertStringContainsString('Unknown key(s) for translations: ' . $dangerous_unknown_key, $error->getMessage());
        $this->assertStringContainsString('<script>alert(1)</script>', $error->getMessage());
    }

    public static function nonArrayNonNullGroupRawValueProvider(): array
    {
        return [
            'string' => ['not-an-array'],
            'int' => [42],
            'bool' => [true],
        ];
    }

    /**
     * A group value that is neither null nor an array must be rejected; degrading it to an empty array would mask
     * it as a misleading required-field error.
     */
    #[DataProvider('nonArrayNonNullGroupRawValueProvider')]
    public function testGrindRejectsANonArrayNonNullRawValueForANestedGroupInsteadOfSilentlyDegradingToEmptyArray(
        mixed $raw_value
    ): void {
        $de = $this->createRealFieldsUiFactory()->input()->field()->text('German', '')
            ->withDedicatedName('de');
        $translations = $this->createRealFieldsUiFactory()->input()->field()->group([
            'de' => $de,
        ])->withDedicatedName('translations');
        $description = $this->createRealFieldsUiFactory()->input()->field()->group([
            'translations' => $translations,
        ]);

        $result = $this->host()->callGrind($description, ['translations' => $raw_value]);

        $this->assertTrue($result->isError());
        $error = $result->error();
        $this->assertInstanceOf(InvalidInputException::class, $error);
        $this->assertInstanceOf(SafeToDisplayActivityError::class, $error);
        $this->assertStringContainsString(
            'Expected an array of values (or none at all) for translations, got: ' . get_debug_type($raw_value),
            $error->getMessage()
        );
    }
}
