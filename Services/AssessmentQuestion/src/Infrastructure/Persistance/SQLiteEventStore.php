<?php
namespace ILIAS\AssessmentQuestion\Infrastructure\Persistence;

use ILIAS\AssessmentQuestion\Example\Config\SQLiteDB;
use ILIAS\Data\Domain\AggregateHistory;
use ILIAS\Data\Domain\DomainEvent;
use ILIAS\Data\Domain\DomainEvents;
use ILIAS\Data\Domain\IdentifiesAggregate;
use DateTimeImmutable;
use \PDO;

class SQLiteEventStore implements EventStore
{
	/**
	 * @var SQLiteDB
	 */
	private $db;

	public function __construct($db)
	{
		$this->db = $db;
	}
	/**
	 * @param DomainEvents $events
	 * @return void
	 */
	public function commit(DomainEvents $events):void
	{
		$statement = $this->db->prepare('INSERT INTO events (aggregate_id, `type`, created_at, `data`)
             VALUES (:aggregate_id, :type, :created_at, :data)');

		$this->db->execute($statement,['fsdfsdfsdf','212342354',20022018,'20fdgsgdfgs22002']);
	}


	/**
	 * @param IdentifiesAggregate $id
	 * @return AggregateHistory
	 */
	public function getAggregateHistoryFor(IdentifiesAggregate $id):AggregateHistory
	{
		$statement = $this->db->prepare('SELECT * FROM events WHERE aggregate_id = :aggregate_id'
		);
		$this->db->execute($statement,[':aggregate_id' => (string) 'fsdfsdfsdf']);
		$events = [];
		while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
			$events[] = $row['data'];
		}
		$statement->closeCursor();

		//return new AggregateHistory($id, $events);
	}
}