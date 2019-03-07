<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Modules\WebResource;

use ilObject;

/**
 * Class ObjWebResourceAdministration
 *
 * @package ILIAS\Modules\WebResource
 *
 * @ingroup ModulesWebResource
 *
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ObjWebResourceAdministration extends ilObject {

	public function __construct($a_id = 0, $a_call_by_reference = true) {
		$this->type = "wbrs";
		parent::__construct($a_id, $a_call_by_reference);
	}


	public function delete() {
		// DISABLED
		return false;
	}
}
