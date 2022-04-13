<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTemplateTest extends ilCertificateBaseTestCase
{
    public function testCreateCertificateTemplate() : void
    {
        $time = time();

        $template = new ilCertificateTemplate(
            100,
            'crs',
            '<xml>crs</xml>',
            md5('<xml>crs</xml>'),
            '[]',
            1,
            'v5.4.0',
            $time,
            true,
            '/some/where/background.jpg',
            '/some/where/thumbnail.svg',
            555
        );

        $this->assertSame(100, $template->getObjId());
        $this->assertSame('crs', $template->getObjType());
        $this->assertSame('<xml>crs</xml>', $template->getCertificateContent());
        $this->assertSame(md5('<xml>crs</xml>'), $template->getCertificateHash());
        $this->assertSame(1, $template->getVersion());
        $this->assertSame('v5.4.0', $template->getIliasVersion());
        $this->assertSame($time, $template->getCreatedTimestamp());
        $this->assertTrue($template->isCurrentlyActive());
        $this->assertSame('/some/where/background.jpg', $template->getBackgroundImagePath());
        $this->assertSame(555, $template->getId());
    }
}
