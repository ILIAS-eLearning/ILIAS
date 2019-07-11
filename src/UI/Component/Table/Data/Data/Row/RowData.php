<?php

namespace ILIAS\UI\Component\Table\Data\Data\Row;

/**
 * Interface RowData
 *
 * @package ILIAS\UI\Component\Table\Data\Data\Row
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface RowData {

	/**
	 * RowData constructor
	 *
	 * @param string $row_id
	 * @param object $original_data
	 */
	public function __construct(string $row_id, object $original_data);


	/**
	 * @return string
	 */
	public function getRowId(): string;


	/**
	 * @param string $row_id
	 *
	 * @return self
	 */
	public function withRowId(string $row_id): self;


	/**
	 * @return object
	 */
	public function getOriginalData(): object;


	/**
	 * @param object $original_data
	 *
	 * @return self
	 */
	public function withOriginalData(object $original_data): self;


	/**
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function __invoke(string $key);
}
