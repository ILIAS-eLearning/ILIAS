<?php

namespace ILIAS\FileDelivery\FileDeliveryTypes;

use ILIAS\FileDelivery\ilFileDeliveryType;
use ILIAS\HTTP\GlobalHttpState;

require_once('./Services/FileDelivery/interfaces/int.ilFileDeliveryType.php');

/**
 * Class XSendfile
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @since   5.3
 * @version 1.0
 */
final class XSendfile implements ilFileDeliveryType
{
    use HeaderBasedDeliveryHelper;
    const X_SENDFILE = 'X-Sendfile';
    const X_SENDFILE_TEMPORARY = 'X-Sendfile-Temporary';
    /**
     * @var GlobalHttpState $httpService
     */
    private $httpService;


    /**
     * PHP constructor.
     *
     * @param GlobalHttpState $httpState
     *
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
        //	Nothing has to be done here
        return true;
    }


    /**
     * @inheritdoc
     */
    public function deliver($path_to_file, $file_marked_to_delete)
    {
        $delivery = function () use ($path_to_file) {
            $response = $this->httpService->response()
                ->withHeader(self::X_SENDFILE, realpath($path_to_file));
            $this->httpService->saveResponse($response);
            $this->httpService->sendResponse();
        };

        if ($file_marked_to_delete) {
            $this->sendFileUnbufferedUsingHeaders($delivery);
        } else {
            $delivery();
        }

        return true;
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
        return true;
    }


    /**
     * @inheritdoc
     */
    public function handleFileDeletion($path_to_file)
    {
        unlink($path_to_file);
    }
}
