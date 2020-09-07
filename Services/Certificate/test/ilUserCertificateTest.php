<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilUserCertificateTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateUserCertificate()
    {
        $userCertificate = new ilUserCertificate(
            1,
            20,
            'crs',
            400,
            'Niels Theen',
            123456789,
            '<xml>Some Content</xml>',
            '[]',
            null,
            '1',
            'v5.4.0',
            true,
            '/some/where/background.jpg',
            '/some/where/thumbnail.svg',
            140
        );

        $this->assertEquals(1, $userCertificate->getPatternCertificateId());
        $this->assertEquals(20, $userCertificate->getObjId());
        $this->assertEquals('crs', $userCertificate->getObjType());
        $this->assertEquals(400, $userCertificate->getUserId());
        $this->assertEquals('Niels Theen', $userCertificate->getUserName());
        $this->assertEquals(123456789, $userCertificate->getAcquiredTimestamp());
        $this->assertEquals('<xml>Some Content</xml>', $userCertificate->getCertificateContent());
        $this->assertEquals('[]', $userCertificate->getTemplateValues());
        $this->assertEquals(null, $userCertificate->getValidUntil());
        $this->assertEquals(1, $userCertificate->getVersion());
        $this->assertEquals('v5.4.0', $userCertificate->getIliasVersion());
        $this->assertEquals(true, $userCertificate->isCurrentlyActive());
        $this->assertEquals('/some/where/background.jpg', $userCertificate->getBackgroundImagePath());
        $this->assertEquals(140, $userCertificate->getId());
    }
}
