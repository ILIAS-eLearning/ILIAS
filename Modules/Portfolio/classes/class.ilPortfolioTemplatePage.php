<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/Portfolio/classes/class.ilPortfolioPage.php");

/**
 * Page for portfolio template
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesPortfolio
 */
class ilPortfolioTemplatePage extends ilPortfolioPage
{
	const TYPE_BLOG_TEMPLATE = 3;
	
	/**
	 * Get parent type
	 *
	 * @return string parent type
	 */
	function getParentType()
	{
		return "prtt";
	}
}

?>