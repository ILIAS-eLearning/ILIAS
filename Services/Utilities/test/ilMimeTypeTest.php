<?php declare(strict_types=1);

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

use PHPUnit\Framework\TestCase;
use ILIAS\FileUpload\MimeType;

class ilMimeTypeTest extends TestCase
{
    public function testMimeTypeForYoutubeUrlCouldBeCorrectlyDetected() : void
    {
        $expected = 'video/youtube';
        $actual = MimeType::lookupMimeType(
            'https://www.youtube.com/watch?v=WSgP85kr6eU',
            MimeType::APPLICATION__OCTET_STREAM
        );

        $this->assertEquals($expected, $actual);
    }


    public function testMimeTypeForVimeoUrlCouldBeCorrectlyDetected() : void
    {
        $expected = 'video/vimeo';
        $actual = MimeType::lookupMimeType(
            'https://vimeo.com/180157999',
            MimeType::APPLICATION__OCTET_STREAM
        );

        $this->assertEquals($expected, $actual);
    }
}
