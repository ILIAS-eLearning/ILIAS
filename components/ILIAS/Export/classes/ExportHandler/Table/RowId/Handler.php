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

namespace ILIAS\Export\ExportHandler\Table\RowId;

use ILIAS\Export\ExportHandler\I\Table\RowId\HandlerInterface as ilExportHandlerTableRowIdInterface;

class Handler implements ilExportHandlerTableRowIdInterface
{
    protected string $export_option_id;
    protected string $file_info_id;

    public function withExportOptionId(string $export_option_id): ilExportHandlerTableRowIdInterface
    {
        $clone = clone $this;
        $clone->export_option_id = $export_option_id;
        return $clone;
    }

    public function withFileIdentifier(string $file_info_id): ilExportHandlerTableRowIdInterface
    {
        $clone = clone $this;
        $clone->file_info_id = $file_info_id;
        return $clone;
    }

    public function withCompositId(string $composit_id): ilExportHandlerTableRowIdInterface
    {
        $clone = clone $this;
        $clone->export_option_id = substr($composit_id, 0, stripos($composit_id, self::SEPARATOR));
        ;
        $clone->file_info_id = substr($composit_id, stripos($composit_id, self::SEPARATOR) + 1);
        return $clone;
    }

    public function getCompositId(): string
    {
        return $this->export_option_id . self::SEPARATOR . $this->file_info_id;
    }

    public function getFileIdentifier(): string
    {
        return $this->file_info_id;
    }

    public function getExportOptionId(): string
    {
        return $this->export_option_id;
    }
}
