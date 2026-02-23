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

namespace ILIAS\GlobalScreen\GUI\Access;

use ILIAS\Refinery\Factory;
use ILIAS\HTTP\Services;
use ILIAS\DI\RBACServices;

/**
 * @author   Fabian Schmid <fabian@sr.solutions>
 * @internal Please do not use outside GlobalScreen
 */
class Access
{
    private \ilRbacSystem $rbac_system;
    private ?int $ref_id = null;

    private bool $access_checked = false;

    /**
     * ilObjMainMenuAccess constructor.
     */
    public function __construct(
        RBACServices $rbac,
        Services $http,
        Factory $refinery,
    ) {
        $this->rbac_system = $rbac->system();
        $this->ref_id = $http->wrapper()->query()->has('ref_id')
            ? $http->wrapper()->query()->retrieve('ref_id', $refinery->kindlyTo()->int())
            : null;
    }

    public function checkAccessAndThrowException(string $permission): void
    {
        if (!$this->hasUserPermissionTo($permission)) {
            throw new \ilException('Permission denied');
        }
    }

    public function hasUserPermissionTo(string $permission): bool
    {
        if ($this->ref_id === null) {
            return false;
        }
        // split permission string
        $permissions = explode(',', $permission);
        foreach ($permissions as $p) {
            if ($this->rbac_system->checkAccess($p, $this->ref_id)) {
                $this->access_checked = true;
                return true;
            }
        }
        $this->access_checked = true;
        return false;
    }

    public function __destruct()
    {
        if (!$this->access_checked) {
            //            throw new \RuntimeException('No Access Check');
        }
    }

    public function requireReadable(): void
    {
        $this->require('read');
    }

    public function requireWritable(): void
    {
        $this->require('write');
    }

    public function require(string $permissions): void
    {
        $this->checkAccessAndThrowException($permissions);
    }

}
