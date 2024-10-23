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

namespace ILIAS\Export\ExportHandler\I\Table\RowId;

interface HandlerInterface
{
    public const SEPARATOR = ':';

    public function withExportOptionId(string $export_option_id): HandlerInterface;

    public function withFileIdentifier(string $file_info_id): HandlerInterface;

    public function withCompositId(string $composit_id): HandlerInterface;

    public function getCompositId(): string;

    public function getFileIdentifier(): string;

    public function getExportOptionId(): string;
}
