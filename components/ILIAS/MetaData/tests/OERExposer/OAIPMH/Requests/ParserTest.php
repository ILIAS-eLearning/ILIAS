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

namespace ILIAS\MetaData\OERExposer\OAIPMH\Requests;

use PHPUnit\Framework\TestCase;
use ILIAS\Data\URI;
use ILIAS\MetaData\OERExposer\OAIPMH\HTTP\NullWrapper;
use ILIAS\MetaData\OERExposer\OAIPMH\HTTP\WrapperInterface;

class ParserTest extends TestCase
{
    protected function getURI(string $string): URI
    {
        $url = $this->createMock(URI::class);
        $url->method('__toString')->willReturn($string);
        return $url;
    }

    /**
     * If verb should be found, also pass it explicitely as an argument.
     * Argument names are keys, their values values (all as strings)
     */
    protected function getHTTPWrapper(
        array $arguments_with_values
    ): WrapperInterface {
        return new class ($arguments_with_values) extends NullWrapper {
            public function __construct(
                protected array $arguments_with_values
            ) {
            }

            public function requestHasArgument(Argument $argument): bool
            {
                return in_array(
                    $argument->value,
                    array_keys($this->arguments_with_values)
                );
            }

            public function retrieveArgumentFromRequest(Argument $argument): string
            {
                if (!$this->requestHasArgument($argument)) {
                    return '';
                }
                return $this->arguments_with_values[$argument->value];
            }
        };
    }

    public function getParser(
        WrapperInterface $wrapper
    ): Parser {
        return new class ($wrapper) extends Parser {
            protected function getEmptyRequest(
                Verb $verb,
                URI $base_url
            ): RequestInterface {
                return new class ($verb, $base_url) extends NullRequest {
                    public array $exposed_arguments = [];

                    public function __construct(
                        public Verb $exposed_verb,
                        public URI $exposed_base_url
                    ) {
                    }

                    public function withArgument(
                        Argument $key,
                        string $value
                    ): RequestInterface {
                        $clone = clone $this;
                        $clone->exposed_arguments[$key->value] = $value;
                        return $clone;
                    }
                };
            }
        };
    }

    public function testParseFromHTTP(): void
    {
        $parser = $this->getParser($this->getHTTPWrapper(
            [
                Argument::IDENTIFIER->value => 'some identifier',
                Argument::VERB->value => Verb::LIST_IDENTIFIERS->value,
                Argument::FROM_DATE->value => 'some date'
            ]
        ));

        $request = $parser->parseFromHTTP($this->getURI('some url'));

        $this->assertSame('some url', (string) $request->exposed_base_url);
        $this->assertSame(Verb::LIST_IDENTIFIERS, $request->exposed_verb);
        $this->assertEquals(
            [
                Argument::IDENTIFIER->value => 'some identifier',
                Argument::FROM_DATE->value => 'some date'
            ],
            $request->exposed_arguments
        );
    }

    public function testParseFromHTTPWithEncodedCharacters(): void
    {
        $string_with_reserved_chars = ':/?#[]@!$&' . "'" . ' ()*+,;=';
        $parser = $this->getParser($this->getHTTPWrapper(
            [
                Argument::IDENTIFIER->value => rawurlencode($string_with_reserved_chars)
            ]
        ));

        $request = $parser->parseFromHTTP($this->getURI('some url'));

        $this->assertEquals(
            [Argument::IDENTIFIER->value => $string_with_reserved_chars],
            $request->exposed_arguments
        );
    }

    public function testParseFromHTTPNoArgumentsNoVerb(): void
    {
        $parser = $this->getParser($this->getHTTPWrapper([]));

        $request = $parser->parseFromHTTP($this->getURI('some url'));

        $this->assertSame(Verb::NULL, $request->exposed_verb);
        $this->assertEmpty($request->exposed_arguments);
    }

    public function testParseFromHTTPNoArguments(): void
    {
        $parser = $this->getParser($this->getHTTPWrapper(
            [Argument::VERB->value => Verb::LIST_IDENTIFIERS->value,]
        ));

        $request = $parser->parseFromHTTP($this->getURI('some url'));

        $this->assertEmpty($request->exposed_arguments);
    }

    public function testParseFromHTTPNoVerb(): void
    {
        $parser = $this->getParser($this->getHTTPWrapper(
            [Argument::IDENTIFIER->value => 'some identifier']
        ));

        $request = $parser->parseFromHTTP($this->getURI('some url'));

        $this->assertSame(Verb::NULL, $request->exposed_verb);
    }

    public function testParseFromHTTPInvalidVerb(): void
    {
        $parser = $this->getParser($this->getHTTPWrapper(
            [Argument::VERB->value => 'nonsense verb']
        ));

        $request = $parser->parseFromHTTP($this->getURI('some url'));

        $this->assertSame(Verb::NULL, $request->exposed_verb);
        $this->assertEmpty($request->exposed_arguments);
    }

    public function testParseFromHTTPInvalidArgument(): void
    {
        $parser = $this->getParser($this->getHTTPWrapper(
            ['nonsense argument' => 'nonsense verb']
        ));

        $request = $parser->parseFromHTTP($this->getURI('some url'));

        $this->assertSame(Verb::NULL, $request->exposed_verb);
        $this->assertEmpty($request->exposed_arguments);
    }
}
