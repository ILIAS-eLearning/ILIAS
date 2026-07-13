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

namespace ILIAS\MetaData\OERExposer\OAIPMH;

use PHPUnit\Framework\TestCase;
use ILIAS\Data\URI;
use ILIAS\MetaData\Settings\SettingsInterface;
use ILIAS\MetaData\Settings\NullSettings;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\ParserInterface as RequestParserInterface;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\NullParser;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\RequestInterface;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\NullRequest;
use ILIAS\MetaData\OERExposer\OAIPMH\Responses\RequestProcessorInterface;
use ILIAS\MetaData\OERExposer\OAIPMH\Responses\NullRequestProcessor;
use ILIAS\MetaData\OERExposer\OAIPMH\HTTP\WrapperInterface as HTTPWrapperInterface;
use ILIAS\MetaData\OERExposer\OAIPMH\HTTP\NullWrapper;

class HandlerTest extends TestCase
{
    protected function getURI(string $string): URI
    {
        $url = $this->createMock(URI::class);
        $url->method('__toString')->willReturn($string);
        return $url;
    }

    protected function getHTTPWrapper(): HTTPWrapperInterface
    {
        return new class () extends NullWrapper {
            public array $exposed_responses = [];

            public function sendResponseAndClose(
                int $status_code,
                string $message = '',
                ?\DOMDocument $body = null
            ): void {
                $this->exposed_responses[] = [
                    'status' => $status_code,
                    'message' => $message,
                    'body' => $body?->saveXML($body->documentElement)
                ];
            }
        };
    }

    protected function getSettings(bool $activated): SettingsInterface
    {
        return new class ($activated) extends NullSettings {
            public function __construct(
                protected bool $activated
            ) {
            }

            public function isOAIPMHActive(): bool
            {
                return $this->activated;
            }
        };
    }

    protected function getRequestParser(string $content): RequestParserInterface
    {
        return new class ($content) extends NullParser {
            public function __construct(protected string $content)
            {
            }

            public function parseFromHTTP(URI $base_url): RequestInterface
            {
                return new class ($this->content, $base_url) extends NullRequest {
                    public function __construct(
                        protected string $content,
                        protected URI $base_url
                    ) {
                    }

                    public function baseURL(): URI
                    {
                        return $this->base_url;
                    }

                    public function exposeContent(): string
                    {
                        return $this->content;
                    }
                };
            }
        };
    }

    protected function getRequestProcessor(
        bool $throws_exception = false,
        bool $throws_error = false,
        bool $triggers_error = false
    ): RequestProcessorInterface {
        return new class (
            $throws_exception,
            $throws_error,
            $triggers_error
        ) extends NullRequestProcessor {
            public function __construct(
                protected bool $throws_exception,
                protected bool $throws_error,
                protected bool $triggers_error
            ) {
            }

            public function getResponseToRequest(RequestInterface $request): \DomDocument
            {
                if ($this->throws_exception) {
                    throw new \ilMDOERExposerException('exception message');
                }
                if ($this->throws_error) {
                    throw new \Error('thrown error message');
                }
                if ($this->triggers_error) {
                    /** @noinspection PhpExpressionResultUnusedInspection */
                    /** @noinspection PhpDivisionByZeroInspection */
                    1 / 0;
                }

                $url = (string) $request->baseURL();
                $content = $request->exposeContent();
                $doc = new \DOMDocument();
                $doc->appendChild($doc->createElement('content', $url . '~!~' . $content));
                return $doc;
            }
        };
    }

    protected function getHandler(
        string $base_url,
        SettingsInterface $settings,
        HTTPWrapperInterface $http_wrapper,
        RequestParserInterface $request_parser,
        RequestProcessorInterface $request_processor,
    ): Handler {
        $base_url = $this->getURI($base_url);
        return new class (
            $settings,
            $http_wrapper,
            $request_parser,
            $request_processor,
            $base_url
        ) extends Handler {
            public array $exposed_logged_errors = [];

            public function __construct(
                protected SettingsInterface $settings,
                protected HTTPWrapperInterface $http_wrapper,
                protected RequestParserInterface $request_parser,
                protected RequestProcessorInterface $request_processor,
                protected readonly URI $base_url
            ) {
            }

            protected function logError(string $message): void
            {
                $this->exposed_logged_errors[] = $message;
            }
        };
    }

    public function testSendResponseToRequestAvailable(): void
    {
        $handler = $this->getHandler(
            'some url',
            $this->getSettings(true),
            $wrapper = $this->getHTTPWrapper(),
            $this->getRequestParser('some content'),
            $this->getRequestProcessor()
        );

        $handler->sendResponseToRequest();

        $this->assertCount(1, $wrapper->exposed_responses);
        $this->assertEquals(
            ['status' => 200, 'message' => '', 'body' => '<content>some url~!~some content</content>'],
            $wrapper->exposed_responses[0] ?? []
        );
    }

    public function testSendResponseToRequestNotAvailable(): void
    {
        $handler = $this->getHandler(
            'some url',
            $this->getSettings(false),
            $wrapper = $this->getHTTPWrapper(),
            $this->getRequestParser('some content'),
            $this->getRequestProcessor()
        );

        $handler->sendResponseToRequest();

        $this->assertCount(1, $wrapper->exposed_responses);
        $this->assertEquals(
            ['status' => 404, 'message' => '', 'body' => null],
            $wrapper->exposed_responses[0] ?? []
        );
    }

    public function testSendResponseToRequestProcessorThrowsException(): void
    {
        $handler = $this->getHandler(
            'some url',
            $this->getSettings(true),
            $wrapper = $this->getHTTPWrapper(),
            $this->getRequestParser(''),
            $this->getRequestProcessor(true)
        );

        $handler->sendResponseToRequest();

        $this->assertCount(1, $wrapper->exposed_responses);
        $this->assertEquals(
            ['status' => 500, 'message' => 'exception message', 'body' => null],
            $wrapper->exposed_responses[0] ?? []
        );
        $this->assertEquals(
            ['exception message'],
            $handler->exposed_logged_errors
        );
    }

    public function testSendResponseToRequestProcessorThrowsError(): void
    {
        $handler = $this->getHandler(
            'some url',
            $this->getSettings(true),
            $wrapper = $this->getHTTPWrapper(),
            $this->getRequestParser(''),
            $this->getRequestProcessor(false, true)
        );

        $handler->sendResponseToRequest();

        $this->assertCount(1, $wrapper->exposed_responses);
        $this->assertEquals(
            ['status' => 500, 'message' => 'thrown error message', 'body' => null],
            $wrapper->exposed_responses[0] ?? []
        );
        $this->assertEquals(
            ['thrown error message'],
            $handler->exposed_logged_errors
        );
    }

    public function testSendResponseToRequestProcessorTriggersError(): void
    {
        $handler = $this->getHandler(
            'some url',
            $this->getSettings(true),
            $wrapper = $this->getHTTPWrapper(),
            $this->getRequestParser(''),
            $this->getRequestProcessor(false, false, true)
        );

        $handler->sendResponseToRequest();

        $this->assertCount(1, $wrapper->exposed_responses);
        $this->assertEquals(
            ['status' => 500, 'message' => 'Division by zero', 'body' => null],
            $wrapper->exposed_responses[0] ?? []
        );
        $this->assertEquals(
            ['Division by zero'],
            $handler->exposed_logged_errors
        );
    }
}
