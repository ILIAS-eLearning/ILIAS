<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Glossary actor class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesGlossary
 */
class ilGlossaryAct
{
    /**
     * @var ilObjGlossary
     */
    protected $glossary;

    /**
     * @var ilObjUser acting user
     */
    protected $user;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * ilGlossaryAct constructor.
     * @param ilObjGlossary $a_glossary
     * @param ilObjUser $a_user
     */
    protected function __construct(ilObjGlossary $a_glossary, ilObjUser $a_user)
    {
        global $DIC;

        $this->access = $DIC->access();
        $this->glossary = $a_glossary;
        $this->user = $a_user;
    }

    /**
     * Get instance
     * @param ilObjGlossary $a_glossary
     * @param ilObjUser $a_user
     * @return ilGlossaryAct
     */
    public static function getInstance(ilObjGlossary $a_glossary, ilObjUser $a_user)
    {
        return new self($a_glossary, $a_user);
    }

    /**
     * Copy term
     *
     * @param ilObjGlossary $a_source_glossary
     * @param int $a_term_id term id
     */
    public function copyTerm(ilObjGlossary $a_source_glossary, $a_term_id)
    {
        if (!$this->access->checkAccessOfUser($this->user->getId(), "write", "", $this->glossary->getRefId())) {
            return;
        }

        if (!$this->access->checkAccessOfUser($this->user->getId(), "read", "", $a_source_glossary->getRefId())) {
            return;
        }

        include_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
        if (ilGlossaryTerm::_lookGlossaryID($a_term_id) != $a_source_glossary->getId()) {
            return;
        }

        ilGlossaryTerm::_copyTerm($a_term_id, $this->glossary->getId());
    }


    /**
     * Reference a term of another glossary in current glossary
     *
     * @param ilObjGlossary $a_source_glossary
     * @param int[] $a_term_ids
     */
    public function referenceTerms(ilObjGlossary $a_source_glossary, $a_term_ids)
    {
        if (!$this->access->checkAccessOfUser($this->user->getId(), "write", "", $this->glossary->getRefId())) {
            return;
        }

        if (!$this->access->checkAccessOfUser($this->user->getId(), "read", "", $a_source_glossary->getRefId())) {
            return;
        }

        include_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
        include_once("./Modules/Glossary/classes/class.ilGlossaryTermReferences.php");
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
