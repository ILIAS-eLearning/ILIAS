<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMimeTypeTest
 */
class ilMimeTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testMimeTypeForYoutubeUrlCouldBeCorrectlyDetected()
    {
        $expected = 'video/youtube';
        $actual   = \ilMimeTypeUtil::lookupMimeType(
            'https://www.youtube.com/watch?v=WSgP85kr6eU',
            \ilMimeTypeUtil::APPLICATION__OCTET_STREAM
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     *
     */
    public function testMimeTypeForVimeoUrlCouldBeCorrectlyDetected()
    {
        $expected = 'video/vimeo';
        $actual   = \ilMimeTypeUtil::lookupMimeType(
            'https://vimeo.com/180157999',
            \ilMimeTypeUtil::APPLICATION__OCTET_STREAM
        );

        $this->assertEquals($expected, $actual);
    }
}
