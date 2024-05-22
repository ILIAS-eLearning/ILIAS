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

namespace ILIAS\Test\Certificate;

use ilObjTest;
use ilCertificateDeleteAction;
use ilCertificateObjectHelper;
use PHPUnit\Framework\TestCase;
use ILIAS\Course\Certificate\CertificateTestTemplateDeleteAction;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class CertificateTestTemplateDeleteActionTest extends TestCase
{
    public function testDelete(): void
    {
        $delete_action = $this->getMockBuilder(ilCertificateDeleteAction::class)
            ->getMock();

        $delete_action
            ->expects($this->once())
            ->method('delete');

        $object_helper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->getMock();

        $object = $this->getMockBuilder(ilObjTest::class)
            ->disableOriginalConstructor()
            ->getMock();

        $object_helper->method('getInstanceByObjId')
            ->willReturn($object);

        $action = new CertificateTestTemplateDeleteAction(
            $delete_action
        );

        $action->delete(100, 200);
    }
}
