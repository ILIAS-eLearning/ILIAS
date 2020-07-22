<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateMigrationInformationObjectTest extends PHPUnit_Framework_TestCase
{
    public function testCreatingObjectAndReceiveData()
    {
        $array = array(
            'id' => 100,
            'usr_id' => 200,
            'lock' => true,
            'found_items' => 4,
            'processed_items' => 3,
            'migrated_items' => 5,
            'progress' => 50,
            'state' => 'finished',
            'started_ts' => 123456789,
            'finished_ts' => 987654321
        );

        $informationObject = new ilCertificateMigrationInformationObject($array);

        $this->assertEquals(100, $informationObject->getId());
        $this->assertEquals(200, $informationObject->getUserId());
        $this->assertEquals(true, $informationObject->getLock());
        $this->assertEquals(4, $informationObject->getFoundItems());
        $this->assertEquals(3, $informationObject->getProgressedItems());
        $this->assertEquals('finished', $informationObject->getState());
        $this->assertEquals(50, $informationObject->getProgress());
        $this->assertEquals(123456789, $informationObject->getStartingTime());
        $this->assertEquals(987654321, $informationObject->getFinishedTime());
        $this->assertEquals((987654321 - 123456789), $informationObject->getProcessingTime());
    }

    public function testCreatingObjectAsArray()
    {
        $array = array(
            'id' => 100,
            'usr_id' => 200,
            'lock' => true,
            'found_items' => 4,
            'processed_items' => 3,
            'migrated_items' => 5,
            'progress' => 50,
            'state' => 'finished',
            'started_ts' => 123456789,
            'finished_ts' => 987654321
        );

        $informationObject = new ilCertificateMigrationInformationObject($array);
        $dataArray = $informationObject->getDataAsArray();

        $this->assertEquals(array(
            'id' => 100,
            'usr_id' => 200,
            'lock' => 1,
            'found_items' => 4,
            'processed_items' => 3,
            'progress' => 50,
            'state' => 'finished',
            'started_ts' => 123456789,
            'finished_ts' => 987654321
        ), $dataArray);
    }
}
