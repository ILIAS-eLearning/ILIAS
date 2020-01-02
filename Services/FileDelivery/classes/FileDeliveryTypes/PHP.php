<?php

namespace ILIAS\FileDelivery\FileDeliveryTypes;

use ILIAS\FileDelivery\ilFileDeliveryType;
use ILIAS\HTTP\GlobalHttpState;

require_once('./Services/FileDelivery/interfaces/int.ilFileDeliveryType.php');

/**
 * Class PHP
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @since   5.3
 * @version 1.0
 *
 * @Internal
 */
final class PHP implements ilFileDeliveryType
{

    /**
     * @var resource
     */
    protected $file;
    /**
     * @var GlobalHttpState $httpService
     */
    protected $httpService;


    /**
     * PHP constructor.
     *
     * @param GlobalHttpState $httpState
     */
    public function __construct(GlobalHttpState $httpState)
    {
        $this->httpService = $httpState;
    }


    /**
     * @inheritDoc
     */
    public function doesFileExists($path_to_file)
    {
        return is_readable($path_to_file);
    }


    /**
     * @inheritdoc
     */
    public function prepare($path_to_file)
    {
        set_time_limit(0);
        $this->file = fopen($path_to_file, "rb");
    }


    /**
     * @inheritdoc
     */
    public function deliver($path_to_file, $file_marked_to_delete)
    {
        $this->httpService->sendResponse();
        fpassthru($this->file);
        // Fix for mantis 22594
        fclose($this->file);
    }


    /**
     * @inheritdoc
     */
    public function supportsInlineDelivery()
    {
        return true;
    }


    /**
     * @inheritdoc
     */
    public function supportsAttachmentDelivery()
    {
        return true;
    }


    /**
     * @inheritdoc
     */
    public function supportsStreaming()
    {
        return false;
    }


    /**
     * @inheritdoc
     */
    public function handleFileDeletion($path_to_file)
    {
        return unlink($path_to_file);
    }
}
