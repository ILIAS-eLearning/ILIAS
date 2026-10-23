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

use ILIAS\DI\Container;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTIToolTest extends TestCase
{
    public static function keywordProvider(): array
    {
        return [
            'list' => ['quiz;video', ['quiz', 'video']],
            'spaces and empty entries' => [' quiz ; ;video;', ['quiz', 'video']],
            'empty' => ['', []],
        ];
    }

    #[DataProvider('keywordProvider')]
    public function testSplitKeywords(string $keywords, array $expected): void
    {
        $this->assertSame($expected, ilLTITool::splitKeywords($keywords));
    }

    public static function customParamsProvider(): array
    {
        return [
            'pairs' => ['color=blue;size=10', ['color' => 'blue', 'size' => '10']],
            'space after the separator' => ['color=blue; size=10', ['color' => 'blue', 'size' => '10']],
            'equals sign in the value' => ['filter=a=b', ['filter' => 'a=b']],
            'name without value' => ['debug', ['debug' => '']],
            'empty' => ['', []],
        ];
    }

    #[DataProvider('customParamsProvider')]
    public function testParseCustomParams(string $params, array $expected): void
    {
        $this->assertSame($expected, ilObjLTITool::parseCustomParams($params));
    }

    public function testNewToolHasDefaults(): void
    {
        $tool = new ilLTITool();

        $this->assertSame(0, $tool->getId());
        $this->assertSame(ilLTITool::VERSION_1P1, $tool->getLtiVersion());
        $this->assertSame(ilLTITool::AVAILABILITY_NONE, $tool->getAvailability());
        $this->assertSame(0.8, $tool->getMasteryScore());
        $this->assertTrue($tool->isKeyCustomizable());
        $this->assertSame([], $tool->getCustomParamsArray());
        $this->assertFalse($tool->isSelectableBy(0));
    }

    public function testToolIsReadFromItsRow(): void
    {
        $tool = $this->readTool([
            'title' => 'Video tool',
            'creator' => 6,
            'keywords' => 'video;media',
            'provider_url' => 'https://tool.example.org/launch',
            'custom_params' => 'color=blue',
            'mastery_score' => '0.5',
            'provider_key' => 'key1',
            'provider_secret' => 'secret1',
            'provider_key_customizable' => '0',
            'xapi_launch_url' => 'https://lrs.example.org/data/xAPI',
        ]);

        $this->assertSame(7, $tool->getId());
        $this->assertSame('Video tool', $tool->getTitle());
        $this->assertTrue($tool->isOwnedBy(6));
        $this->assertSame(['video', 'media'], $tool->getKeywords());
        $this->assertSame('https://tool.example.org/launch', $tool->getUrl());
        $this->assertSame(['color' => 'blue'], $tool->getCustomParamsArray());
        $this->assertSame(0.5, $tool->getMasteryScore());
        $this->assertSame('key1', $tool->getKey());
        $this->assertSame('secret1', $tool->getSecret());
        $this->assertFalse($tool->isKeyCustomizable());
        $this->assertSame('https://lrs.example.org/api/statements/aggregate', $tool->getXapiAggregateEndpoint());
    }

    public function testRedirectionUrisAreSplit(): void
    {
        $tool = $this->readTool([
            'redirection_uris' => 'https://tool.example.org/launch, https://tool.example.org/deep,',
        ]);

        $this->assertSame(
            ['https://tool.example.org/launch', 'https://tool.example.org/deep'],
            $tool->getRedirectionUris()
        );
    }

    public static function keyTypeProvider(): array
    {
        return [
            'PEM key' => [ilLTITool::KEY_TYPE_RSA, 'PEM', ''],
            'key set' => [ilLTITool::KEY_TYPE_JWK, '', 'https://tool.example.org/jwks'],
            'no key type with a key set' => ['', '', 'https://tool.example.org/jwks'],
            'no key type with a PEM key' => ['', 'PEM', ''],
        ];
    }

    #[DataProvider('keyTypeProvider')]
    public function testOnlyTheKeyOfItsTypeIsUsed(string $key_type, string $public_key, string $public_keyset): void
    {
        $tool = $this->readTool([
            'key_type' => $key_type,
            'public_key' => 'PEM',
            'public_keyset' => $public_keyset === '' ? '' : 'https://tool.example.org/jwks',
        ]);

        $this->assertSame($public_key, $tool->getPublicKey());
        $this->assertSame($public_keyset, $tool->getPublicKeysetUrl());
    }

    public static function deepLinkingProvider(): array
    {
        return [
            'LTI Advantage with a URL' => [ilLTITool::VERSION_ADVANTAGE, true, 'https://tool.example.org/deep', true],
            'LTI Advantage without a URL' => [ilLTITool::VERSION_ADVANTAGE, true, '', false],
            'LTI Advantage switched off' => [ilLTITool::VERSION_ADVANTAGE, false, 'https://tool.example.org/deep', false],
            'LTI 1.1' => [ilLTITool::VERSION_1P1, true, 'https://tool.example.org/deep', false],
        ];
    }

    #[DataProvider('deepLinkingProvider')]
    public function testOffersDeepLinking(string $version, bool $content_item, string $url, bool $expected): void
    {
        $tool = $this->readTool([
            'lti_version' => $version,
            'content_item' => $content_item ? '1' : '0',
            'content_item_url' => $url,
        ]);

        $this->assertSame($expected, $tool->offersDeepLinking());
    }

    public static function selectableProvider(): array
    {
        return [
            'global tool to create' => [ilLTITool::AVAILABILITY_CREATE, true, 5, true],
            'own tool to create' => [ilLTITool::AVAILABILITY_CREATE, false, 6, true],
            'tool of another user' => [ilLTITool::AVAILABILITY_CREATE, false, 5, false],
            'global tool for existing objects only' => [ilLTITool::AVAILABILITY_EXISTING, true, 5, false],
            'global tool not available' => [ilLTITool::AVAILABILITY_NONE, true, 6, false],
        ];
    }

    #[DataProvider('selectableProvider')]
    public function testIsSelectableBy(int $availability, bool $global, int $user_id, bool $expected): void
    {
        $tool = $this->readTool([
            'availability' => (string) $availability,
            'global' => $global ? '1' : '0',
            'creator' => '6',
        ]);

        $this->assertSame($expected, $tool->isSelectableBy($user_id));
    }

    private function readTool(array $row): ilLTITool
    {
        $statement = $this->createStub(ilDBStatement::class);
        $db = $this->createMock(ilDBInterface::class);
        $db->expects($this->once())
           ->method('quote')
           ->with(7, 'integer')
           ->willReturn('7');
        $db->expects($this->once())
           ->method('query')
           ->with($this->stringContains(ilLTITool::TABLE_NAME))
           ->willReturn($statement);
        $db->expects($this->once())
           ->method('fetchAssoc')
           ->with($statement)
           ->willReturn(['id' => '7'] + $row);

        $dic = new Container();
        $dic['ilDB'] = static fn(): ilDBInterface => $db;
        $GLOBALS['DIC'] = $dic;

        return new ilLTITool(7);
    }
}
