<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBiblEntryFactory
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblEntryFactory implements ilBiblEntryFactoryInterface {

	/**
	 * @inheritDoc
	 */
	public function findByIdAndTypeString($id, $type_string) {
		return ilBiblEntry::getInstance($type_string, $id);
	}


	/**
	 * @inheritDoc
	 */
	public function filterEntriesForTable($object_id, ilBiblTableQueryInfo $info = null) {
		$entries = $this->filterEntryIdsForTableAsArray($object_id, $info);
		$entry_objects = [];
		foreach ($entries as $entry_id => $entry) {
			$entry_objects[$entry_id] = ilBiblEntry::getInstance($entry['type'], $entry['id']);
		}

		return $entry_objects;
	}


	/**
	 * @inheritDoc
	 */
	public function filterEntryIdsForTableAsArray($object_id, ilBiblTableQueryInfo $info = null) {
		global $DIC;

		$types = [ "integer" ];
		$values = [ $object_id ];

		$q = "SELECT DISTINCT (e.id) FROM il_bibl_entry AS e
                JOIN il_bibl_attribute AS a ON a.entry_id = e.id
                        WHERE data_id = %s";
		if ($info instanceof ilBiblTableQueryInfo) {
			foreach ($info->getFilters() as $filter) {
				if (trim($filter->getFieldValue()) == '') {
					continue;
				}
				$types[] = "text";
				$types[] = "text";
				$values[] = $filter->getFieldName();
				$values[] = "%{$filter->getFieldValue()}%";
				$q .= " AND a.name = %s AND a.value {$filter->getOperator()} %s ";
			}
		}
		$entries = array();
		$set = $DIC->database()->queryF($q, $types, $values);
		while ($rec = $DIC->database()->fetchAssoc($set)) {
			$entries[]['entry_id'] = $rec['id'];
		}

		return $entries;
	}
}
