<?php declare(strict_types = 1);

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

namespace ILIAS\Glossary\Term;

use ILIAS\Glossary\InternalDomainService;

/**
 * Manages terms
 * @author Alexander Killing <killing@leifos.de>
 */
class TermManager
{
    protected InternalDomainService $domain;
    protected TermSessionRepository $session_repo;
    protected \ilObjGlossary $glossary;
    protected int $user_id;

    public function __construct(
        InternalDomainService $domain_service,
        TermSessionRepository $session_repo,
        \ilObjGlossary $glossary,
        int $user_id
    ) {
        $this->session_repo = $session_repo;
        $this->glossary = $glossary;
        $this->user_id = $user_id;
        $this->domain = $domain_service;
    }

    public function setSessionLang(string $lang) : void
    {
        $this->session_repo->setLang($this->glossary->getRefId(), $lang);
    }

    public function getSessionLang() : string
    {
        return $this->session_repo->getLang($this->glossary->getRefId());
    }

    public function copyTermFromOtherGlossary(
        int $other_glossary_ref_id,
        int $term_id
    ) : void {
        $access = $this->domain->access();

        if (!$access->checkAccessOfUser(
            $this->user_id,
            "write",
            "",
            $this->glossary->getRefId()
        )
        ) {
            return;
        }

        if (!$access->checkAccessOfUser(
            $this->user_id,
            "read",
            "",
            $other_glossary_ref_id
        )) {
            return;
        }

        if (\ilGlossaryTerm::_lookGlossaryID($term_id) !=
            \ilObject::_lookupObjectId($other_glossary_ref_id)) {
            return;
        }

        \ilGlossaryTerm::_copyTerm($term_id, $this->glossary->getId());
    }

    /**
     * Reference terms of another glossary in current glossary
     */
    public function referenceTermsFromOtherGlossary(
        int $other_glossary_ref_id,
        array $term_ids
    ) : void {
        $access = $this->domain->access();

        if (!$access->checkAccessOfUser(
            $this->user_id,
            "write",
            "",
            $this->glossary->getRefId()
        )) {
            return;
        }

        if (!$access->checkAccessOfUser(
            $this->user_id,
            "read",
            "",
            $other_glossary_ref_id
        )) {
            return;
        }

        $other_glossary_obj_id = \ilObject::_lookupObjectId($other_glossary_ref_id);
        $refs = new \ilGlossaryTermReferences($this->glossary->getId());
        foreach ($term_ids as $term_id) {
            if (\ilGlossaryTerm::_lookGlossaryID($term_id) != $other_glossary_obj_id) {
                continue;
            }

            if ($this->glossary->getId() == $other_glossary_obj_id) {
                continue;
            }
            $refs->addTerm($term_id);
        }
        $refs->update();
    }
}
