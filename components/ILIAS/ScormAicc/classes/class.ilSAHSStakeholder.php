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

use ILIAS\ResourceStorage\Stakeholder\AbstractResourceStakeholder;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Stakeholder for SCORM/AICC learning module content (table sahs_lm).
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilSAHSStakeholder extends AbstractResourceStakeholder
{
    public function getId(): string
    {
        return 'sahs';
    }

    public function getOwnerOfNewResources(): int
    {
        return $this->default_owner;
    }

    public function getLocationURIForResourceUsage(ResourceIdentification $identification): ?string
    {
        $db = $this->resolveDB();
        if ($db === null) {
            return null;
        }
        $res = $db->queryF(
            'SELECT ref_id FROM sahs_lm
                    JOIN object_reference ON sahs_lm.id = object_reference.obj_id
                    WHERE rid = %s',
            ['text'],
            [$identification->serialize()]
        );
        if ($row = $db->fetchAssoc($res)) {
            return ilLink::_getStaticLink((int) $row['ref_id']);
        }
        return null;
    }

    public function isResourceInUse(ResourceIdentification $identification): bool
    {
        $db = $this->resolveDB();
        if ($db === null) {
            return true; // we assume it is in use
        }
        $res = $db->queryF(
            'SELECT id FROM sahs_lm WHERE rid = %s',
            ['text'],
            [$identification->serialize()]
        );
        return $db->numRows($res) > 0;
    }

    private function resolveDB(): ?ilDBInterface
    {
        global $DIC;
        if ($DIC->isDependencyAvailable('database')) {
            return $DIC->database();
        }
        return null;
    }
}
