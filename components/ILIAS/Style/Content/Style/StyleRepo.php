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

namespace ILIAS\Style\Content\Style;

use ilDBInterface;
use ILIAS\Style\Content\InternalDataService;
use ILIAS\Repository\IRSS\IRSSWrapper;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

class StyleRepo
{
    protected ilDBInterface $db;
    protected InternalDataService $factory;

    public function __construct(
        ilDBInterface $db,
        InternalDataService $factory,
        protected IRSSWrapper $irss
    ) {
        $this->db = $db;
        $this->factory = $factory;
    }

    public function readRid(int $style_id): string
    {
        $set = $this->db->queryF(
            "SELECT rid FROM style_data " .
            " WHERE id = %s ",
            ["integer"],
            [$style_id]
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            return (string) $rec["rid"];
        }
        return "";
    }

    protected function createRid(
        int $style_id,
        ResourceStakeholder $stakeholder
    ): string {
        $rid = $this->irss->createContainer($stakeholder);
        $this->db->update(
            "style_data",
            [
            "rid" => ["string", $rid]
        ],
            [    // where
                "id" => ["integer", $style_id]
            ]
        );
        return $rid;
    }

    public function createContainerFromLocalZip(
        int $style_id,
        string $local_zip_path,
        ResourceStakeholder $stakeholder
    ): string {
        $rid = $this->irss->createContainerFromLocalZip($local_zip_path, $stakeholder);
        $this->db->update(
            "style_data",
            [
            "rid" => ["string", $rid]
        ],
            [    // where
                "id" => ["integer", $style_id]
            ]
        );
        return $rid;
    }

    public function createContainerFromLocalDir(
        int $style_id,
        string $local_dir_path,
        ResourceStakeholder $stakeholder,
        string $container_path = "",
        bool $recursive = true
    ): string {
        $rid = $this->irss->createContainerFromLocalDir($local_dir_path, $stakeholder, $container_path, $recursive);
        $this->db->update(
            "style_data",
            [
            "rid" => ["string", $rid]
        ],
            [    // where
                "id" => ["integer", $style_id]
            ]
        );
        return $rid;
    }

    public function getOrCreateRid(
        int $style_id,
        ResourceStakeholder $stakeholder
    ): string {
        $rid = $this->readRid($style_id);
        if ($rid === "") {
            $rid = $this->createRid(
                $style_id,
                $stakeholder
            );
        }
        return $rid;
    }

    public function writeCss(
        int $style_id,
        string $css,
        ResourceStakeholder $stakeholder
    ): void {
        $rid = $this->getOrCreateRid($style_id, $stakeholder);
        $this->irss->addStringToContainer($rid, $css, "style.css");
    }

    public function getPath(
        int $style_id,
        bool $add_random = true,
        bool $add_token = true
    ): string {
        $rid = $this->readRid($style_id);

        if ($rid !== "") {
            $path = $this->irss->getContainerUri(
                $rid,
                "style.css"
            );
            if ($add_random) {
                $random = new \ilRandom();
                $rand = $random->int(1, 999999);
                $path .= "?dummy=$rand";
            }
        } else {
            $path = \ilFileUtils::getWebspaceDir("output") . "/css/style_" . $style_id . ".css";
            if ($add_random) {
                $random = new \ilRandom();
                $rand = $random->int(1, 999999);
                $path .= "?dummy=$rand";
            }
            if ($add_token) {
                $path = \ilWACSignedPath::signFile($path);
            }
        }
        return $path;
    }

    public function getResourceIdentification(int $style_id): ?ResourceIdentification
    {
        $rid = $this->readRid($style_id);
        if ($rid !== "") {
            return $this->irss->getResourceIdForIdString($rid);
        }
        return null;
    }

    public function cloneResourceContainer(
        int $from_style_id,
        int $to_style_id
    ): void {
        $from_rid = $this->readRid($from_style_id);
        $to_rid = $this->irss->cloneContainer($from_rid);
        if ($to_rid !== "") {
            $this->db->update(
                "style_data",
                [
                    "rid" => ["string", $to_rid]
                ],
                [    // where
                     "id" => ["integer", $to_style_id]
                ]
            );
        }
    }

}
