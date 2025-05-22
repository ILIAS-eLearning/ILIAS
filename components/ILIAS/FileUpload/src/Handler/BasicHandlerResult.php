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

namespace ILIAS\FileUpload\Handler;

use ILIAS\UI\Component\Input\Field\UploadHandler;

/**
 * Class BasicHandlerResult
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
final class BasicHandlerResult implements HandlerResult
{
    /**
     * BasicHandlerResult constructor.
     */
    public function __construct(private string $file_identification_key, private int $status, private string $file_identifier, private string $message)
    {
    }


    /**
     * @inheritDoc
     */
    public function getStatus(): int
    {
        return $this->status;
    }


    /**
     * @inheritDoc
     */
    public function getFileIdentifier(): string
    {
        return $this->file_identifier;
    }


    /**
     * @inheritDoc
     */
    final public function jsonSerialize(): array
    {
        $str = $this->file_identification_key ?? UploadHandler::DEFAULT_FILE_ID_PARAMETER;

        return [
            'status' => $this->status,
            'message' => $this->message,
            $str => $this->file_identifier,
        ];
    }


    /**
     * @inheritDoc
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
