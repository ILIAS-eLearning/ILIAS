<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTemplateTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateCertificateTemplate()
    {
        $time = time();

        $template = new ilCertificateTemplate(
            100,
            'crs',
            '<xml>crs</xml>',
            md5('<xml>crs</xml>'),
            '[]',
            '1',
            'v5.4.0',
            $time,
            true,
            '/some/where/background.jpg',
            '/some/where/thumbnail.svg',
            555
        );

        $this->assertEquals(100, $template->getObjId());
        $this->assertEquals('crs', $template->getObjType());
        $this->assertEquals('<xml>crs</xml>', $template->getCertificateContent());
        $this->assertEquals(md5('<xml>crs</xml>'), $template->getCertificateHash());
        $this->assertEquals('1', $template->getVersion());
        $this->assertEquals('v5.4.0', $template->getIliasVersion());
        $this->assertEquals($time, $template->getCreatedTimestamp());
        $this->assertEquals(true, $template->isCurrentlyActive());
        $this->assertEquals('/some/where/background.jpg', $template->getBackgroundImagePath());
        $this->assertEquals(555, $template->getId());
    }
}
