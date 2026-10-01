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

use PHPUnit\Framework\TestCase;

class ilQTIMatImageSecurityTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            ilQtiMatImageSecurity::class,
            new ilQtiMatImageSecurity(
                $this->image(),
                $this->createMock(\ILIAS\TestQuestionPool\Questions\Files\QuestionFiles::class)
            )
        );
    }


    /**
     * @dataProvider validateProvider
     */
    public function testValidate(string $label, string $uri, ?string $embedded, string $content, bool $expected): void
    {
        $image = new ilQTIMatimage();
        $image->setLabel($label);
        $image->setUri($uri);
        if ($embedded !== null) {
            $image->setEmbedded($embedded);
        }
        $image->setContent($content);

        $security = new ilQtiMatImageSecurity($image, new \ILIAS\TestQuestionPool\Questions\Files\QuestionFiles());

        $this->assertSame($expected, $security->validate());
    }

    public static function validateProvider(): array
    {
        $gif = "GIF89a\x01\x00\x01\x00\x80\x00\x00\xff\xff\xff\x00\x00\x00,\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x02D\x01\x00;";
        $encoded = base64_encode($gif);

        return [
            'question image with matching extension' => ['bild.gif', '', ilQTIMatimage::EMBEDDED_BASE64, $encoded, true],
            'reported bypass via extension-less uri' => ['shell.php', 'x', ilQTIMatimage::EMBEDDED_BASE64, $encoded, false],
            'htaccess label' => ['.htaccess', 'x', ilQTIMatimage::EMBEDDED_BASE64, $encoded, false],
            'phar label despite image uri' => ['shell.phar', 'x.gif', ilQTIMatimage::EMBEDDED_BASE64, $encoded, false],
            'raw content is not the stored bytes' => ['bild.gif', '', null, $gif, false],
            'media object from an ilias export' => ['il_0_mob_1', 'objects/il_0_mob_1/bild.gif', null, $gif, true],
            'media object uri with executable extension' => ['il_0_mob_1', 'objects/x.phar', null, $gif, false],
            'media object uri leaving the import archive' => ['il_0_mob_1', '../../client.ini.php', null, $gif, false],
            'media object uri with a parent segment' => ['il_0_mob_1', 'objects/../../secret.gif', null, $gif, false],
            'suffixed label is not a media object' => ['il_0_mob_1.php', 'objects/x.gif', ilQTIMatimage::EMBEDDED_BASE64, $encoded, false],
        ];
    }

    private function image(): ilQTIMatimage
    {
        $image = $this->getMockBuilder(ilQTIMatimage::class)->disableOriginalConstructor()->getMock();
        $image->expects(self::exactly(2))->method('getRawContent')->willReturn('Ayayay');

        return $image;
    }
}
