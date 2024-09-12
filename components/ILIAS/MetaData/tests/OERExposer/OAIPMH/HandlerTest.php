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

    protected function getInitiator(
        bool $activated,
        string $content,
        bool $processor_throws_exception = false,
        bool $processor_throws_error = false,
        bool $processor_triggers_error = false
    ): InitiatorInterface {
        return new class (
            $activated,
            $content,
            $processor_throws_exception,
            $processor_throws_error,
            $processor_triggers_error
        ) extends NullInitiator {
            protected HTTPWrapperInterface $wrapper;

            public function __construct(
                protected bool $activated,
                protected string $content,
                protected bool $processor_throws_exception,
                protected bool $processor_throws_error,
                protected bool $processor_triggers_error
            ) {
                $this->wrapper = new class () extends NullWrapper {
                    public array $exposed_responses = [];

                    public function sendResponseAndClose(
                        int $status_code,
                        string $message = '',
                        \DOMDocument $body = null
                    ): void {
                        $this->exposed_responses[] = [
                            'status' => $status_code,
                            'message' => $message,
                            'body' => $body?->saveXML($body->documentElement)
                        ];
                    }
                };
            }

            public function settings(): SettingsInterface
            {
                return new class ($this->activated) extends NullSettings {
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

            public function requestParser(): RequestParserInterface
            {
                return new class ($this->content) extends NullParser {
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

            public function requestProcessor(): RequestProcessorInterface
            {
                return new class (
                    $this->processor_throws_exception,
                    $this->processor_throws_error,
                    $this->processor_triggers_error
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
                            trigger_error('triggered error message', E_USER_ERROR);
                        }

                        $url = (string) $request->baseURL();
                        $content = $request->exposeContent();
                        $doc = new \DOMDocument();
                        $doc->appendChild($doc->createElement('content', $url . '~!~' . $content));
                        return $doc;
                    }
                };
            }

            public function httpWrapper(): HTTPWrapperInterface
            {
                // must always be the same instance to make the expose work
                return $this->wrapper;
            }

            public function exposeHTTPResponses(): array
            {
                return $this->httpWrapper()->exposed_responses;
            }
        };
    }

    protected function getHandler(
        string $base_url,
        InitiatorInterface $initiator
    ): Handler {
        $base_url = $this->getURI($base_url);
        return new class ($initiator, $base_url) extends Handler {
            public array $exposed_logged_errors = [];

            public function __construct(
                protected InitiatorInterface $initiator,
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
        $initiator = $this->getInitiator(
            true,
            'some content'
        );
        $handler = $this->getHandler(
            'some url',
            $initiator
        );

        $handler->sendResponseToRequest();

        $this->assertCount(1, $initiator->exposeHTTPResponses());
        $this->assertEquals(
            ['status' => 200, 'message' => '', 'body' => '<content>some url~!~some content</content>'],
            $initiator->exposeHTTPResponses()[0] ?? []
        );
    }

    public function testSendResponseToRequestNotAvailable(): void
    {
        $initiator = $this->getInitiator(
            false,
            'some content'
        );
        $handler = $this->getHandler(
            'some url',
            $initiator
        );

        $handler->sendResponseToRequest();

        $this->assertCount(1, $initiator->exposeHTTPResponses());
        $this->assertEquals(
            ['status' => 404, 'message' => '', 'body' => null],
            $initiator->exposeHTTPResponses()[0] ?? []
        );
    }

    public function testSendResponseToRequestProcessorThrowsException(): void
    {
        $initiator = $this->getInitiator(
            true,
            '',
            true,
            false,
            false
        );
        $handler = $this->getHandler(
            'some url',
            $initiator
        );

        $handler->sendResponseToRequest();

        $this->assertCount(1, $initiator->exposeHTTPResponses());
        $this->assertEquals(
            ['status' => 500, 'message' => 'exception message', 'body' => null],
            $initiator->exposeHTTPResponses()[0] ?? []
        );
        $this->assertEquals(
            ['exception message'],
            $handler->exposed_logged_errors
        );
    }

    public function testSendResponseToRequestProcessorThrowsError(): void
    {
        $initiator = $this->getInitiator(
            true,
            '',
            false,
            true,
            false
        );
        $handler = $this->getHandler(
            'some url',
            $initiator
        );

        $handler->sendResponseToRequest();

        $this->assertCount(1, $initiator->exposeHTTPResponses());
        $this->assertEquals(
            ['status' => 500, 'message' => 'thrown error message', 'body' => null],
            $initiator->exposeHTTPResponses()[0] ?? []
        );
        $this->assertEquals(
            ['thrown error message'],
            $handler->exposed_logged_errors
        );
    }

    public function testSendResponseToRequestProcessorTriggersError(): void
    {
        $initiator = $this->getInitiator(
            true,
            '',
            false,
            false,
            true
        );
        $handler = $this->getHandler(
            'some url',
            $initiator
        );

        $handler->sendResponseToRequest();

        $this->assertCount(1, $initiator->exposeHTTPResponses());
        $this->assertEquals(
            ['status' => 500, 'message' => 'triggered error message', 'body' => null],
            $initiator->exposeHTTPResponses()[0] ?? []
        );
        $this->assertEquals(
            ['triggered error message'],
            $handler->exposed_logged_errors
        );
    }
}
