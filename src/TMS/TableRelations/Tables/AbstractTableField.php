<?php
namespace ILIAS\TMS\TableRelations\Tables;
/**
 * A field combining Predicate-Fields and table functinality.
 * Note: it may still be used in Predicates.
 */
interface AbstractTableField  {

	/**
	 * Any TableField may be related to a Table.
	 * Two different Tables may contain fields with equal name.
	 *
	 * @return	string
	 */
	public function tableId();

	/**
	 * To avoid ambiguity we have to include related table-id into fieldname,
	 * i.e. return fully qualified name for query.
	 *
	 * @return	string
	 */
	public function name();

	/**
	 * Return plain field name.
	 *
	 * @return	string
	 */
	public function name_simple();
}
