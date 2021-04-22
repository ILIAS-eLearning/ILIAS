<?php

namespace ILIAS\ResourceStorage\Policy;

use ILIAS\MainMenu\Tests\DummyIDGenerator;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\ResourceStorage\AbstractBaseResourceBuilderTest;

/**
 * Class FileNamePolicyTest
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class FileNamePolicyTest extends AbstractBaseResourceBuilderTest
{
    /**
     * @param string $denied_ending
     * @return ResourceBuilder
     */
    protected function getResourceBuilder(string $denied_ending) : ResourceBuilder
    {
        $policy = $this->getFileNamePolicy($denied_ending);
        $resource_builder = new ResourceBuilder(
            $this->storage_handler,
            $this->revision_repository,
            $this->resource_repository,
            $this->information_repository,
            $this->stakeholder_repository,
            $this->locking,
            $policy
        );
        return $resource_builder;
    }

    /**
     * @return FileNamePolicy
     */
    protected function getFileNamePolicy(string $denied_ending)
    {
        return new class($denied_ending) implements FileNamePolicy {
            public function __construct(string $denied_ending)
            {
                $this->denied_ending = $denied_ending;
            }

            public function check(string $extension) : bool
            {
                if ($this->denied_ending === $extension) {
                    throw new FileNamePolicyException('ERROR');
                }
                return true;
            }

            public function isValidExtension(string $extension) : bool
            {
                return $this->denied_ending !== $extension;
            }

            public function isBlockedExtension(string $extension) : bool
            {
                return $this->denied_ending === $extension;
            }

            public function prepareFileNameForConsumer(string $filename_with_extension) : string
            {
                return $filename_with_extension;
            }

        };
    }

    public function testDeniedFileEnding() : void
    {
        $denied_ending = 'xml';
        $resource_builder = $this->getResourceBuilder($denied_ending);

        // EXPECTED VALUES
        $expected_file_name = 'info.' . $denied_ending;

        // MOCK
        list($upload_result, $info_resolver, $identification) = $this->mockResourceAndRevision(
            $expected_file_name,
            "",
            0, 1, 0
        );

        // RUN
        $resource = $resource_builder->new(
            $upload_result,
            $info_resolver
        );

        $this->expectException(FileNamePolicyException::class);
        $resource_builder->store($resource);
    }

    public function testValidFileEnding() : void
    {
        $denied_ending = 'xml';
        $resource_builder = $this->getResourceBuilder($denied_ending);

        // EXPECTED VALUES
        $expected_file_name = 'info.pdf';

        // MOCK
        list($upload_result, $info_resolver, $identification) = $this->mockResourceAndRevision(
            $expected_file_name,
            "",
            0, 1, 0
        );

        // RUN
        $resource = $resource_builder->new(
            $upload_result,
            $info_resolver
        );

        $resource_builder->store($resource);
    }
}

