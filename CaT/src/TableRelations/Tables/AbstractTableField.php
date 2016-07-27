<?php
namespace CaT\TableRelations\Tables;

interface AbstractTableField  {
	public function tableId();
	public function name();
	public function name_simple();
}
