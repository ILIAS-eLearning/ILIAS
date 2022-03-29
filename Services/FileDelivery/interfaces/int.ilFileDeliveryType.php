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
 * Interface ilFileDeliveryType
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilFileDeliveryType
{

    /**
     * @return bool
     */
    public function doesFileExists(string $path_to_file) : bool;


    /**
     * @param string $path_to_file
     *
     * @return bool
     */
    public function prepare(string $path_to_file) : bool;


    /**
     * @param string  $path_to_file          absolute path to file
     *
     * @param    bool $file_marked_to_delete This is needed at this point for header-based delivery
     *                                       methods
     *
     * @return void
     */
    public function deliver(string $path_to_file, bool $file_marked_to_delete) : void;


    /**
     * @param string $path_to_file
     *
     * @return bool
     */
    public function handleFileDeletion(string $path_to_file) : bool;


    public function supportsInlineDelivery() : bool;

    public function supportsAttachmentDelivery() : bool;

    public function supportsStreaming() : bool;
}
