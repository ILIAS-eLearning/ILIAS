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

use PHPUnit\Framework\MockObject\MockObject;

require_once(__DIR__ . '/../DummyIDGenerator.php');

use PHPUnit\Framework\TestCase;
use ILIAS\ResourceStorage\Resource\Repository\CollectionDBRepository;
use ILIAS\ResourceStorage\DummyIDGenerator;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Events\DataContainer;
use ILIAS\ResourceStorage\Events\CollectionData;
use ILIAS\ResourceStorage\Collection\ResourceCollection;

/**
 * Class CollectionTest
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class CollectionRepositoryTest extends TestCase
{
    private const string TEST_RCID = 'test_rcid';
    private MockObject $db_mock;
    private CollectionDBRepository $repo;
    private DummyIDGenerator $rcid_generator;

    protected function setUp(): void
    {
        $this->db_mock = $this->createMock(\ilDBInterface::class);
        $this->repo = new CollectionDBRepository($this->db_mock);
        $this->rcid_generator = new DummyIDGenerator(self::TEST_RCID);
    }

    public function testStore(): void
    {
        $collection = $this->repo->blank($this->rcid_generator->getUniqueResourceCollectionIdentification());
        $this->assertSame(0, $collection->count());

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

        $called_table_names = [
            'il_resource_rca',
            'il_resource_rca',
            'il_resource_rc',
        ];

        $this->db_mock->expects($this->exactly(3))
                      ->method('insert')
                      ->willReturnCallback(
                          function ($table_name) use (&$called_table_names): int {
                              $expected_table_name = array_shift($called_table_names);
                              TestCase::assertSame($expected_table_name, $table_name);
                              return 1;
                          }
                      );

        $event_data_container = new DataContainer();
        $this->repo->update($collection, $event_data_container);
        $this->assertCount(2, $event_data_container->get());
        foreach ($event_data_container->get() as $event_data) {
            $this->assertInstanceOf(CollectionData::class, $event_data);
            $this->assertContains($event_data->getRid(), $rids_given);
            $this->assertSame(self::TEST_RCID, $event_data->getRcid());
        }
    }

    /**
     * A collection read from the database must not have its resource id cache marked as
     * loaded and empty. Otherwise getResourceIdStrings() short circuits and the collection
     * appears empty although it has assignments.
     *
     * @see https://mantis.ilias.de/view.php?id=48254
     */
    public function testExistingLoadsResourceIds(): void
    {
        $rcid = $this->rcid_generator->getUniqueResourceCollectionIdentification();

        $this->expectCollectionRow();
        $this->expectAssignmentQueryReturning(['rid_one', 'rid_two']);

        $this->repo->existing($rcid);

        $this->assertSame(
            ['rid_one', 'rid_two'],
            iterator_to_array($this->repo->getResourceIdStrings($rcid))
        );
    }

    /**
     * A blank collection has no assignments in the database yet, so no query is needed.
     */
    public function testBlankDoesNotQueryForResourceIds(): void
    {
        $rcid = $this->rcid_generator->getUniqueResourceCollectionIdentification();

        $this->db_mock->expects($this->never())->method('query');

        $this->repo->blank($rcid);

        $this->assertSame([], iterator_to_array($this->repo->getResourceIdStrings($rcid)));
    }

    /**
     * Reading a preloaded collection must not throw the preloaded ids away, otherwise the
     * collection preloader degenerates into one query per collection again.
     */
    public function testExistingKeepsPreloadedResourceIds(): void
    {
        $rcid = $this->rcid_generator->getUniqueResourceCollectionIdentification();

        $this->expectAssignmentQueryReturning(['rid_one']);
        $this->expectCollectionRow();

        $this->repo->preload([self::TEST_RCID]);
        $this->repo->existing($rcid);

        $this->assertSame(['rid_one'], iterator_to_array($this->repo->getResourceIdStrings($rcid)));
    }

    private function expectCollectionRow(): void
    {
        $this->db_mock->method('queryF')->willReturn($this->createStub(\ilDBStatement::class));
        $this->db_mock->method('fetchObject')->willReturn(
            (object) ['owner_id' => ResourceCollection::NO_SPECIFIC_OWNER, 'title' => 'a title']
        );
    }

    /**
     * @param string[] $rids
     */
    private function expectAssignmentQueryReturning(array $rids): void
    {
        $this->db_mock->method('in')->willReturn('rcid IN("' . self::TEST_RCID . '")');

        // The assignments must be read exactly once, no matter how often the collection is
        // touched afterwards.
        $this->db_mock->expects($this->once())
                      ->method('query')
                      ->willReturn($this->createStub(\ilDBStatement::class));

        $rows = array_map(static fn(string $rid): array => ['rcid' => self::TEST_RCID, 'rid' => $rid], $rids);
        $rows[] = null;

        $this->db_mock->method('fetchAssoc')->willReturnCallback(
            static function () use (&$rows): ?array {
                return array_shift($rows);
            }
        );
    }
}
