<?php
namespace ILIAS\AssessmentQuestion\Common\examples\CommonDDD\DomainModel\Aggregate;

use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractAggregateRoot;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AggregateId;

class Question extends AbstractAggregateRoot {

	/**
	 * @var AggregateId
	 */
	private $id;
	/**
	 * @var string
	 */
	private $title;
	/**
	 * @var string
	 */
	private $description;
	/**
	 * @var int
	 */
	private $creator_id;


	private function __construct(AggregateId $id, string $title, string $description, int $creator_id) {
		$this->id = $id;
		$this->title = $title;
		$this->description = $description;
		$this->creator_id = $creator_id;
	}


	public function editTitle(string $title) {
		$this->title = $title;
	}


	public function editDescription(string $description) {
		$this->description = $description;
	}


	function getAggregateId(): AggregateId {
		return $this->id;
	}


	/**
	 * @return string
	 */
	public function getTitle(): string {
		return $this->title;
	}


	/**
	 * @return string
	 */
	public function getDescription(): string {
		return $this->description;
	}


	/**
	 * @return int
	 */
	public function getCreatorId(): int {
		return $this->creator_id;
	}
}