<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * Class ilDclSelectionFieldRepresentation
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilDclSelectionFieldRepresentation extends ilDclBaseFieldRepresentation {

	protected function buildFieldCreationInput(ilObjDataCollection $dcl, $mode = 'create') {
		$opt = parent::buildFieldCreationInput($dcl, $mode);




		return $opt;
	}
}