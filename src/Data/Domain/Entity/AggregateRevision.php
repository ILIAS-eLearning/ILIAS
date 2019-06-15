<?php

namespace ILIAS\Data\Domain\Entity;

interface AggregateRevision {

	public function getAggregateRevision(): string;
}