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

namespace ILIAS\ResourceStorage\Resource;

require_once(__DIR__ . '/../DummyIDGenerator.php');

use PHPUnit\Framework\TestCase;
use ILIAS\ResourceStorage\Resource\Repository\CollectionDBRepository;
use ILIAS\ResourceStorage\DummyIDGenerator;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Events\Subject;
use ILIAS\ResourceStorage\Events\DataContainer;
use ILIAS\ResourceStorage\Events\CollectionData;

/**
 * Class CollectionTest
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class CollectionRepositoryTest extends TestCase
{
    private const TEST_RCID = 'test_rcid';
    private \ilDBInterface|\PHPUnit\Framework\MockObject\MockObject $db_mock;
    private CollectionDBRepository $repo;

    protected function setUp(): void
    {
        $this->db_mock = $this->createMock(\ilDBInterface::class);
        $this->repo = new CollectionDBRepository($this->db_mock);
        $this->rcid_generator = new DummyIDGenerator(self::TEST_RCID);
    }

    public function testStore(): void
    {
        $collection = $this->repo->blank($this->rcid_generator->getUniqueResourceCollectionIdentification());
        $this->assertEquals(0, $collection->count());

        $rid_one = 'rid_one';
        $collection->add(new ResourceIdentification($rid_one));
        $rid_two = 'rid_two';
        $collection->add(new ResourceIdentification($rid_two));

        $rids_given = [$rid_one, $rid_two];
        $this->db_mock->expects($this->once())
                      ->method('in')
                      ->with('rid', $rids_given, true, 'text')
                      ->willReturn('rid NOT IN("rid_one", "rid_one")');

        $this->db_mock->expects($this->once())
                      ->method('manipulateF')
                      ->with('DELETE FROM il_resource_rca WHERE rcid = %s AND rid NOT IN("rid_one", "rid_one")');

        $this->db_mock->expects($this->once())
                      ->method('manipulateF')
                      ->with('DELETE FROM il_resource_rca WHERE rcid = %s AND rid NOT IN("rid_one", "rid_one")');

        $this->db_mock->expects($this->exactly(3))
                      ->method('insert')
                      ->will(
                          $this->onConsecutiveCalls(
                              $this->returnCallback(function ($table, $fields) {
                                  $this->assertEquals('il_resource_rca', $table);
                                  return 1;
                              }),
                              $this->returnCallback(function ($table, $fields) {
                                  $this->assertEquals('il_resource_rca', $table);
                                  return 1;
                              }),
                              $this->returnCallback(function ($table, $fields) {
                                  $this->assertEquals('il_resource_rc', $table);
                                  return 1;
                              })
                          )
                      );

        $event_data_container = new DataContainer();
        $this->repo->update($collection, $event_data_container);

        $rids = [];
        $this->assertCount(2, $event_data_container->get());
        foreach ($event_data_container->get() as $event_data) {
            $this->assertInstanceOf(CollectionData::class, $event_data);
            $this->assertContains($event_data->getRid(), $rids_given);
            $this->assertEquals(self::TEST_RCID, $event_data->getRcid());
        }
    }

}
