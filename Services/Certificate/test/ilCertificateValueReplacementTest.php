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

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateValueReplacementTest extends ilCertificateBaseTestCase
{
    public function testReplace(): void
    {
        $replacement = new ilCertificateValueReplacement();

        $placeholderValues = ['NAME' => 'Peter', 'PRIZE' => 'a fantastic prize'];

        $certificateContent = '<xml> 
[BACKGROUND_IMAGE]
Hurray [NAME] you have received [PRIZE]
</xml>';

        $replacedContent = $replacement->replace($placeholderValues, $certificateContent);

        $expected = '<xml> 
[BACKGROUND_IMAGE]
Hurray Peter you have received a fantastic prize
</xml>';

        $this->assertSame($expected, $replacedContent);
    }

    public function testReplaceClientWebDir(): void
    {
        $replacement = new ilCertificateValueReplacement();

        $placeholderValues = ['NAME' => 'Peter', 'PRIZE' => 'a fantastic prize'];

        $certificateContent = '<xml> 
[BACKGROUND_IMAGE]
[CLIENT_WEB_DIR]/background.jpg
Hurray [NAME] you have received [PRIZE]
</xml>';

        $replacedContent = $replacement->replace($placeholderValues, $certificateContent);

        $expected = '<xml> 
[BACKGROUND_IMAGE]
[CLIENT_WEB_DIR]/background.jpg
Hurray Peter you have received a fantastic prize
</xml>';

        $this->assertSame($expected, $replacedContent);
    }
}
