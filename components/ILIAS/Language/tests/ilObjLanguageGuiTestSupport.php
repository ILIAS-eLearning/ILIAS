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

use PHPUnit\Framework\MockObject\Stub;

/**
 * Shared fixture of the ilObjLanguageFolderGUI*Test / ilObjLanguageExtGUI*Test classes. The GUIs are bound to the
 * legacy $DIC bootstrap, so tests create an instance without constructor and inject() the collaborators needed.
 * The consuming classes must extend PHPUnit's TestCase.
 */
trait ilObjLanguageGuiTestSupport
{
    private ?\ilLogger $stubbed_activity_error_logger = null;
    private mixed $dic_before_test = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dic_before_test = $GLOBALS['DIC'] ?? null;
    }

    protected function tearDown(): void
    {
        if ($this->dic_before_test === null) {
            unset($GLOBALS['DIC']);
        } else {
            $GLOBALS['DIC'] = $this->dic_before_test;
        }
        $this->dic_before_test = null;
        $this->stubbed_activity_error_logger = null;
        parent::tearDown();
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private function newGuiWithoutConstructor(string $class): object
    {
        return (new ReflectionClass($class))->newInstanceWithoutConstructor();
    }

    /** For a mock the property is looked up on its declaring class: a plain ReflectionProperty on the mock cannot find readonly ones. */
    private function inject(object $gui, string $property_name, mixed $value): void
    {
        for ($class = new ReflectionClass($gui); $class !== false; $class = $class->getParentClass()) {
            if ($class->hasProperty($property_name) && $class->getProperty($property_name)->getDeclaringClass()->getName() === $class->getName()) {
                $class->getProperty($property_name)->setValue($gui, $value);
                return;
            }
        }
        throw new LogicException('No property ' . $property_name . ' on ' . $gui::class);
    }

    /**
     * Expects one error log entry "<exception class>: <message>\n<trace>"; only the prefix is compared. Call before building the GUI.
     *
     * @param non-empty-string $expected_logged_message
     * @param class-string<\Throwable> $expected_exception_class
     */
    private function stubLoggerLangError(
        string $expected_logged_message,
        string $expected_exception_class = \RuntimeException::class
    ): void {
        $expected_prefix = $expected_exception_class . ': ' . $expected_logged_message . "\n";

        $logger = $this->createMock(\ilLogger::class);
        $logger->expects($this->once())->method('error')->with(
            $this->callback(
                static fn(string $logged): bool => str_starts_with($logged, $expected_prefix)
            )
        );

        $this->stubbed_activity_error_logger = $logger;
    }

    private function activityErrorLoggerForGui(): \ilLogger
    {
        return $this->stubbed_activity_error_logger ?? $this->createStub(\ilLogger::class);
    }

    /** maybePerformAs() reads ui_factory->input(), whose typed property is otherwise uninitialized. */
    private function stubUiFactoryForMaybePerformAs(): \ILIAS\UI\Factory&Stub
    {
        $ui_factory = $this->createStub(\ILIAS\UI\Factory::class);
        $ui_factory->method('input')->willReturn(
            $this->createStub(\ILIAS\UI\Component\Input\Factory::class)
        );

        return $ui_factory;
    }

    /** @param list<string> $installed_languages */
    private function createLanguageMockReturningTopicAsIs(array $installed_languages = []): ilLanguage&Stub
    {
        $lng = $this->createStub(ilLanguage::class);
        $lng->method('txt')->willReturnArgument(0);
        $lng->method('getInstalledLanguages')->willReturn($installed_languages);

        return $lng;
    }
}
