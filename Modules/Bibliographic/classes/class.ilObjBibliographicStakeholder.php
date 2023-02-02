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

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Stakeholder\AbstractResourceStakeholder;

/**
 * Class ilObjBibliographicStakeholder
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilObjBibliographicStakeholder extends AbstractResourceStakeholder
{
    private ilDBInterface $db;

    public function __construct()
    {
        global $DIC;
        $this->db = $DIC->database();
    }


    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return 'bibl';
    }

    /**
     * @inheritDoc
     */
    public function getOwnerOfNewResources(): int
    {
        return 6;
    }

    public function getLocationURIForResourceUsage(ResourceIdentification $identification): ?string
    {
        $r = $this->db->query(
            "SELECT id FROM il_bibl_data WHERE rid = " . $this->db->quote($identification->serialize(), 'text')
        );
        $d = $this->db->fetchObject($r);
        if (isset($d->id)) {
            $references = ilObject::_getAllReferences($d->id);
            $ref_id = array_shift($references);

            return ilLink::_getLink($ref_id, 'bibl');
        }
        return null;
    }
}
