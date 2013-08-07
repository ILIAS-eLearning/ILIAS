<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";

/**
 * Portfolio 
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesPortfolio
 */
class ilObjPortfolioTemplate extends ilObjPortfolio
{	
	public function initType()
	{
		$this->type = "prtt";
	}
}

?>