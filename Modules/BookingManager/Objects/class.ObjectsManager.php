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

namespace ILIAS\BookingManager\Objects;

use ILIAS\BookingManager\InternalRepoService;
use ILIAS\BookingManager\InternalDataService;
use ILIAS\BookingManager\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ObjectsManager
{
    protected int $pool_id;
    protected ObjectsDBRepository $object_repo;
    protected InternalRepoService $repo;
    protected InternalDataService $data;
    protected InternalDomainService $domain;

    public function __construct(
        InternalDataService $data,
        InternalRepoService $repo,
        InternalDomainService $domain,
        int $pool_id
    ) {
        $this->object_repo = $repo->objects();
        $this->object_repo->loadDataOfPool($pool_id);
        $this->pool_id = $pool_id;
    }

    public function getNrOfItemsForObject(int $book_obj_id) : int
    {
        return $this->object_repo->getNrOfItemsForObject($book_obj_id);
    }

    public function getObjectTitles() : array
    {
        $titles = [];
        foreach ($this->object_repo->getObjectDataForPool($this->pool_id) as $d) {
            $titles[$d["booking_object_id"]] = $d["title"];
        }
        return $titles;
    }

    public function getObjectIds() : array
    {
        return array_map(static function ($d) {
            return (int) $d["booking_object_id"];
        }, $this->object_repo->getObjectDataForPool($this->pool_id));
    }

    public function getColorNrForObject(int $book_obj_id) : int
    {
        return $this->object_repo->getColorNrForObject($book_obj_id);
    }

    public function getDataArrayFromInputString(string $input) : array
    {
        $rows = explode("\n", $input);
        $data = [];
        foreach ($rows as $row) {
            $cells = explode(";", $row);
            if (count($cells) === 1) {
                $cells = explode("\t", $row);
            }
            $data[] = [
                "title" => trim($cells[0] ?? ""),
                "description" => trim($cells[1] ?? ""),
                "nr" => trim($cells[2] ?? ""),
            ];
        }
        return $data;
    }

    public function createObjectsFromBulkInputString(string $input, int $schedule_id) : void
    {
        foreach ($this->getDataArrayFromInputString($input) as $data) {
            $object = new \ilBookingObject();
            $object->setTitle($data["title"]);
            $object->setDescription($data["description"]);
            $object->setNrOfItems((int) $data["nr"]);
            $object->setPoolId($this->pool_id);
            if ($schedule_id > 0) {
                $object->setScheduleId($schedule_id);
            }
            $object->save();
        }
    }

}
