<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Permission checker for terms
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesGlossary
 */
class ilGlossaryTermPermission
{
    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var int[]
     */
    protected $glossary_id = array();

    /**
     * @var array
     */
    protected $permission = array();

    /**
     * @var ilLogger
     */
    protected $log;

    /**
     * ilGlossaryTermPermission constructor.
     */
    private function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->access = $DIC->access();

        $this->log = ilLoggerFactory::getLogger('glo');
    }

    /**
     * Get instance
     * @return ilGlossaryTermPermission
     */
    public static function getInstance()
    {
        return new self();
    }

    /**
     * Check permission
     *
     * @param string $a_perm
     * @param int $a_term_id
     * @return bool
     */
    public function checkPermission($a_perm, $a_term_id)
    {
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

    /**
     * Get glossary for term
     *
     * @param int $a_term_id
     * @return int
     */
    protected function getGlossaryIdForTerm($a_term_id)
    {
        if (!isset($this->glossary_id[$a_term_id])) {
            include_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
            $this->glossary_id[$a_term_id] = ilGlossaryTerm::_lookGlossaryID($a_term_id);
        }
        return $this->glossary_id[$a_term_id];
    }
}
