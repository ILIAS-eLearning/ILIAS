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

use ILIAS\Init\ErrorHandling\Incident\ErrorIncident;
use ILIAS\Init\ErrorHandling\Incident\ErrorIncidentId;
use ILIAS\Init\ErrorHandling\Notification\ErrorIncidentUserMessage;
use ILIAS\Init\ErrorHandling\Notification\Settings;
use PHPUnit\Framework\TestCase;

class ErrorIncidentUserMessageTest extends TestCase
{
    public function testFormatsFallbackMessageWithoutLanguageWithMail(): void
    {
        $settings = $this->createMock(Settings::class);
        $settings->method('errorRecipient')->willReturn('admin@example.org');

        $message_formatter = new ErrorIncidentUserMessage();
        $message = $message_formatter->format(new ErrorIncident(new ErrorIncidentId('abc_12')), null, $settings);

        self::assertStringContainsString('abc_12', $message);
        self::assertStringContainsString('admin@example.org', $message);
    }

    public function testFormatsLocalizedMessageWithLanguageWithoutMail(): void
    {
        $language = $this->createMock(ilLanguage::class);
        $language->expects($this->once())->method('loadLanguageModule')->with('logging');
        $language->method('txt')->willReturnCallback(
            static fn(string $key): string => match ($key) {
                'log_error_message' => 'Logged error %s',
                default => $key,
            }
        );

        $message_formatter = new ErrorIncidentUserMessage();
        $message = $message_formatter->format(new ErrorIncident(new ErrorIncidentId('abc_12')), $language, null);

        self::assertSame('Logged error abc_12', $message);
    }
}
