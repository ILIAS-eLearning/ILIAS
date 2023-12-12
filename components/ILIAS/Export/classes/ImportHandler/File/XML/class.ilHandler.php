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

namespace ImportHandler\File\XML;

use DOMDocument;
use ILIAS\DI\Exceptions\Exception;
use ilImportException;
use ImportHandler\File\ilHandler as ilFileHandler;
use ImportHandler\I\File\ilHandlerInterface as ilFileHandlerInterface;
use ImportHandler\I\File\Namespace\ilFactoryInterface as ilFileNamespaceFactoryInterface;
use ImportHandler\I\File\Namespace\ilHandlerInterface as ilFileNamespaceHandlerInterface;
use ImportHandler\I\File\Validation\Set\ilCollectionInterface as ilFileValidationSetCollectionInterface;
use ImportHandler\I\File\XML\ilHandlerInterface as ilXMLFileHandlerInterface;
use ImportHandler\I\File\XSD\ilHandlerInterface as ilXSDFileHandlerInterface;
use ImportStatus\Exception\ilException as ilImportStatusException;
use ImportStatus\I\ilFactoryInterface as ilImportStatusFactoryInterface;
use ImportStatus\I\ilCollectionInterface as ilImportStatusCollectioninterface;
use ImportStatus\StatusType;
use Schema\ilXmlSchemaFactory;
use SplFileInfo;

class ilHandler extends ilFileHandler implements ilXMLFileHandlerInterface
{
    protected ilImportStatusFactoryInterface $status;

    public function __construct(
        ilFileNamespaceFactoryInterface $namespace,
        ilImportStatusFactoryInterface $status
    ) {
        parent::__construct($namespace);
        $this->status = $status;
    }

    public function withFileInfo(SplFileInfo $file_info): ilHandler
    {
        $clone = clone $this;
        $clone->xml_file_info = $file_info;
        return $clone;
    }

    public function withAdditionalNamespace(ilFileNamespaceHandlerInterface $namespace_handler): ilHandler
    {
        $clone = clone $this;
        $clone->namespaces = $clone->namespaces->withElement($namespace_handler);
        return $clone;
    }

    /**
     * @throws ilImportStatusException
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
