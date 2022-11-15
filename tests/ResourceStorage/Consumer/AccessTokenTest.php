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

namespace ILIAS\ResourceStorage\Flavours;

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\AbstractBaseTest;
use ILIAS\ResourceStorage\Consumer\StreamAccess\Token;
use ILIAS\ResourceStorage\Consumer\StreamAccess\TokenFactory;

/**
 * Class FlavourMachineTest
 * @author Fabian Schmid <fabian@sr.solutions>
 */
require_once __DIR__ . '/../AbstractBaseTest.php';

class AccessTokenTest extends AbstractBaseTest
{
    private const TEST_SVG = 'test.svg';
    private const INSIDE = __DIR__ . '/files/inside/';
    private const OUTSIDE = __DIR__ . '/files/outside/';
    private const INSIDE_FILE = self::INSIDE . self::TEST_SVG;
    private const OUTSIDE_FILE = self::OUTSIDE . self::TEST_SVG;

    public function testMemoryStream(): void
    {
        $tof = new TokenFactory(__DIR__);

        $file_stream = Streams::ofString('test');
        $token = $tof->lease($file_stream);

        $this->assertFalse($token->hasStreamableStream());
        $this->assertTrue($token->hasInMemoryStream());

        $token_stream = $token->resolveStream();

        $this->assertFalse($token->hasStreamableStream());

        $this->assertTrue($token_stream->isReadable());
        $this->assertFalse($token_stream->isWritable());
        $this->assertEquals('application/x-empty', $token_stream->getMimeType());
    }

    public function testRealStream(): void
    {
        $tof = new TokenFactory(__DIR__);

        $file_stream = Streams::ofResource(fopen(self::INSIDE_FILE, 'rb'));

        $token = $tof->lease($file_stream);
        $this->assertFalse($token->hasStreamableStream());
        $this->assertFalse($token->hasInMemoryStream());

        $token_stream = $token->resolveStream();

        $this->assertTrue($token->hasStreamableStream());
        $this->assertFalse($token->hasInMemoryStream());

        $this->assertTrue($token_stream->isReadable());
        $this->assertFalse($token_stream->isWritable());
        $this->assertEquals('image/svg+xml', $token_stream->getMimeType());

        $token2 = $tof->lease($file_stream, true);
        $this->assertTrue($token->hasStreamableStream());
        $this->assertFalse($token->hasInMemoryStream());
    }


    public function testStreamAccessInfoWrongDirectory(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $sif = new TokenFactory('/loremipsum/');
    }

    public function testStreamAccessInfoOutsideDirectory(): void
    {
        $tof = new TokenFactory(self::INSIDE);
        $file_stream_outside = Streams::ofResource(fopen(self::OUTSIDE_FILE, 'rb'));
        $file_stream_inside = Streams::ofResource(fopen(self::INSIDE_FILE, 'rb'));
        $this->assertInstanceOf(Token::class, $tof->lease($file_stream_inside));
        $this->expectException(\InvalidArgumentException::class);
        $tof->lease($file_stream_outside);
    }
}
