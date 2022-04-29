<?php

namespace ILIAS\ResourceStorage\Resource;

use ILIAS\ResourceStorage\AbstractBaseResourceBuilderTest;

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
 * Class ResourceBuilderTest
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ResourceBuilderTest extends AbstractBaseResourceBuilderTest
{
    public function testNewUpload() : void
    {
        // EXPECTED VALUES
        $expected_file_name = 'info.xml';
        $expected_owner_id = 6;
        $expected_version_number = 99;
        $expected_mime_type = 'text/xml';
        $expected_size = 128;

        $resource_builder = new ResourceBuilder(
            $this->storage_handler_factory,
            $this->revision_repository,
            $this->resource_repository,
            $this->information_repository,
            $this->stakeholder_repository,
            $this->locking
        );

        // MOCK
        list($upload_result, $info_resolver, $identification) = $this->mockResourceAndRevision(
            $expected_file_name,
            $expected_mime_type,
            $expected_size,
            $expected_version_number,
            $expected_owner_id
        );

        // RUN
        $resource = $resource_builder->new(
            $upload_result,
            $info_resolver
        );

        $this->assertEquals($identification->serialize(), $resource->getIdentification()->serialize());
        $this->assertEquals($expected_version_number, $resource->getCurrentRevision()->getVersionNumber());
        $this->assertEquals($expected_version_number, $resource->getMaxRevision());
        $this->assertEquals($expected_file_name, $resource->getCurrentRevision()->getTitle());
        $this->assertEquals($expected_owner_id, $resource->getCurrentRevision()->getOwnerId());
        $this->assertEquals($expected_file_name, $resource->getCurrentRevision()->getInformation()->getTitle());
        $this->assertEquals($expected_mime_type, $resource->getCurrentRevision()->getInformation()->getMimeType());
        $this->assertEquals($expected_size, $resource->getCurrentRevision()->getInformation()->getSize());
    }
}
