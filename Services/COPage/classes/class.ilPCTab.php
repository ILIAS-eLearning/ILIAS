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
 * Class ilPCTab
 * Tab content object (see ILIAS DTD)
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCTab extends ilPageContent
{
    public function init(): void
    {
        $this->setType("tabstab");
    }

    public function newItemAfter(): void
    {
        $tab = $this->getNode();
        $new_tab = $this->dom->create_element("Tab");
        if ($next_tab = $tab->next_sibling()) {
            $new_tab = $next_tab->insert_before($new_tab, $next_tab);
        } else {
            $parent_tabs = $tab->parent_node();
            $new_tab = $parent_tabs->append_child($new_tab);
        }
    }

    public function newItemBefore(): void
    {
        $tab = $this->getNode();
        $new_tab = $this->dom->create_element("Tab");
        $new_tab = $tab->insert_before($new_tab, $tab);
    }

    public function deleteItem(): void
    {
        $tab = $this->getNode();
        $tab->unlink($tab);
    }

    public function moveItemDown(): void
    {
        $tab = $this->getNode();
        $next = $tab->next_sibling();
        $next_copy = $next->clone_node(true);
        $next_copy = $tab->insert_before($next_copy, $tab);
        $next->unlink($next);
    }

    public function moveItemUp(): void
    {
        $tab = $this->getNode();
        $prev = $tab->previous_sibling();
        $tab_copy = $tab->clone_node(true);
        $tab_copy = $prev->insert_before($tab_copy, $prev);
        $tab->unlink($tab);
    }
}
