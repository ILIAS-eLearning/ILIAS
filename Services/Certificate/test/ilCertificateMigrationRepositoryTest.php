<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateMigrationRepositoryTest extends PHPUnit_Framework_TestCase
{
    public function testDeleteFromMigrationJob()
    {
        $database = $this->getMockBuilder('ilDBInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $database->expects($this->atLeastOnce())->method('manipulate');

        $logger = $this->getMockBuilder('ilLogger')
            ->disableOriginalConstructor()
            ->getMock();

        $logger->expects($this->atLeastOnce())->method('log');

        $repository = new ilCertificateMigrationRepository($database, $logger);

        $repository->deleteFromMigrationJob(100);
    }
}
