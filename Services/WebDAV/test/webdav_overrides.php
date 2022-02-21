<?php declare(strict_types = 1);

class ilDAVContainerWithOverridenGetChildCollection extends ilDAVContainer
{
    protected ilContainer $child_collection;
    public function setChildcollection(ilContainer $child_collection)
    {
        $this->child_collection = $child_collection;
    }
    protected function getChildCollection() : ilContainer
    {
        return $this->child_collection;
    }
}
