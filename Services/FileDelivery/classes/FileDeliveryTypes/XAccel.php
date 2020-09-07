<?php

namespace ILIAS\FileDelivery\FileDeliveryTypes;

use ILIAS\FileDelivery\ilFileDeliveryType;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\HTTP\Response\ResponseHeader;

require_once('./Services/FileDelivery/interfaces/int.ilFileDeliveryType.php');

/**
 * Class XAccel
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @since   5.3
 * @version 1.0
 */
final class XAccel implements ilFileDeliveryType
{
    use  HeaderBasedDeliveryHelper;
    const DATA = 'data';
    const SECURED_DATA = 'secured-data';
    /**
     * @var GlobalHttpState $httpService
     */
    private $httpService;
    const X_ACCEL_REDIRECT = 'X-Accel-Redirect';


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
        $response = $this->httpService->response()->withHeader(ResponseHeader::CONTENT_TYPE, '');

        $this->httpService->saveResponse($response);

        return true;
    }


    /**
     * @inheritdoc
     */
    public function deliver($path_to_file, $file_marked_to_delete)
    {
        // There is currently no way to delete the file after delivery
        if (strpos($path_to_file, './' . self::DATA . '/') === 0) {
            $path_to_file = str_replace('./' . self::DATA . '/', '/' . self::SECURED_DATA
                                                                 . '/', $path_to_file);
        }

        $response = $this->httpService->response();
        $delivery = function () use ($path_to_file, $response) {
            $response = $response->withHeader(self::X_ACCEL_REDIRECT, $path_to_file);
            $this->httpService->saveResponse($response);
            $this->httpService->sendResponse();
        };

        if ($file_marked_to_delete) {
            $this->sendFileUnbufferedUsingHeaders($delivery);
        } else {
            $delivery();
        }
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
        // No possibilities to do this at the moment
    }
}
