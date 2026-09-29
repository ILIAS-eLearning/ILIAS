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

namespace ILIAS\FileServices\Policy;

/**
 * Resolves the bypass permission through the legacy container, since the public
 * RBAC surface of AccessControl does not offer a permission check yet. Without a
 * constructor it can be built at build time; the container is only read once the
 * check actually runs.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
final class UploadRestrictionBypassLegacyProxy implements UploadRestrictionBypass
{
    private const string PERMISSION = 'upload_blacklisted_files';

    private ?bool $granted = null;

    public function isGrantedToCurrentUser(): bool
    {
        if ($this->granted !== null) {
            return $this->granted;
        }

        global $DIC;
        return $this->granted = ($DIC->isDependencyAvailable('rbac')
            && isset($DIC['rbacsystem'])
            && $DIC->rbac()->system()->checkAccess(
                self::PERMISSION,
                $this->determineFileAdminRefId()
            ));
    }

    private function determineFileAdminRefId(): int
    {
        global $DIC;
        try {
            $r = $DIC->database()->query(
                "SELECT ref_id FROM object_reference JOIN object_data ON object_reference.obj_id = object_data.obj_id WHERE object_data.type = 'facs';"
            );
            $r = $DIC->database()->fetchObject($r);
            return (int) ($r->ref_id ?? 0);
        } catch (\Throwable) {
            return 0;
        }
    }
}
