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

class ilRTEMediaObjectImageSrcTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        ini_set('pcre.jit', '0');
        ini_set('pcre.backtrack_limit', '1000000');
    }

    public function testReplacesMediaObjectSourceWithoutTouchingOtherImages(): void
    {
        $html = '<p><img src="data:image/png;base64,AAAA"></p><img src="foo.jpg" data-id="42">';

        $result = ilRTE::_replaceMediaObjectImageSrc($html, 0, '7');

        $this->assertSame(
            '<p><img src="data:image/png;base64,AAAA"></p><img src="il_7_mob_42">',
            $result
        );
    }

    public function testKeepsLargeInlineImageWhenBacktrackingIsDisabled(): void
    {
        $payload = str_repeat('A', 750 * 1024 * 4 / 3);
        $html = '<p><img src="data:image/jpeg;base64,' . $payload . '" alt="drop"></p>';

        $result = ilRTE::_replaceMediaObjectImageSrc($html, 0, '7');

        $this->assertIsString($result);
        $this->assertSame($html, $result);
        $this->assertSame(PREG_NO_ERROR, preg_last_error());
    }

    public function testTreatsInstallationIdAsLiteralText(): void
    {
        $html = '<img src="foo.jpg" data-id="42">';

        $result = ilRTE::_replaceMediaObjectImageSrc($html, 0, '1$2');

        $this->assertSame('<img src="il_1$2_mob_42">', $result);
    }
}
