<?php declare(strict_types = 1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
class ilDAVContainerWithOverridenGetChildCollection extends ilDAVContainer
{
    protected ilContainer $child_collection;
    public function setChildcollection(ilContainer $child_collection) : void
    {
        $this->child_collection = $child_collection;
    }
    protected function getChildCollection() : ilContainer
    {
        return $this->child_collection;
    }
}
