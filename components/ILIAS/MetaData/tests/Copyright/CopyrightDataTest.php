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

namespace ILIAS\MetaData\Copyright;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Copyright\CopyrightData;
use ILIAS\Data\URI;

class CopyrightDataTest extends TestCase
{
    protected function getMockURI(): URI
    {
        return $this->createMock(URI::class);
    }

    protected function getData(?URI $image_link, string $image_file): CopyrightData
    {
        return new CopyrightData(
            'name',
            $this->getMockURI(),
            $image_link,
            $image_file,
            'alt',
            false
        );
    }

    public function testImage(): void
    {
        $uri = $this->getMockURI();

        $data_without_image = $this->getData(null, '');
        $data_with_file_image = $this->getData(null, 'file identifier');
        $data_with_link_image = $this->getData($uri, '');

        $this->assertSame('', $data_without_image->imageFile());
        $this->assertNull($data_without_image->imageLink());

        $this->assertSame('file identifier', $data_with_file_image->imageFile());
        $this->assertNull($data_with_file_image->imageLink());

        $this->assertSame('', $data_with_link_image->imageFile());
        $this->assertSame($uri, $data_with_link_image->imageLink());
    }

    public function testHasImage(): void
    {
        $data_without_image = $this->getData(null, '');
        $data_with_file_image = $this->getData(null, 'file identifier');
        $data_with_link_image = $this->getData($this->getMockURI(), '');

        $this->assertFalse($data_without_image->hasImage());
        $this->assertTrue($data_with_file_image->hasImage());
        $this->assertTrue($data_with_link_image->hasImage());
    }

    public function testIsImageLink(): void
    {
        $data_without_image = $this->getData(null, '');
        $data_with_file_image = $this->getData(null, 'file identifier');
        $data_with_link_image = $this->getData($this->getMockURI(), '');

        $this->assertFalse($data_without_image->isImageLink());
        $this->assertFalse($data_with_file_image->isImageLink());
        $this->assertTrue($data_with_link_image->isImageLink());
    }
}
