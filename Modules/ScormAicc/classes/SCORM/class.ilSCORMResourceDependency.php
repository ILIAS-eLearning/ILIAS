<?php declare(strict_types=1);
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
* SCORM Resource Dependency, DB accesses are done in ilSCORMResource
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMResourceDependency
{
    public string $identifierref;

    public function getIdentifierRef() : string
    {
        return $this->identifierref;
    }

    public function setIdentifierRef(string $a_id_ref) : void
    {
        $this->identifierref = $a_id_ref;
    }
}
