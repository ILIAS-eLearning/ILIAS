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
 * Class for container reference export
 *
 * @author Fabian Wolf <wolf@leifos.com>
 * @extends ilContainerReferenceXmlWriter
 *
 * @ingroup components\ILIASGroupReference
 */
class ilGroupReferenceXmlWriter extends ilContainerReferenceXmlWriter
{
    /**
     * ilGroupReferenceXmlWriter constructor.
     * @param ilObjGroupReference|null $ref
     */
    public function __construct(?ilObjGroupReference $ref = null)
    {
        parent::__construct($ref);
    }

    protected function buildHeader(): void
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        $this->xmlSetGenCmt("Export of ILIAS course reference " . $this->getReference()->getId() . " of installation " . $ilSetting->get('inst_id') . ".");
        $this->xmlHeader();
    }
}
