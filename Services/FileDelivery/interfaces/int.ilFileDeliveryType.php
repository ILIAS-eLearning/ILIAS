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
    public function doesFileExists(string $path_to_file);


    /**
     * @param $path_to_file
     *
     * @return bool
     */
    public function prepare($path_to_file);


    /**
     * @param string  $path_to_file          absolute path to file
     *
     * @param    bool $file_marked_to_delete This is needed at this point for header-based delivery
     *                                       methods
     *
     * @return bool
     */
    public function deliver(string $path_to_file, bool $file_marked_to_delete);


    /**
     * @param $path_to_file
     *
     * @return bool
     */
    public function handleFileDeletion($path_to_file);


    /**
     * @return bool
     */
    public function supportsInlineDelivery();


    /**
     * @return bool
     */
    public function supportsAttachmentDelivery();


    /**
     * @return bool
     */
    public function supportsStreaming();
}
