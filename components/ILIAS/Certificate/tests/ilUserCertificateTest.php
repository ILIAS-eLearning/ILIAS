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

use ILIAS\Certificate\ValueObject\CertificateId;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilUserCertificateTest extends ilCertificateBaseTestCase
{
    public function testCreateUserCertificate(): void
    {
        $userCertificate = new ilUserCertificate(
            1,
            20,
            'crs',
            400,
            'Niels Theen',
            123_456_789,
            '<xml>Some Content</xml>',
            '[]',
            null,
            1,
            'v5.4.0',
            true,
            new CertificateId('11111111-2222-3333-4444-555555555555'),
            '/some/where/background.jpg',
            '/some/where/thumbnail.svg',
            140,
        );

        $this->assertSame(1, $userCertificate->getPatternCertificateId());
        $this->assertSame(20, $userCertificate->getObjId());
        $this->assertSame('crs', $userCertificate->getObjType());
        $this->assertSame(400, $userCertificate->getUserId());
        $this->assertSame('Niels Theen', $userCertificate->getUserName());
        $this->assertSame(123_456_789, $userCertificate->getAcquiredTimestamp());
        $this->assertSame('<xml>Some Content</xml>', $userCertificate->getCertificateContent());
        $this->assertSame('[]', $userCertificate->getTemplateValues());
        $this->assertEquals(0, $userCertificate->getValidUntil());
        $this->assertSame(1, $userCertificate->getVersion());
        $this->assertSame('v5.4.0', $userCertificate->getIliasVersion());
        $this->assertTrue($userCertificate->isCurrentlyActive());
        $this->assertSame('/some/where/background.jpg', $userCertificate->getBackgroundImageIdentification());
        $this->assertSame(140, $userCertificate->getId());
        $this->assertSame('11111111-2222-3333-4444-555555555555', $userCertificate->getCertificateId()->asString());
    }
}
