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

namespace ILIAS\Export\ImportHandler\File\XML;

use DOMDocument;
use ILIAS\Export\ImportHandler\File\Handler as File;
use ILIAS\Export\ImportHandler\I\File\Namespace\FactoryInterface as FileNamespaceFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\Namespace\HandlerInterface as FileNamespaceInterface;
use ILIAS\Export\ImportHandler\I\File\XML\HandlerInterface as XMLFileInterface;
use ILIAS\Export\ImportStatus\Exception\ilException as ImportStatusException;
use ILIAS\Export\ImportStatus\I\ilFactoryInterface as ImportStatusFactoryInterface;
use ILIAS\Export\ImportStatus\StatusType as StatusType;
use SplFileInfo;

class Handler extends File implements XMLFileInterface
{
    protected ImportStatusFactoryInterface $status;

    public function __construct(
        FileNamespaceFactoryInterface $namespace_factory,
        ImportStatusFactoryInterface $status
    ) {
        parent::__construct($namespace_factory);
        $this->status = $status;
    }

    public function withFileInfo(
        SplFileInfo $file_info
    ): XMLFileInterface {
        $clone = clone $this;
        $clone->spl_file_info = $file_info;
        return $clone;
    }

    public function withAdditionalNamespace(
        FileNamespaceInterface $namespace_handler
    ): XMLFileInterface {
        $clone = clone $this;
        $clone->namespaces = $clone->namespaces->withElement($namespace_handler);
        return $clone;
    }

    /**
     * @throws ImportStatusException
     */
    public function loadDomDocument(): DOMDocument
    {
        $old_val = libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->load($this->getFilePath());
        $status_collection = $this->status->collection();
        $errors = libxml_get_errors();
        libxml_clear_errors();
        foreach ($errors as $error) {
            $status_collection = $status_collection->withAddedStatus(
                $this->status->handler()->withType(StatusType::FAILED)->withContent(
                    $this->status->content()->builder()->string()->withString(
                        "Error loading dom document:" .
                        "<br>  XML: " . $this->getSubPathToDirBeginningAtPathEnd('temp')->getFilePath() .
                        "<br>ERROR: " . $error->message
                    )
                )
            );
        }
        if ($status_collection->hasStatusType(StatusType::FAILED)) {
            $exception = $this->status->exception($status_collection->toString(StatusType::FAILED));
            $exception->setStatuses($status_collection);
            throw $exception;
        }
        libxml_use_internal_errors($old_val);
        return $doc;
    }
}
