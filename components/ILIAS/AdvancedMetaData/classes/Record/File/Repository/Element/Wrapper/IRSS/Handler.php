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

namespace ILIAS\AdvancedMetaData\Record\File\Repository\Element\Wrapper\IRSS;

use DateTimeImmutable;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\Wrapper\IRSS\HandlerInterface as FileRepositoryElementIRSSWrapperInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Stakeholder\HandlerInterface as FileRepositoryStakeholderInterface;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Services as IRSS;
use SimpleXMLElement;

class Handler implements FileRepositoryElementIRSSWrapperInterface
{
    protected IRSS $irss;
    protected string $resource_id_serialized;

    public function __construct(
        IRSS $irss
    ) {
        $this->irss = $irss;
    }

    public function withResourceIdSerialized(
        string $resource_id_serialized
    ): FileRepositoryElementIRSSWrapperInterface {
        $clone = clone $this;
        $clone->resource_id_serialized = $resource_id_serialized;
        return $clone;
    }

    public function getResourceIdSerialized(): string
    {
        return $this->resource_id_serialized;
    }

    public function getCreationDate(): DateTimeImmutable
    {
        return $this->irss->manage()->getResource($this->getResourceIdentification())->getCurrentRevision()->getInformation()->getCreationDate();
    }

    public function getFileName(): string
    {
        return $this->irss->manage()->getResource($this->getResourceIdentification())->getCurrentRevision()->getInformation()->getTitle();
    }

    public function getResourceSize(): int
    {
        return $this->irss->manage()->getResource($this->getResourceIdentification())->getCurrentRevision()->getInformation()->getSize();
    }

    public function getRecords(): array
    {
        $records = [];
        $stream = $this->irss->consume()->stream($this->getResourceIdentification())->getStream();
        $xml = simplexml_load_string($stream->getContents());
        if ($xml instanceof SimpleXMLElement) {
            $records = array_map(function ($title) { return (string) $title; }, $xml->xpath('Record/Title'));
        }
        return $records;
    }

    public function deleteResource(
        FileRepositoryStakeholderInterface $stakeholder
    ): void {
        $this->irss->manage()->remove($this->getResourceIdentification(), $stakeholder);
    }

    public function resourceExists(): bool
    {
        return !is_null($this->getResourceIdentification());
    }

    public function download(?string $new_filename = null): void
    {
        $download = $this->irss->consume()->download($this->getResourceIdentification());
        if (!is_null($new_filename)) {
            $download = $download->overrideFileName($new_filename);
        }
        $download->run();
    }

    public function getResourceIdentification(): ResourceIdentification|null
    {
        return $this->irss->manage()->find($this->resource_id_serialized);
    }
}
