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
/**
 * Meta Data to XML class
 * @package ilias-core
 * @version $Id$
 * @deprecated will be removed with ILIAS 11, LOM should only be exported as a tail dependency
 */
class ilMD2XML extends ilXmlWriter
{
    public ilMD $md_obj;
    public bool $export_mode = false;

    public function __construct(int $a_rbac_id, int $a_obj_id, string $a_type)
    {
        $this->md_obj = new ilMD($a_rbac_id, $a_obj_id, $a_type);
        parent::__construct();
    }

    public function setExportMode(bool $a_export_mode = true): void
    {
        $this->export_mode = $a_export_mode;
    }

    public function getExportMode(): bool
    {
        return $this->export_mode;
    }

    public function startExport(): void
    {
        // Starts the xml export and calls all element classes
        $this->md_obj->setExportMode($this->getExportMode());
        $this->md_obj->toXML($this);
    }

    public function getXML(): string
    {
        return $this->xmlDumpMem(false);
    }
}
