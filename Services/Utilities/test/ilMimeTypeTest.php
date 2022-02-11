<?php
use PHPUnit\Framework\TestCase;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

class ilMimeTypeTest extends TestCase
{

    public function testMimeTypeForYoutubeUrlCouldBeCorrectlyDetected() : void
    {
        $expected = 'video/youtube';
        $actual = \ILIAS\FileUpload\MimeType::lookupMimeType(
            'https://www.youtube.com/watch?v=WSgP85kr6eU',
            \ILIAS\FileUpload\MimeType::APPLICATION__OCTET_STREAM
        );

        $this->assertEquals($expected, $actual);
    }


    public function testMimeTypeForVimeoUrlCouldBeCorrectlyDetected() : void
    {
        $expected = 'video/vimeo';
        $actual = \ILIAS\FileUpload\MimeType::lookupMimeType(
            'https://vimeo.com/180157999',
            \ILIAS\FileUpload\MimeType::APPLICATION__OCTET_STREAM
        );

        $this->assertEquals($expected, $actual);
    }
}
