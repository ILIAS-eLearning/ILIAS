<?php declare(strict_types=1);

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
 * This class represents a section header in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilFormSectionHeaderGUI
{
    protected string $type = "";
    protected string $title = "";
    protected string $info = "";
    protected array $section_icon = [];
    protected string $section_anchor = "";
    protected ?ilPropertyFormGUI $parentform = null;
    
    public function __construct()
    {
        $this->setType("section_header");
    }
    
    public function checkInput() : bool
    {
        return true;
    }

    public function setType(string $a_type) : void
    {
        $this->type = $a_type;
    }

    public function getType() : string
    {
        return $this->type;
    }
    
    public function setSectionIcon(
        string $a_file,
        string $a_alt
    ) : void {
        $this->section_icon['file'] = $a_file;
        $this->section_icon['alt'] = $a_alt;
    }
    
    public function getSectionIcon() : array
    {
        return $this->section_icon ?: array();
    }

    public function setTitle(string $a_title) : void
    {
        $this->title = $a_title;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function setInfo(string $a_info) : void
    {
        $this->info = $a_info;
    }

    public function getInfo() : string
    {
        return $this->info;
    }

    public function setParentForm(ilPropertyFormGUI $a_parentform) : void
    {
        $this->parentform = $a_parentform;
    }

    public function getParentForm() : ilPropertyFormGUI
    {
        return $this->parentform;
    }
    
    public function setSectionAnchor(string $value) : void
    {
        $this->section_anchor = $value;
    }

    public function insert(ilTemplate $a_tpl) : void
    {
        $section_icon = $this->getSectionIcon();
        
        if (isset($section_icon['file']) && is_file($section_icon['file'])) {
            $a_tpl->setCurrentBlock("title_icon");
            $a_tpl->setVariable("IMG_ICON", $section_icon['file']);
            $a_tpl->setVariable('IMG_ALT', $section_icon['alt']);
            $a_tpl->parseCurrentBlock();
        }
        
        $a_tpl->setCurrentBlock("header");
        $a_tpl->setVariable("TXT_TITLE", $this->getTitle());
        $a_tpl->setVariable("TXT_DESCRIPTION", $this->getInfo());
        $a_tpl->setVariable('HEAD_COLSPAN', 2);
        if (isset($this->section_anchor)) {
            $a_tpl->setVariable('LABEL', $this->section_anchor);
        }
        $a_tpl->parseCurrentBlock();
    }
    
    public function setValueByArray(array $a_values) : void
    {
    }
    
    public function getPostVar() : string
    {
        return "";
    }
}
