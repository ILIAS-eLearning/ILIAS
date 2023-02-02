<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

namespace ILIAS\BookingManager\BookingProcess;

use ILIAS\BookingManager\InternalDataService;
use ILIAS\BookingManager\InternalRepoService;
use ILIAS\BookingManager\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ObjectSelectionManager
{
    protected int $pool_id;
    protected \ILIAS\BookingManager\Objects\ObjectsManager $object_manager;
    protected InternalDataService $data;
    protected InternalRepoService $repo;
    protected InternalDomainService $domain;

    public function __construct(
        InternalDataService $data,
        InternalRepoService $repo,
        InternalDomainService $domain,
        int $pool_id
    ) {
        $this->data = $data;
        $this->repo = $repo;
        $this->domain = $domain;
        $this->object_manager = $domain->objects($pool_id);
        $this->pool_id = $pool_id;
    }

    public function getSelectedObjects(int $user_id = 0) : array
    {
        if ($user_id === 0) {
            $user_id = $this->domain->user()->getId();
        }
        $valid_obj_ids = $this->object_manager->getObjectIds();
        return array_filter(
            $this->repo->objectSelection()->getSelectedObjects($this->pool_id, $user_id),
            static function ($id) use ($valid_obj_ids) {
                return in_array($id, $valid_obj_ids, true);
            }
        );
    }

    public function setSelectedObjects(array $obj_ids, int $user_id = 0) : void
    {
        if ($user_id === 0) {
            $user_id = $this->domain->user()->getId();
        }
        $valid_obj_ids = $this->object_manager->getObjectIds();
        $obj_ids = array_filter(
            $obj_ids,
            static function ($id) use ($valid_obj_ids) {
                return in_array($id, $valid_obj_ids, true);
            }
        );
        $this->repo->objectSelection()->setSelectedObjects(
            $this->pool_id,
            $user_id,
            $obj_ids
        );
    }
}
