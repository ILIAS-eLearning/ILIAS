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
 * Unit tests for the GrindsFormInput trait in isolation from any concrete Activity, via
 * GrindsFormInputTestHost (mixes in the trait, exposes grind() through a public wrapper).
 * Activity-specific contract tests live in each Activity's own test class instead.
 */
class GrindsFormInputTest extends TestCase
{
    use RealFieldsUiFactory;

    private function host(): GrindsFormInputTestHost
    {
        return new GrindsFormInputTestHost();
    }

    /**
     * getInputDescription() returns the public FormInput interface, which doesn't guarantee
     * InputInternal - grind() must not assume it does; a FormInput missing it must be a clean
     * Result\Error, never a fatal TypeError.
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

    // Uses a REAL Checkbox field (not a mock) to also cover 'checked'/'on', the two raw values an
    // actual HTML checkbox submits.
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
     * Every field this trait grinds is nested inside a top-level Group in real Activities -
     * collectRawValues() only narrows $raw_value to one key when recursing into a Group's
     * children, so a bare top-level field would wrongly receive the whole $raw_parameters array
     * as its own value.
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
     * Anything outside the explicit whitelist must be rejected, not guessed at - grind()'s
     * dedicated catch(\InvalidArgumentException) (before the generic catch(\Throwable)) must turn
     * this into an InvalidInputException (a SafeToDisplayActivityError), which
     * RendersActivityErrors relies on to show the rejection to the end user directly rather than
     * logging it as an internal failure.
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
     * Same \InvalidArgumentException -> InvalidInputException conversion, but reached via the UI
     * framework's own Text::isClientSideValueOk() type check instead of
     * normalizeCheckboxRawValue().
     */
    public function testGrindConvertsAUiFrameworkInvalidArgumentExceptionFromANonStringTextValueIntoInvalidInputException(): void
    {
        $text = $this->createRealFieldsUiFactory()->input()->field()->text('Language key', '')
            ->withDedicatedName('language_key');
        $description = $this->createRealFieldsUiFactory()->input()->field()->group([
            'language_key' => $text,
        ]);

        // A bool is neither a string nor an array - collectRawValues()
        // passes it straight through unmodified (only array/Checkbox raw
        // values are special-cased), so it reaches Text::withValue() as-is.
        $result = $this->host()->callGrind($description, ['language_key' => true]);

        $this->assertTrue($result->isError());
        $error = $result->error();
        $this->assertInstanceOf(InvalidInputException::class, $error);
        $this->assertInstanceOf(SafeToDisplayActivityError::class, $error);
    }

    /**
     * A validation failure must produce "<field>: <error>" (via describeInputError()/
     * collectFieldErrors()), identifying the field by its dedicated name - not the generic,
     * uninformative "ui_error_in_group" text Group::withInput() would otherwise surface.
     */
    public function testGrindReportsAFieldAttributedInvalidInputExceptionOnValidationFailure(): void
    {
        $language_key = $this->createRealFieldsUiFactory()->input()->field()->text('Language key', '')
            ->withRequired(true)
            ->withDedicatedName('language_key');
        $description = $this->createRealFieldsUiFactory()->input()->field()->group([
            'language_key' => $language_key,
        ]);

        // Blank/missing raw value for a required field - fails its own
        // hasMinLength(1) constraint inside grind() itself.
        $result = $this->host()->callGrind($description, []);

        $this->assertTrue($result->isError());
        $error = $result->error();
        $this->assertInstanceOf(InvalidInputException::class, $error);
        $this->assertInstanceOf(SafeToDisplayActivityError::class, $error);
        $this->assertStringStartsWith('language_key: ', $error->getMessage());
        $this->assertNotSame('language_key: ', $error->getMessage());
    }

    /**
     * A raw array-of-strings value (e.g. the shape class.ilObjLanguageFolderGUI.php sends for
     * 'language_keys') must be joined into the same comma-separated string a real HTML text input
     * would carry, before it reaches the field.
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
     * Regression test for the outermost group's $enforce_known_keys=false default (see
     * collectRawValues()): an unrelated key elsewhere in top-level $raw_parameters must be
     * ignored, not rejected.
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
            // Not a field of the outermost group - must be tolerated, not rejected.
            'some_unrelated_form_field' => 'bar',
        ]);

        $this->assertTrue($result->isOk());
        $this->assertSame('Foo', $result->value()['name']);
    }

    /**
     * The unknown-key message must embed the key RAW/unescaped - escaping is
     * RendersActivityErrors::activityErrorMessage()'s job, the single rendering seam; escaping
     * here too would double-escape it.
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
        // Not escaped here - htmlspecialchars() would turn '<'/'>' into
        // '&lt;'/'&gt;', which must NOT happen at this layer.
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
     * A group's raw value that is neither null (omitted) nor an array is a genuine type mismatch
     * and must be rejected - silently degrading it to an empty array would instead make every
     * child field look merely "omitted", masking the real problem behind a misleading
     * required-field error.
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
