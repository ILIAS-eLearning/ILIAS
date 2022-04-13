<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

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
