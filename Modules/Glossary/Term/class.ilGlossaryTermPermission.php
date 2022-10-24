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
 * Permission checker for terms
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesGlossary
 */
class ilGlossaryTermPermission
{
    protected ilObjUser $user;
    protected ilAccessHandler $access;
    /** @var int[] */
    protected array $glossary_id = array();
    protected array $permission = array();
    protected ilLogger $log;

    private function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->access = $DIC->access();

        $this->log = ilLoggerFactory::getLogger('glo');
    }

    public static function getInstance(): self
    {
        return new self();
    }

    public function checkPermission(
        string $a_perm,
        int $a_term_id
    ): bool {
        $this->log->debug("check permission " . $a_perm . " for " . $a_term_id . ".");
        $glo_id = $this->getGlossaryIdForTerm($a_term_id);
        if (!isset($this->permission[$a_perm][$glo_id])) {
            $this->permission[$a_perm][$glo_id] = false;
            $this->log->debug("...checking references");
            foreach (ilObject::_getAllReferences($glo_id) as $ref_id) {
                $this->log->debug("..." . $ref_id);
                if ($this->permission[$a_perm][$glo_id] == true) {
                    continue;
                }
                if ($this->access->checkAccess($a_perm, "", $ref_id)) {
                    $this->permission[$a_perm][$glo_id] = true;
                }
            }
        }
        $this->log->debug("...return " . ((int) $this->permission[$a_perm][$glo_id]));
        return $this->permission[$a_perm][$glo_id];
    }

    protected function getGlossaryIdForTerm(int $a_term_id): int
    {
        if (!isset($this->glossary_id[$a_term_id])) {
            $this->glossary_id[$a_term_id] = ilGlossaryTerm::_lookGlossaryID($a_term_id);
        }
        return $this->glossary_id[$a_term_id];
    }
}
