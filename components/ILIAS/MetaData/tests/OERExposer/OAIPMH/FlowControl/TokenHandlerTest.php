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

namespace ILIAS\MetaData\OERExposer\OAIPMH\FlowControl;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\RequestInterface;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\NullRequest;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\Argument;

class TokenHandlerTest extends TestCase
{
    protected function getDate(string $string): \DateTimeImmutable
    {
        return new \DateTimeImmutable($string, new \DateTimeZone('UTC'));
    }

    protected function getTokenHandler(\DateTimeImmutable $current_date): TokenHandler
    {
        return new class ($current_date) extends TokenHandler {
            public function __construct(protected \DateTimeImmutable $current_date)
            {
            }

            protected function getCurrentDate(): \DateTimeImmutable
            {
                return $this->current_date;
            }
        };
    }

    protected function getRequest(): RequestInterface
    {
        return new class () extends NullRequest {
            public ?string $exposed_from_date = null;
            public ?string $exposed_until_date = null;

            public function withArgument(
                Argument $key,
                string $value
            ): RequestInterface {
                $clone = clone $this;
                switch ($key) {
                    case Argument::FROM_DATE:
                        $clone->exposed_from_date = $value;
                        return $clone;

                    case Argument::UNTIL_DATE:
                        $clone->exposed_until_date = $value;
                        return $clone;

                    default:
                        throw new \ilMDOERExposerException('Argument not covered in mock.');
                }
            }
        };
    }

    public function testTokenGenerateAndReadOutOnlyOffset(): void
    {
        $handler = $this->getTokenHandler($this->getDate('2022-10-30'));

        $token = $handler->generateToken(32, null, null);
        $offset_from_token = $handler->getOffsetFromToken($token);
        $request_from_token = $handler->appendArgumentsFromTokenToRequest($this->getRequest(), $token);

        $this->assertTrue($handler->isTokenValid($token));
        $this->assertSame(32, $offset_from_token);
        $this->assertNull($request_from_token->exposed_from_date);
        $this->assertSame('2022-10-30', $request_from_token->exposed_until_date);
    }

    public function testTokenGenerateAndReadOutWithFromDate(): void
    {
        $handler = $this->getTokenHandler($this->getDate('2022-10-30'));

        $token = $handler->generateToken(
            32,
            $this->getDate('2021-10-30'),
            null
        );
        $offset_from_token = $handler->getOffsetFromToken($token);
        $request_from_token = $handler->appendArgumentsFromTokenToRequest($this->getRequest(), $token);

        $this->assertTrue($handler->isTokenValid($token));
        $this->assertSame(32, $offset_from_token);
        $this->assertSame('2021-10-30', $request_from_token->exposed_from_date);
        $this->assertSame('2022-10-30', $request_from_token->exposed_until_date);
    }

    public function testTokenGenerateAndReadOutWithUntilDate(): void
    {
        $handler = $this->getTokenHandler($this->getDate('2022-10-30'));

        $token = $handler->generateToken(
            32,
            null,
            $this->getDate('2022-09-30')
        );
        $offset_from_token = $handler->getOffsetFromToken($token);
        $request_from_token = $handler->appendArgumentsFromTokenToRequest($this->getRequest(), $token);

        $this->assertTrue($handler->isTokenValid($token));
        $this->assertSame(32, $offset_from_token);
        $this->assertNull($request_from_token->exposed_from_date);
        $this->assertSame('2022-09-30', $request_from_token->exposed_until_date);
    }

    public function testTokenGenerateAndReadOutWithUntilDateInTheFuture(): void
    {
        $handler = $this->getTokenHandler($this->getDate('2022-10-30'));

        $token = $handler->generateToken(
            32,
            null,
            $this->getDate('2022-11-30')
        );
        $offset_from_token = $handler->getOffsetFromToken($token);
        $request_from_token = $handler->appendArgumentsFromTokenToRequest($this->getRequest(), $token);

        $this->assertTrue($handler->isTokenValid($token));
        $this->assertSame(32, $offset_from_token);
        $this->assertNull($request_from_token->exposed_from_date);
        $this->assertSame('2022-10-30', $request_from_token->exposed_until_date);
    }

    public function testTokenGenerateAndReadOutWithBothDates(): void
    {
        $handler = $this->getTokenHandler($this->getDate('2022-10-30'));

        $token = $handler->generateToken(
            32,
            $this->getDate('2023-10-30'),
            $this->getDate('2021-10-30'),
        );
        $offset_from_token = $handler->getOffsetFromToken($token);
        $request_from_token = $handler->appendArgumentsFromTokenToRequest($this->getRequest(), $token);

        $this->assertTrue($handler->isTokenValid($token));
        $this->assertSame(32, $offset_from_token);
        $this->assertSame('2023-10-30', $request_from_token->exposed_from_date);
        $this->assertSame('2021-10-30', $request_from_token->exposed_until_date);
    }

    public function testIsTokenValidNonsenseString(): void
    {
        $handler = $this->getTokenHandler($this->getDate('2022-10-30'));

        $this->assertFalse($handler->isTokenValid('sadadsadsgr'));
    }

    public function testIsTokenValidNonsenseAppended(): void
    {
        $handler = $this->getTokenHandler($this->getDate('2022-10-30'));
        $token = $handler->generateToken(
            32,
            $this->getDate('2023-10-30'),
            $this->getDate('2021-10-30'),
        );

        $this->assertFalse($handler->isTokenValid($token . 'sadadsadsgr'));
    }

    public function testIsTokenValidEmptyArray(): void
    {
        $handler = $this->getTokenHandler($this->getDate('2022-10-30'));

        $this->assertFalse($handler->isTokenValid(base64_encode(json_encode([]))));
    }

    public function testIsTokenValidNoUntilDate(): void
    {
        $handler = $this->getTokenHandler($this->getDate('2022-10-30'));

        $this->assertFalse($handler->isTokenValid(base64_encode(json_encode([5]))));
    }

    public function testIsTokenValidTooManyDates(): void
    {
        $handler = $this->getTokenHandler($this->getDate('2022-10-30'));

        $this->assertFalse(
            $handler->isTokenValid(
                base64_encode(json_encode([5, '2023-10-30', '2021-10-30', '2051-10-30']))
            )
        );
    }

    public function testIsTokenValidNoNonNumericOffset(): void
    {
        $handler = $this->getTokenHandler($this->getDate('2022-10-30'));

        $this->assertFalse(
            $handler->isTokenValid(
                base64_encode(json_encode(['asdsasd', '2023-10-30']))
            )
        );
    }

    public function testIsTokenValidInvalidDate(): void
    {
        $handler = $this->getTokenHandler($this->getDate('2022-10-30'));

        $this->assertFalse(
            $handler->isTokenValid(
                base64_encode(json_encode([5, '2023-99-99']))
            )
        );
    }
}
