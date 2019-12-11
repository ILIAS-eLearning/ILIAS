<?php

namespace ILIAS\FileDelivery;

/**
 * Interface ilFileDeliveryType
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilFileDeliveryType
{

    /**
     * @param string $path_to_file
     *
     * @return bool
     */
    public function doesFileExists($path_to_file);


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
    public function deliver($path_to_file, $file_marked_to_delete);


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
