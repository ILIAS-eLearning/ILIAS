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
class ilCertificateQueueEntryTest extends ilCertificateBaseTestCase
{
    public function testEntryCanBeInstantiated() : void
    {
        $timestamp = time();

        $queueEntry = new ilCertificateQueueEntry(
            10,
            500,
            'SomeClass',
            'SomeState',
            1000,
            $timestamp,
            20
        );

        $this->assertSame(20, $queueEntry->getId());
        $this->assertSame(10, $queueEntry->getObjId());
        $this->assertSame(500, $queueEntry->getUserId());
        $this->assertSame(1000, $queueEntry->getTemplateId());
        $this->assertSame('SomeClass', $queueEntry->getAdapterClass());
        $this->assertSame('SomeState', $queueEntry->getState());
        $this->assertSame($timestamp, $queueEntry->getStartedTimestamp());
    }
}
