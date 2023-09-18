<?php exit; ?>

===================================
Collect and iterate container items
===================================

ilContainer
-----------
- ilContainer->getSubItems()
-- used for getting all childs or a single child (todo: separate)
-- handles caching in $this->items[$a_admin_panel_enabled][$a_include_side_block]
-- handles data preloader
-- get objects via $tree->getChilds
-- groups by ilContainer->getSubItems()
   -> $objDefinition->getGroupedRepositoryObjectTypes($this->getType());
-- complex long description determination
-- gets sorting instance ilContainerSorting::_getInstance($this->getId());
-- gets session items
-- filters objects
   - no dev mode types
   - no inactive plugins
   - no hidden files/container
   - side block elements (if not enabled by parameter)
-- groups objects by type
-- loads activation properties (ilContainer->addAdditionalSubItemInformation())
-- stores objects per type -> $this->items[$type][...]
-- stores all objects -> $this->items["_all"][...]
-- stores non-session objects -> $this->items["_non_sess"][...]
-- sort items by ilContainerSorting instance $sort->sortItems($this->items);
-- stores sort result in $this->items[$a_admin_panel_enabled][$a_include_side_block]









