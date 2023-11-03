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

declare(strict_types=1);

/**
 * PageComponent "Status Information" for PageEditor in PRGs
 */
#[AllowDynamicProperties]
class ilPCPRGStatusInfo extends ilPageContent
{
    protected ilLanguage $lng;
    public const PCTYPE = 'prgstatusinfo';
    public const PCELEMENT = 'PRGStatusInfo';
    public const PLACEHOLDER = '[[[PRG_STATUS_INFO]]]';
    public const PROVIDING_TYPES = ['prg'];

    public function init(): void
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->setType(self::PCTYPE);
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->cache_node = $this->dom_doc->createElement(self::PCELEMENT);
        $this->cache_node = $this->node->appendChild($this->cache_node);
    }

    /**
     * @inheritdoc
     */
    public function modifyPageContentPostXsl(
        string $a_output,
        string $a_mode,
        bool $a_abstract_only = false
    ): string {
        $parent_obj_id = $this->getPage()->getParentId();
        $end = 0;
        $start = strpos($a_output, "[[[PRG_STATUS_INFO");

        if (is_int($start)) {
            $end = strpos($a_output, "]]]", $start);
        }

        if ($a_mode === 'edit') {
            while ($end > 0) {
                if ($this->supportsType($parent_obj_id)) {
                    $html = $this->getTemplate();

                    $a_output = substr($a_output, 0, $start) .
                        $html .
                        substr($a_output, $end + 3);

                    $start = strpos($a_output, "[[[PRG_STATUS_INFO", $start + 3);
                    $end = 0;
                    if (is_int($start)) {
                        $end = strpos($a_output, "]]]", $start);
                    }
                }
            }
            return $a_output;
        }

        $parent_obj_id = (int) $this->getPage()->getParentId();
        if ($this->supportsType($parent_obj_id)) {
            $a_output = $this->replaceWithRendered($parent_obj_id, $a_output);
        }
        return $a_output;
    }

    protected function supportsType(int $parent_obj_id): bool
    {
        $parent_obj_type = \ilObject::_lookupType($parent_obj_id);
        return in_array($parent_obj_type, self::PROVIDING_TYPES);
    }

    protected function replaceWithRendered(int $obj_id, $html): string
    {
        $dic = ilStudyProgrammeDIC::dic();
        $builder = $dic['pc.statusinfo'];
        $rendered = $builder->getStatusInfoFor($obj_id);
        return str_replace(self::PLACEHOLDER, $rendered, $html);
    }

    protected function getTemplate(): string
    {
        $template = new ilTemplate("tpl.statusinfo_poeditor_element.html", true, true, 'Modules/StudyProgramme');
        $icon = "./templates/default/images/standard/icon_prg.svg";

        $template->setVariable("ICON", $icon);
        $template->setVariable("ICON_TEXT", $this->lng->txt("study_programme_icon"));
        $template->setVariable("LABEL", $this->lng->txt("pc_prg_statusinfo_label"));

        return $template->get();
    }
}
