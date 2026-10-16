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

namespace ILIAS\Language;

use ILIAS\Language\Activities\AmbiguousLanguageTitleException;
use ILIAS\Language\Activities\InvalidInputException;
use PHPUnit\Framework\TestCase;

/**
 * The RendersActivityErrors trait in isolation, via RendersActivityErrorsTestHost.
 */
class RendersActivityErrorsTest extends TestCase
{
    private function host(\ilLogger $logger, \ilLanguage|\PHPUnit\Framework\MockObject\MockObject $lng): RendersActivityErrorsTestHost
    {
        return new RendersActivityErrorsTestHost($logger, $lng);
    }

    private function languageMockReturningTopicAsIs(): \ilLanguage
    {
        $lng = $this->createMock(\ilLanguage::class);
        $lng->method('txt')->willReturnArgument(0);

        return $lng;
    }

    public function testAPlainStringErrorIsReturnedAsIsAndNeverLogged(): void
    {
        $logger = $this->createMock(\ilLogger::class);
        $logger->expects($this->never())->method('error');

        $message = $this->host($logger, $this->languageMockReturningTopicAsIs())
            ->callActivityErrorMessage('msg_no_perm_write');

        $this->assertSame('msg_no_perm_write', $message);
    }

    public function testASafeToDisplayActivityErrorMessageIsReturnedAsIsAndNeverLogged(): void
    {
        $logger = $this->createMock(\ilLogger::class);
        $logger->expects($this->never())->method('error');

        $error = new InvalidInputException('language_keys: not_min_length');

        $message = $this->host($logger, $this->languageMockReturningTopicAsIs())
            ->callActivityErrorMessage($error);

        $this->assertSame('language_keys: not_min_length', $message);
    }

    public function testAnAmbiguousLanguageTitleExceptionIsAlsoTreatedAsSafeToDisplayAndNeverLogged(): void
    {
        $logger = $this->createMock(\ilLogger::class);
        $logger->expects($this->never())->method('error');

        $error = new AmbiguousLanguageTitleException('Multiple language objects share the title(s) "fr".');

        $message = $this->host($logger, $this->languageMockReturningTopicAsIs())
            ->callActivityErrorMessage($error);

        // the domain layer embeds the raw title in the message: activityErrorMessage() is the one seam that escapes it
        $this->assertSame('Multiple language objects share the title(s) &quot;fr&quot;.', $message);
    }

    /** Dangerous markup (not just a stray quote) must come back HTML-escaped. */
    public function testASafeToDisplayActivityErrorMessageWithDangerousMarkupIsHtmlEscaped(): void
    {
        $logger = $this->createMock(\ilLogger::class);
        $logger->expects($this->never())->method('error');

        $error = new InvalidInputException('Unknown key(s) for <script>alert(1)</script> & "quoted" \'single\'.');

        $message = $this->host($logger, $this->languageMockReturningTopicAsIs())
            ->callActivityErrorMessage($error);

        $this->assertSame(
            'Unknown key(s) for &lt;script&gt;alert(1)&lt;/script&gt; &amp; &quot;quoted&quot; &#039;single&#039;.',
            $message
        );
        $this->assertStringNotContainsString('<script>', $message);
    }

    /** Without ENT_SUBSTITUTE invalid UTF-8 made htmlspecialchars() return an empty string for the whole message. */
    public function testASafeToDisplayActivityErrorMessageWithInvalidUtf8IsNotSilentlyDroppedToAnEmptyString(): void
    {
        $logger = $this->createMock(\ilLogger::class);
        $logger->expects($this->never())->method('error');

        $error = new InvalidInputException("Invalid language key \"d\xC3\x28\": bad input.");

        $message = $this->host($logger, $this->languageMockReturningTopicAsIs())
            ->callActivityErrorMessage($error);

        $this->assertNotSame('', $message);
        $this->assertStringStartsWith('Invalid language key &quot;d', $message);
        $this->assertStringContainsString("\u{FFFD}", $message);
        $this->assertStringEndsWith('&quot;: bad input.', $message);
    }

