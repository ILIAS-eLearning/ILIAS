<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Page for portfolio template
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPortfolioTemplatePage extends ilPortfolioPage
{
    public const TYPE_BLOG_TEMPLATE = 3;
    
    public function getParentType() : string
    {
        return "prtt";
    }
    
    public function getPageDiskSize() : int
    {
        $quota_sum = 0;
        
        $this->buildDom();
        $dom = $this->getDom();
        if ($dom instanceof php4DOMDocument) {
            $dom = $dom->myDOMDocument;
        }
        $xpath_temp = new DOMXPath($dom);
        
        // mobs
        $nodes = $xpath_temp->query("//PageContent/MediaObject/MediaAlias");
        foreach ($nodes as $node) {
            $id = explode("_", $node->getAttribute("OriginId"));
            $mob_id = array_pop($id);
            $mob_dir = ilObjMediaObject::_getDirectory($mob_id);
            $quota_sum += ilFileUtils::dirsize($mob_dir);
        }
        
        return $quota_sum;
    }
}
