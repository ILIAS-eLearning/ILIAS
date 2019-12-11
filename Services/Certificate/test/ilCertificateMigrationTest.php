<?php

class ilCertificateMigrationTest extends PHPUnit_Framework_TestCase
{
    public function testGetTaskInformations()
    {
        $database = $this->getMockBuilder('ilDBInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $statement = $this->getMockBuilder('ilDBStatement')
            ->disableOriginalConstructor()
            ->getMock();

        $statement
            ->expects($this->atLeastOnce())
            ->method('numRows')
            ->willReturn(1);

        $database
            ->expects($this->atLeastOnce())
            ->method('queryF')
            ->willReturn($statement);

        $database
            ->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->willReturn(array(
                'id'              => 100,
                'usr_id'          => 200,
                'lock'            => false,
                'found_items'     => 4,
                'processed_items' => 3,
                'migrated_items'  => 5,
                'progress'        => 50,
                'state'           => 10,
                'started_ts'      => 123456789,
                'finished_ts'     => 987654321
            ));

        $migration = new ilCertificateMigration(100, $database);

        $result = $migration->getTaskInformations();

        $this->assertEquals(array(
            'id'              => 100,
            'usr_id'          => 200,
            'lock'            => false,
            'found_items'     => 4,
            'processed_items' => 3,
            'migrated_items'  => 5,
            'progress'        => 50,
            'state'           => 10,
            'started_ts'      => 123456789,
            'finished_ts'     => 987654321
        ), $result);
    }

    public function testGetTaskInformationsWithNoInformationStored()
    {
        $database = $this->getMockBuilder('ilDBInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $statement = $this->getMockBuilder('ilDBStatement')
            ->disableOriginalConstructor()
            ->getMock();

        $statement
            ->expects($this->atLeastOnce())
            ->method('numRows')
            ->willReturn(1);

        $database
            ->expects($this->atLeastOnce())
            ->method('queryF')
            ->willReturn($statement);

        $database
            ->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->willReturn(array(
                'id'              => 100,
                'usr_id'          => 200,
                'lock'            => false,
                'found_items'     => 4,
                'processed_items' => 3,
                'migrated_items'  => 5,
                'progress'        => 50,
                'state'           => 10,
                'started_ts'      => 123456789,
                'finished_ts'     => 987654321
            ));

        $migration = new ilCertificateMigration(100, $database);

        $result = $migration->getTaskInformationObject();

        $this->assertEquals(100, $result->getId());
    }

    public function testProgressedItemsAsPercent()
    {
        $database = $this->getMockBuilder('ilDBInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $statement = $this->getMockBuilder('ilDBStatement')
            ->disableOriginalConstructor()
            ->getMock();

        $statement
            ->expects($this->atLeastOnce())
            ->method('numRows')
            ->willReturn(1);

        $database
            ->expects($this->atLeastOnce())
            ->method('queryF')
            ->willReturn($statement);

        $database
            ->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->willReturn(array(
                'id'              => 100,
                'usr_id'          => 200,
                'lock'            => false,
                'found_items'     => 4,
                'processed_items' => 3,
                'migrated_items'  => 5,
                'progress'        => 50,
                'state'           => 10,
                'started_ts'      => 123456789,
                'finished_ts'     => 987654321
            ));

        $migration = new ilCertificateMigration(100, $database);

        $result = $migration->getProgressedItemsAsPercent();

        $this->assertEquals(75, $result);
    }

    public function testIsTaskStarted()
    {
        $database = $this->getMockBuilder('ilDBInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $statement = $this->getMockBuilder('ilDBStatement')
            ->disableOriginalConstructor()
            ->getMock();

        $statement
            ->expects($this->atLeastOnce())
            ->method('numRows')
            ->willReturn(1);

        $database
            ->expects($this->atLeastOnce())
            ->method('queryF')
            ->willReturn($statement);

        $database
            ->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->willReturn(array(
                'id'              => 100,
                'usr_id'          => 200,
                'lock'            => false,
                'found_items'     => 4,
                'processed_items' => 3,
                'migrated_items'  => 5,
                'progress'        => 50,
                'state'           => 'not started',
                'started_ts'      => 123456789,
                'finished_ts'     => 987654321
            ));

        $migration = new ilCertificateMigration(100, $database);

        $result = $migration->isTaskStarted();

        $this->assertTrue($result);
    }

    public function testIsTaskRunning()
    {
        $database = $this->getMockBuilder('ilDBInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $statement = $this->getMockBuilder('ilDBStatement')
            ->disableOriginalConstructor()
            ->getMock();

        $statement
            ->expects($this->atLeastOnce())
            ->method('numRows')
            ->willReturn(1);

        $database
            ->expects($this->atLeastOnce())
            ->method('queryF')
            ->willReturn($statement);

        $database
            ->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->willReturn(array(
                'id'              => 100,
                'usr_id'          => 200,
                'lock'            => true,
                'found_items'     => 4,
                'processed_items' => 3,
                'migrated_items'  => 5,
                'progress'        => 50,
                'state'           => 'running',
                'started_ts'      => 123456789,
                'finished_ts'     => 987654321
            ));

        $migration = new ilCertificateMigration(100, $database);

        $result = $migration->isTaskRunning();

        $this->assertTrue($result);
    }

    public function testIsTaskFailed()
    {
        $database = $this->getMockBuilder('ilDBInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $statement = $this->getMockBuilder('ilDBStatement')
            ->disableOriginalConstructor()
            ->getMock();

        $statement
            ->expects($this->atLeastOnce())
            ->method('numRows')
            ->willReturn(1);

        $database
            ->expects($this->atLeastOnce())
            ->method('queryF')
            ->willReturn($statement);

        $database
            ->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->willReturn(array(
                'id'              => 100,
                'usr_id'          => 200,
                'lock'            => true,
                'found_items'     => 4,
                'processed_items' => 3,
                'migrated_items'  => 5,
                'progress'        => 50,
                'state'           => 'failed',
                'started_ts'      => 123456789,
                'finished_ts'     => 987654321
            ));

        $migration = new ilCertificateMigration(100, $database);

        $result = $migration->isTaskFailed();

        $this->assertTrue($result);
    }

    public function testIsTaskFinished()
    {
        $database = $this->getMockBuilder('ilDBInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $statement = $this->getMockBuilder('ilDBStatement')
            ->disableOriginalConstructor()
            ->getMock();

        $statement
            ->expects($this->atLeastOnce())
            ->method('numRows')
            ->willReturn(1);

        $database
            ->expects($this->atLeastOnce())
            ->method('queryF')
            ->willReturn($statement);

        $database
            ->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->willReturn(array(
                'id'              => 100,
                'usr_id'          => 200,
                'lock'            => true,
                'found_items'     => 4,
                'processed_items' => 3,
                'migrated_items'  => 5,
                'progress'        => 50,
                'state'           => 'finished',
                'started_ts'      => 123456789,
                'finished_ts'     => 987654321
            ));

        $migration = new ilCertificateMigration(100, $database);

        $result = $migration->isTaskFinished();

        $this->assertTrue($result);
    }
}
