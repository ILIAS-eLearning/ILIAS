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
	
	public function getPageDiskSize()
	{
		$quota_sum = 0;
		
		$this->buildDom();		
		$dom = $this->getDom();					
		if($dom instanceof php4DOMDocument)
		{
			$dom = $dom->myDOMDocument;
		}
		$xpath_temp = new DOMXPath($dom);
		
		// mobs
		include_once "Services/MediaObjects/classes/class.ilObjMediaObject.php";
		$nodes = $xpath_temp->query("//PageContent/MediaObject/MediaAlias");
		foreach($nodes as $node)
		{
			$mob_id = array_pop(explode("_", $node->getAttribute("OriginId")));
			$mob_dir = ilObjMediaObject::_getDirectory($mob_id);
			$quota_sum += ilUtil::dirSize($mob_dir); 
		}
		
		return $quota_sum;
	}
}

?>