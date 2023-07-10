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

namespace ILIAS\ResourceStorage\Consumer;

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\AbstractBaseTest;
use ILIAS\ResourceStorage\Consumer\StreamAccess\AccessToken;
use ILIAS\ResourceStorage\Consumer\StreamAccess\Packaging;
use ILIAS\ResourceStorage\Consumer\StreamAccess\Token;
use ILIAS\ResourceStorage\Consumer\StreamAccess\TokenFactory;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

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

        $rid = new ResourceIdentification('unique_id');

        $file_stream = Streams::ofString('test');
        $token = $tof->lease($file_stream, $rid);

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

        $rid = new ResourceIdentification('unique_id');

        $file_stream = Streams::ofResource(fopen(self::INSIDE_FILE, 'rb'));

        $token = $tof->lease($file_stream, $rid);
        $this->assertFalse($token->hasStreamableStream());
        $this->assertFalse($token->hasInMemoryStream());

        $token_stream = $token->resolveStream();

        $this->assertTrue($token->hasStreamableStream());
        $this->assertFalse($token->hasInMemoryStream());

        $this->assertTrue($token_stream->isReadable());
        $this->assertFalse($token_stream->isWritable());
        $this->assertEquals('image/svg+xml', $token_stream->getMimeType());

        $token2 = $tof->lease($file_stream, $rid, true);
        $this->assertTrue($token->hasStreamableStream());
        $this->assertFalse($token->hasInMemoryStream());
    }


    public function testStreamAccessInfoWrongDirectory(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $sif = new TokenFactory('/loremipsum/');
        $sif->check('loremipsum');
    }

    public function testStreamAccessInfoOutsideDirectory(): void
    {
        $tof = new TokenFactory(self::INSIDE);
        $rid = new ResourceIdentification('unique_id');
        $file_stream_outside = Streams::ofResource(fopen(self::OUTSIDE_FILE, 'rb'));
        $file_stream_inside = Streams::ofResource(fopen(self::INSIDE_FILE, 'rb'));
        $this->assertInstanceOf(Token::class, $tof->lease($file_stream_inside, $rid));
        $this->expectException(\InvalidArgumentException::class);
        $tof->lease($file_stream_outside, $rid);
    }

    public function testViceVersa(): void
    {
        $this>self::markTestSkipped('This test is currently skipped, because there are inexplicable differences in comparing the hases locally of in github...');
        return;

        $token = new AccessToken(
            42,
            new \DateTimeImmutable('2022-11-21'),
            new ResourceIdentification('unique_id'),
            self::INSIDE_FILE
        );
        $str = 'eyJsYiI6NDIsImxhIjoiMjAyMi0xMS0yMSAwMDowMDowMCIsInIiOiJ1bmlxdWVfaWQiLCJ1IjoiXC9Vc2Vyc1wvZnNjaG1pZFwvRGV2ZWxvcG1lbnRcL0lMSUFTXC9Db3JlXC90cnVua1wvdGVzdHNcL1Jlc291cmNlU3RvcmFnZVwvQ29uc3VtZXJcL2ZpbGVzXC9pbnNpZGVcL3Rlc3Quc3ZnIiwidCI6InJpZCJ9';
        $this->assertEquals($str, $token->pack());

        $new_token = new AccessToken(
            1,
            new \DateTimeImmutable(),
            new ResourceIdentification('unique_id_2'),
            self::OUTSIDE_FILE
        );
        $new_token->unpack($str);

        $this->assertEquals($str, $new_token->pack());
        $this->assertEquals('2022-11-21', $new_token->leasedAt()->format('Y-m-d'));
        $this->assertEquals('unique_id', $new_token->leasedForRid()->serialize());
        $this->assertEquals(42, $new_token->leasedBy());

        $packaging = new Packaging();

        $new_token_2 = $packaging->unpack($str);

        $this->assertEquals($str, $new_token_2->pack());
        $this->assertEquals('2022-11-21', $new_token_2->leasedAt()->format('Y-m-d'));
        $this->assertEquals('unique_id', $new_token_2->leasedForRid()->serialize());
        $this->assertEquals(42, $new_token_2->leasedBy());
    }
}
