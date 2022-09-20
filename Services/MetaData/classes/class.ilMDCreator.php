<?php

declare(strict_types=1);

/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
 * Base class for creating meta data sets for object types
 * If you need special element values, inherit from this class. E.g class.ilMDCourseCreator extends class.ilMDCreator
 * @package ilias-core
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 */
class ilMDCreator
{
    protected ilMD $md_obj;

    /*
     * rbac_id ref_id of rbac object (e.g for page objects the obj_id of the content object)
     */
    private int $rbac_id;

    /*
     * obj_id (e.g for structure objects the obj_id of the structure object)
     */
    private int $obj_id;

    /*
     * type of the object (e.g st,pg,crs ...)
     */
    public string $obj_type;

    private string $structure = '';
    private string $catalog = '';
    private string $entry = '';
    private string $keyword = '';
    private string $title = '';
    private string $description = '';
    private string $title_lng = '';

    public function __construct(int $a_rbac_id, int $a_obj_id, string $a_type)
    {
        if ($a_obj_id === 0) {
            $a_obj_id = $a_rbac_id;
        }

        $this->rbac_id = $a_rbac_id;
        $this->obj_id = $a_obj_id;
        $this->obj_type = $a_type;

        $this->md_obj = new ilMD($a_rbac_id, $a_obj_id, $a_type);
    }

    // SET/GET
    public function setTitle(string $a_title): void
    {
        $this->title = $a_title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setDescription(string $a_desc): void
    {
        $this->description = $a_desc;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setTitleLanguage(string $a_lng): void
    {
        $this->title_lng = $a_lng;
    }

    public function getTitleLanguage(): ilMDLanguageItem
    {
        return new ilMDLanguageItem($this->title_lng);
    }

    public function setDescriptionLanguage(string $a_lng): void
    {
        $this->title_lng = $a_lng;
    }

    public function getDescriptionLanguage(): ilMDLanguageItem
    {
        return new ilMDLanguageItem($this->title_lng);
    }

    public function setLanguage(string $a_lng): void
    {
        $this->title_lng = $a_lng;
    }

    public function getLanguage(): ilMDLanguageItem
    {
        return new ilMDLanguageItem($this->title_lng);
    }

    public function setKeyword(string $a_key): void
    {
        $this->keyword = $a_key;
    }

    public function getKeyword(): string
    {
        return $this->keyword;
    }

    public function getRBACId(): int
    {
        return $this->rbac_id;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getObjType(): string
    {
        return $this->obj_type;
    }

    public function setKeywordLanguage(string $a_lng): void
    {
        $this->title_lng = $a_lng;
    }

    public function getKeywordLanguage(): ilMDLanguageItem
    {
        return new ilMDLanguageItem($this->title_lng);
    }

    public function setCatalog(string $a_cat): void
    {
        $this->catalog = $a_cat;
    }

    public function getCatalog(): string
    {
        return $this->catalog ?: 'ILIAS';
    }

    public function setEntry(string $a_entry): void
    {
        $this->entry = $a_entry;
    }

    public function getEntry(): string
    {
        return $this->entry ?: 'il__' . $this->getObjType() . '_' . $this->getObjId();
    }

    public function setStructure(string $a_structure): void
    {
        $this->structure = $a_structure;
    }

    public function getStructure(): string
    {
        return $this->structure ?: 'Hierarchical';
    }

    public function create(): void
    {
        $this->__createGeneral();
    }

    // PROTECTED
    public function __createGeneral(): bool
    {
        $md_gen = $this->md_obj->addGeneral();

        $md_gen->setStructure($this->getStructure());
        $md_gen->setTitle($this->getTitle());
        $md_gen->setTitleLanguage($this->getTitleLanguage());
        $md_gen->save();

        $md_ide = $md_gen->addIdentifier();
        $md_ide->setCatalog($this->getCatalog());
        $md_ide->setEntry($this->getEntry());
        $md_ide->save();

        $md_lng = $md_gen->addLanguage();
        $md_lng->setLanguage($this->getLanguage());
        $md_lng->save();

        $md_des = $md_gen->addDescription();
        $md_des->setDescription($this->getDescription());
        $md_des->setDescriptionLanguage($this->getDescriptionLanguage());
        $md_des->save();

        $md_key = $md_gen->addKeyword();
        $md_key->setKeyword($this->getKeyword());
        $md_key->setKeywordLanguage($this->getKeywordLanguage());
        $md_key->save();

        return true;
    }
}
