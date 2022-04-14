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
