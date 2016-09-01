<?php

/**
 * A possibility to store clusters of tables
 * as logical data aggregates is required.
 * This purpose will be served by Table Collections.
 */

namespace CaT\TableRelations\Tables;

interface AbstractTableCollection {

	/**
	 * Get a list of tables in table collection instance.
	 *
	 * @return AbstractTable[]
	 */
	public function tables();

	/**
	 * Get a list of dependencies in table collection.
	 *
	 * @return AbstractTableDependency[]
	 */
	public function dependencies();

	/**
	 * Any collection should have an id. It will correspond to a
	 * subgraph-id.
	 *
	 * @return string
	 */
	public function id();
}