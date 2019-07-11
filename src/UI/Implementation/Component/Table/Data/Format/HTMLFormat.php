<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Format;

use ILIAS\UI\Renderer;
use ilTemplate;

/**
 * Class HTMLFormat
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Format
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class HTMLFormat extends AbstractFormat {

	/**
	 * @inheritDoc
	 */
	public function getFormatId(): string {
		return self::FORMAT_HTML;
	}


	/**
	 * @inheritDoc
	 */
	public function getFileExtension(): string {
		return "html";
	}


	/**
	 * @inheritDoc
	 */
	public function render(array $columns, array $rows, string $title, string $table_id, Renderer $renderer): string {
		$tpl = new ilTemplate(__DIR__
			. "/../../../../../templates/default/Table/tpl.datatable.html", true, true); // TODO: Somehow access `getTemplate` of renderer

		$tpl->setVariable("ID", $table_id);

		$tpl->setVariable("TITLE", $title);

		$tpl->setCurrentBlock("header");
		foreach ($columns as $column) {
			$tpl->setVariable("HEADER", $column);

			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("body");
		foreach ($rows as $row) {
			$tpl_row = new ilTemplate(__DIR__
				. "/../../../../../templates/default/Table/tpl.datatablerow.html", true, true); // TODO: Somehow access `getTemplate` of renderer

			$tpl_row->setCurrentBlock("row");

			foreach ($row as $column) {
				$tpl_row->setVariable("COLUMN", $column);

				$tpl_row->parseCurrentBlock();
			}

			$tpl->setVariable("ROW", $tpl_row->get());

			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}
}
