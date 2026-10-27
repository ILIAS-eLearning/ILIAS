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
 * Provides learning maps as page content.
 */
class ilPCLearningMap extends ilPageContent
{
    public const PCTYPE = 'lsolearningmap';
    public const PCELEMENT = 'LearningMap';
    public const PLACEHOLDER = '[[[LEARNINGMAP]]]';
    public const PROVIDING_TYPES = ['lso'];

    /**
     * Initializes the page content type.
     */
    public function init(): void
    {
        $this->setType(self::PCTYPE);
    }

    /**
     * Creates the page content node.
     */
    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $map_node = $this->dom_doc->createElement(self::PCELEMENT);
        $this->getDomNode()->appendChild($map_node);
    }

    /**
     * Replaces the learning map placeholder in page output.
     */
    public function modifyPageContentPostXsl(
        string $a_output,
        string $a_mode,
        bool $a_abstract_only = false
    ): string {
        if ($a_mode == 'edit') {
            return $a_output;
        }

        $parent_obj_id = (int) $this->getPage()->getParentId();
        if ($this->supportsLearningMap($parent_obj_id)) {
            $a_output = $this->replaceWithRenderedLearningMap($parent_obj_id, $a_output);
        }

        return $a_output;
    }

    /**
     * Determines whether an object provides a learning map.
     */
    protected function supportsLearningMap(int $parent_obj_id): bool
    {
        $parent_obj_type = \ilObject::_lookupType($parent_obj_id);
        return in_array($parent_obj_type, self::PROVIDING_TYPES);
    }

    /**
     * Replaces the placeholder with a rendered learning map.
     */
    protected function replaceWithRenderedLearningMap(int $obj_id, string $html): string
    {
        $lso = \ilObjectFactory::getInstanceByObjId($obj_id);
        $rendered_map = $lso->getCurrentUserLearningMap();
        return str_replace(self::PLACEHOLDER, $rendered_map, $html);
    }
}
