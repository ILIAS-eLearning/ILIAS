<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Glossary actor class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilGlossaryAct
{
    protected ilObjGlossary $glossary;
    protected ilObjUser $user;
    protected ilAccessHandler $access;

    protected function __construct(
        ilObjGlossary $a_glossary,
        ilObjUser $a_user
    ) {
        global $DIC;

        $this->access = $DIC->access();
        $this->glossary = $a_glossary;
        $this->user = $a_user;
    }

    public static function getInstance(
        ilObjGlossary $a_glossary,
        ilObjUser $a_user
    ) {
        return new self($a_glossary, $a_user);
    }

    public function copyTerm(
        ilObjGlossary $a_source_glossary,
        int $a_term_id
    ) : void {
        if (!$this->access->checkAccessOfUser($this->user->getId(), "write", "", $this->glossary->getRefId())) {
            return;
        }

        if (!$this->access->checkAccessOfUser($this->user->getId(), "read", "", $a_source_glossary->getRefId())) {
            return;
        }

        if (ilGlossaryTerm::_lookGlossaryID($a_term_id) != $a_source_glossary->getId()) {
            return;
        }

        ilGlossaryTerm::_copyTerm($a_term_id, $this->glossary->getId());
    }


    /**
     * Reference a term of another glossary in current glossary
     * @param int[] $a_term_ids
     */
    public function referenceTerms(
        ilObjGlossary $a_source_glossary,
        array $a_term_ids
    ) : void {
        if (!$this->access->checkAccessOfUser($this->user->getId(), "write", "", $this->glossary->getRefId())) {
            return;
        }

        if (!$this->access->checkAccessOfUser($this->user->getId(), "read", "", $a_source_glossary->getRefId())) {
            return;
        }

        $refs = new ilGlossaryTermReferences($this->glossary->getId());
        foreach ($a_term_ids as $term_id) {
            if (ilGlossaryTerm::_lookGlossaryID($term_id) != $a_source_glossary->getId()) {
                continue;
            }

            if ($this->glossary->getId() == $a_source_glossary->getId()) {
                continue;
            }
            $refs->addTerm($term_id);
        }
        $refs->update();
    }
}
