<?php declare(strict_types=1);
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
 * Creates a path for a start and endnode
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesTree
 */
class ilPathGUI
{
    private int $startnode;
    private int $endnode;

    private bool $textOnly = true;
    private bool $useImages = false;
    private bool $hide_leaf = true;
    private bool $display_cut = false;

    protected ilLanguage $lng;
    protected ilTree $tree;
    protected ilObjectDefinition $objectDefinition;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->startnode = (int) ROOT_FOLDER_ID;
        $this->endnode = (int) ROOT_FOLDER_ID;
        $this->tree = $DIC->repositoryTree();
        $this->lng = $DIC->language();
        $this->objectDefinition = $DIC['objDefinition'];
    }

    /**
     * get path
     * @param int $a_startnode ref_id of startnode
     * @param int $a_endnode   ref_id of endnode
     * @return string html
     */
    public function getPath(int $a_startnode, int $a_endnode) : string
    {
        $this->startnode = $a_startnode;
        $this->endnode = $a_endnode;

        return $this->getHTML();
    }

    /**
     * render path as text only
     * @param bool $a_text_only path as text only true/false
     * @return void
     */
    public function enableTextOnly(bool $a_status) : void
    {
        $this->textOnly = $a_status;
    }

    public function textOnly() : bool
    {
        return $this->textOnly;
    }

    /**
     * Hide leaf node in path
     */
    public function enableHideLeaf(bool $a_status) : void
    {
        $this->hide_leaf = $a_status;
    }

    public function hideLeaf() : bool
    {
        return $this->hide_leaf;
    }

    public function setUseImages(bool $a_status) : void
    {
        $this->useImages = $a_status;
    }

    /**
     * get use images
     * @return bool
     */
    public function getUseImages() : bool
    {
        return $this->useImages;
    }

    /**
     * Display a cut with "..."
     */
    public function enableDisplayCut(bool $a_status) : void
    {
        $this->display_cut = $a_status;
    }

    /**
     * Display a cut with "..."
     */
    public function displayCut() : bool
    {
        return $this->display_cut;
    }

    /**
     * get html
     */
    protected function getHTML() : string
    {
        if ($this->textOnly()) {
            $tpl = new ilTemplate('tpl.locator_text_only.html', true, true, "Services/Locator");

            $first = true;

            // Display cut
            if ($this->displayCut() && $this->startnode != ROOT_FOLDER_ID) {
                $tpl->setCurrentBlock('locator_item');
                $tpl->setVariable('ITEM', "...");
                $tpl->parseCurrentBlock();

                $first = false;
            }

            foreach ($this->getPathIds() as $ref_id) {
                $obj_id = ilObject::_lookupObjId($ref_id);
                $title = $this->buildTitle($obj_id);

                if ($first) {
                    if ($ref_id == ROOT_FOLDER_ID) {
                        $title = $this->lng->txt('repository');
                    }
                } else {
                    $tpl->touchBlock('locator_separator_prefix');
                }

                $tpl->setCurrentBlock('locator_item');
                $tpl->setVariable('ITEM', $title);
                $tpl->parseCurrentBlock();
                $first = false;
            }
            return $tpl->get();
        } else {
            // With images and links

            $tpl = new ilTemplate('tpl.locator.html', true, true, 'Services/Locator');

            $first = true;

            // Display cut
            if ($this->displayCut() && $this->startnode != ROOT_FOLDER_ID) {
                $tpl->setCurrentBlock('locator_item');
                $tpl->setVariable('ITEM', "...");
                $tpl->parseCurrentBlock();

                $first = false;
            }

            foreach ($this->getPathIds() as $ref_id) {
                $obj_id = ilObject::_lookupObjId($ref_id);
                $title = $this->buildTitle($obj_id);
                $type = ilObject::_lookupType($obj_id);

                if ($first) {
                    if ($ref_id == ROOT_FOLDER_ID) {
                        $title = $this->lng->txt('repository');
                    }
                } else {
                    $tpl->touchBlock('locator_separator_prefix');
                }
                if ($this->getUseImages()) {
                    $tpl->setCurrentBlock('locator_img');
                    $tpl->setVariable('IMG_SRC', ilObject::_getIcon($obj_id, "small", $type));
                    $tpl->setVariable('IMG_ALT', $this->lng->txt('obj_' . $type));
                    $tpl->parseCurrentBlock();
                }

                if (!$this->tree->isDeleted($ref_id)) {
                    $tpl->setCurrentBlock('locator_item');
                    $tpl->setVariable('LINK_ITEM', ilLink::_getLink($ref_id, $type));
                    $tpl->setVariable('ITEM', $title);
                    $tpl->parseCurrentBlock();
                } else {
                    $tpl->setCurrentBlock('locator_item');
                    $tpl->setVariable('ITEM_READ_ONLY', $title);
                    $tpl->parseCurrentBlock();
                }

                $first = false;
            }
            $tpl->setVariable("TXT_BREADCRUMBS", $this->lng->txt("breadcrumb_navigation"));
            return $tpl->get();
        }
    }

    protected function buildTitle(int $a_obj_id) : string
    {
        $type = ilObject::_lookupType($a_obj_id);
        if ($this->objectDefinition->isAdministrationObject($type)) {
            return $this->lng->txt('obj_' . $type);
        }
        return ilObject::_lookupTitle($a_obj_id);
    }

    /**
     * @return int[]
     */
    protected function getPathIds() : array
    {
        $path = $this->tree->getPathId($this->endnode, $this->startnode);
        if ($this->hideLeaf() && count($path)) {
            unset($path[count($path) - 1]);
        }
        return $path;
    }
}
