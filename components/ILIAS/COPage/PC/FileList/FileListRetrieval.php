<?php

/* Copyright (c) 1998-2024 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace ILIAS\COPage\PC\FileList;

use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\COPage\InternalDomainService;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\RetrievalBase;
use ilPCFileList;
use ilObject;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class FileListRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    protected InternalDomainService $domain_service;
    protected ilPCFileList $file_list;

    public function __construct(
        InternalDomainService $domain_service,
        ilPCFileList $file_list
    ) {
        $this->domain_service = $domain_service;
        $this->file_list = $file_list;
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        $data = $this->collectData();
        foreach ($data as $row) {
            yield $row;
        }
    }

    public function count(
        array $filter,
        array $parameters
    ): int {
        return count($this->collectData());
    }

    protected function collectData(): array
    {
        $data = $this->file_list->getFileList();
        foreach ($data as $k => $v) {
            $data[$k]["file_id"] = 0;
            $data[$k]["file_name"] = "";
            $entry = explode("_", $v["entry"]);
            if (count($entry) === 4) {
                if ($entry[1] === "") {
                    $file_id = (int) $entry[3];
                    if (\ilObject::_lookupType($file_id) === "file") {
                        $data[$k]["file_id"] = $file_id;
                        $file = new \ilObjFile($file_id, false);
                        $data[$k]["file_name"] = $file->getFileName();
                    }
                }
            }
            $class = ($v["class"] == "")
                ? "FileListItem"
                : $v["class"];
            $data[$k]["class"] = $class;
            $data[$k]["id"] = $v["hier_id"] . ":" . $v["pc_id"];
        }
        return $data;
    }

    public function isFieldNumeric(string $field): bool
    {
        return false;
    }
}
