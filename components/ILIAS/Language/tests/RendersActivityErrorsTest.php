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
 * Unit tests for the RendersActivityErrors trait itself (see its own class
 * docblock), in isolation from any concrete GUI class - via
 * RendersActivityErrorsTestHost, a minimal class that only mixes in the
 * trait and exposes activityErrorMessage() through a public wrapper.
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

        // The domain layer (UninstallLanguage/RemoveLocalLanguageChanges etc.)
        // deliberately embeds the raw, unescaped title in the exception
        // message - activityErrorMessage() is the one seam that HTML-escapes
        // it for safe display, so the '"' characters must come back as
        // '&quot;' here.
        $this->assertSame('Multiple language objects share the title(s) &quot;fr&quot;.', $message);
    }

    /**
     * Regression test for the escaping seam itself: a SafeToDisplayActivityError
     * message containing genuinely dangerous markup (not just a stray quote)
     * must come back HTML-escaped - proving activityErrorMessage() is not
     * merely quote-escaping by coincidence but actually protects against
     * XSS-shaped content that a caller-controlled language key/title/field
     * name could carry.
     */
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

    /**
     * Regression test for the ENT_SUBSTITUTE fix: without it, invalid UTF-8
     * anywhere in a SafeToDisplayActivityError's message (e.g. caller-
     * controlled data embedded by the domain layer, see the class docblock
     * above) made htmlspecialchars() return an EMPTY STRING for the WHOLE
     * message, not just drop the offending bytes - the user would see a
     * blank error box with no indication anything went wrong at all. With
     * ENT_SUBSTITUTE, the invalid byte sequence is replaced by U+FFFD
     * (encoded as UTF-8: "\xEF\xBF\xBD") and the rest of the message - both
     * before and after the invalid bytes - survives.
     */
    public function testASafeToDisplayActivityErrorMessageWithInvalidUtf8IsNotSilentlyDroppedToAnEmptyString(): void
    {
        $logger = $this->createMock(\ilLogger::class);
        $logger->expects($this->never())->method('error');

        // "\xC3\x28" is not valid UTF-8 (0xC3 announces a 2-byte sequence,
        // but 0x28 "(" is not a valid continuation byte).
        $error = new InvalidInputException("Invalid language key \"d\xC3\x28\": bad input.");

        $message = $this->host($logger, $this->languageMockReturningTopicAsIs())
            ->callActivityErrorMessage($error);

        $this->assertNotSame('', $message);
        $this->assertStringStartsWith('Invalid language key &quot;d', $message);
        $this->assertStringContainsString("\u{FFFD}", $message);
        $this->assertStringEndsWith('&quot;: bad input.', $message);
    }

    /**
     * Companion to the previous test: the plain-string branch (is_string($error),
     * e.g. 'msg_no_perm_write' from $this->lng->txt()) must NOT be escaped at
     * all - proving no double-escaping was introduced alongside the new
     * escaping seam for SafeToDisplayActivityError.
     */
    public function testAPlainStringErrorContainingHtmlSignificantCharactersIsReturnedCompletelyUnescaped(): void
    {
        $logger = $this->createMock(\ilLogger::class);
        $logger->expects($this->never())->method('error');

        $message = $this->host($logger, $this->languageMockReturningTopicAsIs())
            ->callActivityErrorMessage('<b>already trusted</b> & safe "as-is"');

        $this->assertSame('<b>already trusted</b> & safe "as-is"', $message);
    }

    /**
     * A non-SafeToDisplayActivityError \Throwable must never be shown
     * directly - its message is logged instead, and a generic, localized
     * text ('action_aborted') is returned.
     */
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

    /**
     * Regression test for describeThrowableChain(): a wrapper exception
     * (e.g. the \RuntimeException/InvalidInputException GrindsFormInput::grind()
     * wraps a lower-level \Throwable in) must never hide the original
     * failure's own class and message from the log - the FULL cause chain
     * (every getPrevious() link) must be logged, not just the outermost
     * wrapper.
     */
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

        // Both the outer wrapper AND the inner root cause (class + message)
        // must appear in the logged string - a wrapper must never hide the
        // original failure.
        $this->assertStringContainsString(\RuntimeException::class . ': failed to write language entry', $logged_message);
        $this->assertStringContainsString(\PDOException::class . ': duplicate key value violates unique constraint', $logged_message);

        // The two descriptions must be visibly separated ("Caused by:"),
        // not merely both present anywhere in the string by coincidence.
        $this->assertStringContainsString("Caused by:\n", $logged_message);
        $this->assertLessThan(
            strpos($logged_message, \PDOException::class . ': duplicate key value violates unique constraint'),
            strpos($logged_message, \RuntimeException::class . ': failed to write language entry')
        );
    }

    /**
     * A THREE-deep chain must have every link (not just the first two)
     * logged - describeThrowableChain() recurses via getPrevious() until it
     * returns null, not just once.
     */
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
 * Minimal host for RendersActivityErrors: only mixes in the trait, wires up
 * its two required collaborators via the constructor (rather than the
 * `assign exactly once, in your own constructor` idiom every real GUI class
 * uses), and exposes activityErrorMessage() through a public wrapper.
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
