<?php
declare(strict_types=1);

namespace ILIAS\FileDelivery;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Interface ilFileDeliveryService
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilFileDeliveryService
{
    public static function deliverFileAttached(
        string $path_to_file,
        ?string $download_file_name = null,
        ?string $mime_type = null,
        bool $delete_file = false
    ) : void;

    public static function streamVideoInline(
        string $path_to_file,
        ?string $download_file_name = null
    ) : void;

    public static function deliverFileInline(
        string $path_to_file,
        ?string $download_file_name = null
    ) : void;

    /**
     * Converts a UTF-8 filename to ASCII
     */
    public static function returnASCIIFileName(string $original_filename) : string;
}