    /** The plain-string branch must not be escaped (no double-escaping). */
    public function testAPlainStringErrorContainingHtmlSignificantCharactersIsReturnedCompletelyUnescaped(): void
    {
        $logger = $this->createMock(\ilLogger::class);
        $logger->expects($this->never())->method('error');

        $message = $this->host($logger, $this->languageMockReturningTopicAsIs())
            ->callActivityErrorMessage('<b>already trusted</b> & safe "as-is"');

        $this->assertSame('<b>already trusted</b> & safe "as-is"', $message);
    }

    /** A non-SafeToDisplayActivityError is never shown: its message is logged, a generic 'action_aborted' returned. */
    public function testANonSafeToDisplayThrowableIsLoggedAndAGenericMessageIsReturned(): void
    {
        $logger = $this->createMock(\ilLogger::class);
        $logger->expects($this->once())->method('error')->with(
            $this->stringContains('boom')
        );

        $message = $this->host($logger, $this->languageMockReturningTopicAsIs())
            ->callActivityErrorMessage(new \RuntimeException('boom'));

        $this->assertSame('action_aborted', $message);
    }

    /** A wrapper exception must not hide the root cause: the full getPrevious() chain is logged. */
    public function testANonSafeToDisplayThrowableWithAPreviousExceptionLogsTheFullCauseChain(): void
    {
        $root_cause = new \PDOException('duplicate key value violates unique constraint');
        $wrapper = new \RuntimeException('failed to write language entry', 0, $root_cause);

        $logged = [];
        $logger = $this->createMock(\ilLogger::class);
        $logger->expects($this->once())->method('error')->willReturnCallback(
            static function (string $message) use (&$logged): void {
                $logged[] = $message;
            }
        );

        $message = $this->host($logger, $this->languageMockReturningTopicAsIs())
            ->callActivityErrorMessage($wrapper);

        $this->assertSame('action_aborted', $message);
        $this->assertCount(1, $logged);
        $logged_message = $logged[0];

        $this->assertStringContainsString(\RuntimeException::class . ': failed to write language entry', $logged_message);
        $this->assertStringContainsString(\PDOException::class . ': duplicate key value violates unique constraint', $logged_message);

        $this->assertStringContainsString("Caused by:\n", $logged_message);
        $this->assertLessThan(
            strpos($logged_message, \PDOException::class . ': duplicate key value violates unique constraint'),
            strpos($logged_message, \RuntimeException::class . ': failed to write language entry')
        );
    }

    /** A three-deep chain logs every link. */
    public function testAThreeDeepCauseChainLogsEveryLink(): void
    {
        $innermost = new \InvalidArgumentException('column "xx" does not exist');
        $middle = new \PDOException('query failed', 0, $innermost);
        $outer = new \RuntimeException('database operation failed', 0, $middle);

        $logged = [];
        $logger = $this->createMock(\ilLogger::class);
        $logger->method('error')->willReturnCallback(
            static function (string $message) use (&$logged): void {
                $logged[] = $message;
            }
        );

        $this->host($logger, $this->languageMockReturningTopicAsIs())->callActivityErrorMessage($outer);

        $this->assertCount(1, $logged);
        $logged_message = $logged[0];

        $this->assertStringContainsString(\RuntimeException::class . ': database operation failed', $logged_message);
        $this->assertStringContainsString(\PDOException::class . ': query failed', $logged_message);
        $this->assertStringContainsString(
            \InvalidArgumentException::class . ': column "xx" does not exist',
            $logged_message
        );
    }
}

/**
 * Minimal host: mixes in the trait, sets its two collaborators in the constructor and exposes activityErrorMessage().
 */
final class RendersActivityErrorsTestHost
{
    use RendersActivityErrors;

    public function __construct(
        \ilLogger $activity_error_logger,
        private readonly \ilLanguage $lng,
    ) {
        $this->activity_error_logger = $activity_error_logger;
    }

    public function callActivityErrorMessage(\Throwable|string $error): string
    {
        return $this->activityErrorMessage($error);
    }
}
