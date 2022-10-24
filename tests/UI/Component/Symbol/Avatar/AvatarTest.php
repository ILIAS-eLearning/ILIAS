<?php

declare(strict_types=1);

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

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Component\Symbol\Avatar\Factory;
use ILIAS\UI\Implementation as I;

/**
 * Test on avatar implementation.
 */
class AvatarTest extends ILIAS_UI_TestBase
{
    protected const ICON_PATH = __DIR__ . "/../../../../../templates/default/images/";

    private function getAvatarFactory(): Factory
    {
        return new I\Component\Symbol\Avatar\Factory();
    }

    public function testConstruction(): void
    {
        $f = $this->getAvatarFactory();
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Symbol\\Avatar\\Factory", $f);

        $le = $f->letter('ru');
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Symbol\\Avatar\\Letter", $le);

        $ci = $f->picture(self::ICON_PATH . 'no_photo_xsmall.jpg', 'ru');
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Symbol\\Avatar\\Picture", $ci);
    }

    public function testAbbreviation(): void
    {
        $f = $this->getAvatarFactory();

        $this->assertEquals('ro', $f->letter('ro')->getAbbreviation());
        $this->assertEquals('ro', $f->letter('root')->getAbbreviation());
        $this->assertEquals('Ro', $f->letter('Root')->getAbbreviation());
        $this->assertEquals('RO', $f->letter('ROOT')->getAbbreviation());
    }

    public function testUsername(): void
    {
        $f = $this->getAvatarFactory();

        $this->assertEquals('ro', $f->letter('ro')->getUsername());
        $this->assertEquals('ro', $f->picture('', 'ro')->getUsername());
        $this->assertEquals('root', $f->letter('root')->getUsername());
        $this->assertEquals('root', $f->picture('', 'root')->getUsername());
        $this->assertEquals('Root', $f->letter('Root')->getUsername());
        $this->assertEquals('Root', $f->picture('', 'Root')->getUsername());
        $this->assertEquals('ROOT', $f->letter('ROOT')->getUsername());
        $this->assertEquals('ROOT', $f->picture('', 'ROOT')->getUsername());
    }

    public function testPicturePath(): void
    {
        $f = $this->getAvatarFactory();

        $str = '/path/to/picture.jpg';
        $this->assertEquals($str, $f->picture($str, 'ro')->getPicturePath());
    }

    public function testColorVariant(): void
    {
        $f = $this->getAvatarFactory();

        // Test all 26 colors
        $variants = array(
            1 => 'om',
            2 => 'gk',
            3 => 'bj',
            4 => 'ea',
            5 => 'mf',
            6 => 'ob',
            7 => 'bi',
            8 => 'hu',
            9 => 'fa',
            10 => 'so',
            11 => 'il',
            12 => 'ut',
            13 => 'ur',
            14 => 'lt',
            15 => 'kg',
            16 => 'jl',
            17 => 'qb',
            18 => 'rq',
            19 => 'ot',
            20 => 'cq',
            21 => 'rm',
            22 => 'aj',
            23 => 'li',
            24 => 'er',
            25 => 'ui',
            26 => 'mi',
        );
        foreach ($variants as $color => $variant) {
            $this->assertEquals($color, $f->letter($variant)->getBackgroundColorVariant());
        }
    }

    public function testCrc32(): void
    {
        // test mechanism (crc32)
        $f = $this->getAvatarFactory();
        $number_of_colors = 26;
        $abb = 'ru';

        $calculated_color_variant = (crc32($abb) % $number_of_colors) + 1; // plus 1 since colors start with 1
        $this->assertEquals($calculated_color_variant, $f->letter($abb)->getBackgroundColorVariant());

        // test with random abbreviations (dynamically generated)

        foreach ($this->getRandom26StringsForAllColorVariants() as $color => $variant) {
            $this->assertEquals($color, $f->letter($variant)->getBackgroundColorVariant());
        }
    }

    public function testAlternativeText(): void
    {
        $f = $this->getAvatarFactory();
        $this->assertEquals("", $f->picture('', 'ro')->getLabel());
        $this->assertEquals("", $f->letter('', 'ro')->getLabel());
        $this->assertEquals("alternative", $f->picture('', 'ro')
                                             ->withLabel("alternative")
                                             ->getLabel());
        $this->assertEquals("alternative", $f->letter('', 'ro')
                                             ->withLabel("alternative")
                                             ->getLabel());
    }

    public function testRenderingLetter(): void
    {
        $f = $this->getAvatarFactory();
        $r = $this->getDefaultRenderer();

        $letter = $f->letter('ro');
        $html = $this->brutallyTrimHTML($r->render($letter));
        $expected = $this->brutallyTrimHTML('
<span class="il-avatar il-avatar-letter il-avatar-size-large il-avatar-letter-color-1" aria-label="user_avatar" role="img">	
    <span class="abbreviation">ro</span>
</span>');
        $this->assertEquals($expected, $html);
    }

    public function testRenderingPicture(): void
    {
        $f = $this->getAvatarFactory();
        $r = $this->getDefaultRenderer();

        $str = '/path/to/picture.jpg';
        $letter = $f->picture($str, 'ro');
        $html = $this->brutallyTrimHTML($r->render($letter));
        $expected = $this->brutallyTrimHTML('
<span class="il-avatar il-avatar-picture il-avatar-size-large">	
    <img src="/path/to/picture.jpg" alt="user_avatar"/>
</span>');
        $this->assertEquals($expected, $html);
    }

    public function testRenderingPictureWithSomeAlternativeText(): void
    {
        $f = $this->getAvatarFactory();
        $r = $this->getDefaultRenderer();

        $str = '/path/to/picture.jpg';
        $letter = $f->picture($str, 'ro')->withLabel("alternative");
        $html = $this->brutallyTrimHTML($r->render($letter));
        $expected = $this->brutallyTrimHTML('
<span class="il-avatar il-avatar-picture il-avatar-size-large">	
    <img src="/path/to/picture.jpg" alt="alternative"/>
</span>');
        $this->assertEquals($expected, $html);
    }
    /**
     * @param int $color_variants
     * @param int $length
     * @return Generator|Closure
     */
    public function getRandom26StringsForAllColorVariants(int $color_variants = 26, int $length = 2): Generator
    {
        $sh = static function ($length = 10) {
            return substr(
                str_shuffle(str_repeat(
                    $x = 'abcdefghijklmnopqrstuvwxyz',
                    (int) ceil($length / strlen($x))
                )),
                1,
                $length
            );
        };

        $strings = [];
        $running = true;
        while ($running) {
            $str = $sh($length);
            $probe = crc32($str);
            $i = ($probe % $color_variants) + 1;
            if (!in_array($i, $strings, true)) {
                $strings[$i] = $str;
                yield $i => $str;
            }
            if (count($strings) === $color_variants) {
                $running = false;
            }
        }
    }
}
