<?php

declare(strict_types=1);

namespace ILIAS\FileDelivery\FileDeliveryTypes;

use ILIAS\FileDelivery\ilFileDeliveryType;
use ILIAS\HTTP\Services;

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
    protected \ILIAS\HTTP\Services $httpService;


    /**
     * PHP constructor.
     *
     * @param Services $httpState
     */
    public function __construct(Services $httpState)
    {
        $this->httpService = $httpState;
    }


    /**
     * @inheritDoc
     */
    public function doesFileExists(string $path_to_file): bool
    {
        return is_readable($path_to_file);
    }


    /**
     * @inheritdoc
     */
    public function prepare(string $path_to_file): bool
    {
        set_time_limit(0);
        $this->file = fopen($path_to_file, "rb");

        return true;
    }


    /**
     * @inheritdoc
     */
    public function deliver(string $path_to_file, bool $file_marked_to_delete): void
    {
        $this->httpService->sendResponse();
        fpassthru($this->file);
        // Fix for mantis 22594
        fclose($this->file);
    }


    /**
     * @inheritdoc
     */
    public function supportsInlineDelivery(): bool
    {
        return true;
    }


    /**
     * @inheritdoc
     */
    public function supportsAttachmentDelivery(): bool
    {
        return true;
    }


    /**
     * @inheritdoc
     */
    public function supportsStreaming(): bool
    {
        return false;
    }


    /**
     * @inheritdoc
     */
    public function handleFileDeletion(string $path_to_file): bool
    {
        return unlink($path_to_file);
    }
}
