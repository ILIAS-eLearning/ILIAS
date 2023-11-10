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
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Link\Link;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Implementation\Component\Image\Image as IImage;
use ILIAS\UI\Implementation\Component\Link\Link as ILink;
use ILIAS\UI\Implementation\Component\Legacy\Legacy as ILegacy;
use ILIAS\Filesystem\Filesystem as WebFiles;
use ILIAS\UI\Factory;
use ILIAS\ResourceStorage\Services as IRSS;
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\Data\URI;
use ILIAS\UI\Component\Link\Relationship;

class RendererTest extends TestCase
{
    protected function getMockRenderer(
        Image $image,
        Link $link,
        Legacy $legacy,
        string $src_from_irss
    ): Renderer {
        return new class ($image, $link, $legacy, $src_from_irss) extends Renderer {
            public function __construct(
                protected Image $image,
                protected Link $link,
                protected Legacy $legacy,
                protected string $src_from_irss
            ) {
            }

            protected function getFallBackSrc(): string
            {
                return 'fallback src';
            }

            protected function standardImage(string $src, string $alt): Image
            {
                /** @noinspection PhpUndefinedMethodInspection */
                $this->image->checkParams($src, $alt);
                return $this->image;
            }

            protected function standardLink(string $label, string $action): Link
            {
                /** @noinspection PhpUndefinedMethodInspection */
                $this->link->checkParams($label, $action);
                return $this->link;
            }

            protected function textInLegacy(string $text): Legacy
            {
                /** @noinspection PhpUndefinedMethodInspection */
                $this->legacy->checkParams($text);
                return $this->legacy;
            }

            protected function getSourceFromIRSS(string $string_id): string
            {
                return $this->src_from_irss;
            }
        };
    }

    protected function getMockImage(): MockObject|Image
    {
        return $this->getMockBuilder(IImage::class)
                    ->disableOriginalConstructor()
                    ->onlyMethods(['withAction'])
                    ->addMethods(['checkParams'])
                    ->getMock();
    }

    protected function getMockLink(): MockObject|Link
    {
        return $this->getMockBuilder(ILink::class)
                    ->disableOriginalConstructor()
                    ->onlyMethods(['withAdditionalRelationshipToReferencedResource'])
                    ->addMethods(['checkParams'])
                    ->getMock();
    }

    protected function getMockLegacy(): MockObject|Legacy
    {
        return $this->getMockBuilder(ILegacy::class)
                    ->disableOriginalConstructor()
                    ->addMethods(['checkParams'])
                    ->getMock();
    }

    protected function getMockURI(string $link): URI
    {
        $uri = $this->getMockBuilder(URI::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $uri->method('__toString')->willReturn($link);
        return $uri;
    }

    public function testToUIComponentsWithLinkAndImage(): void
    {
        $image = $this->getMockImage();
        $image->expects($this->once())
              ->method('checkParams')
              ->with('image link', 'alt text');
        $image->expects($this->once())
              ->method('withAction')
              ->with('link');
        $link = $this->getMockLink();
        $link->expects($this->once())
             ->method('checkParams')
             ->with('full name', 'link');
        $link->expects($this->once())
             ->method('withAdditionalRelationshipToReferencedResource')
             ->with(Relationship::LICENSE);
        $uri = $this->getMockURI('link');
        $img_uri = $this->getMockURI('image link');

        $renderer = $this->getMockRenderer(
            $image,
            $link,
            $this->getMockLegacy(),
            ''
        );
        $data = new class ($uri, $img_uri) extends NullCopyrightData {
            public function __construct(
                protected URI $uri,
                protected URI $img_uri
            ) {
            }

            public function fullName(): string
            {
                return 'full name';
            }

            public function link(): ?URI
            {
                return $this->uri;
            }

            public function hasImage(): bool
            {
                return true;
            }

            public function isImageLink(): bool
            {
                return true;
            }

            public function imageLink(): ?URI
            {
                return $this->img_uri;
            }

            public function altText(): string
            {
                return 'alt text';
            }
        };

        $result = $renderer->toUIComponents($data);
        $this->assertSame(2, count($result));
        $this->assertInstanceOf(Image::class, $result[0]);
        $this->assertInstanceOf(Link::class, $result[1]);
    }

    public function testToUIComponentsEmpty(): void
    {
        $legacy = $this->getMockLegacy();

        $renderer = $this->getMockRenderer(
            $this->getMockImage(),
            $this->getMockLink(),
            $legacy,
            ''
        );

        $result = $renderer->toUIComponents(new NullCopyrightData());
        $this->assertSame(0, count($result));
    }

    public function testToUIComponentsWithoutLink(): void
    {
        $legacy = $this->getMockLegacy();
        $legacy->expects($this->once())
               ->method('checkParams')
               ->with('full name');

        $image = $this->getMockImage();
        $image->expects($this->once())
              ->method('checkParams')
              ->with('image link', 'alt text');
        $image->expects($this->never())->method('withAction');
        $uri = $this->getMockURI('image link');

        $renderer = $this->getMockRenderer(
            $image,
            $this->getMockLink(),
            $legacy,
            ''
        );
        $data = new class ($uri) extends NullCopyrightData {
            public function __construct(protected URI $uri)
            {
            }

            public function fullName(): string
            {
                return 'full name';
            }

            public function hasImage(): bool
            {
                return true;
            }

            public function isImageLink(): bool
            {
                return true;
            }

            public function imageLink(): ?URI
            {
                return $this->uri;
            }

            public function altText(): string
            {
                return 'alt text';
            }
        };

        $result = $renderer->toUIComponents($data);
        $this->assertSame(2, count($result));
        $this->assertInstanceOf(Image::class, $result[0]);
        $this->assertInstanceOf(Legacy::class, $result[1]);
    }

    public function testToUIComponentsWithLinkNoImage(): void
    {
        $link = $this->getMockLink();
        $link->expects($this->once())
             ->method('checkParams')
             ->with('full name', 'link');
        $link->expects($this->once())
             ->method('withAdditionalRelationshipToReferencedResource')
             ->with(Relationship::LICENSE);
        $uri = $this->getMockURI('link');

        $renderer = $this->getMockRenderer(
            $this->getMockImage(),
            $link,
            $this->getMockLegacy(),
            ''
        );
        $data = new class ($uri) extends NullCopyrightData {
            public function __construct(protected URI $uri)
            {
            }

            public function fullName(): string
            {
                return 'full name';
            }

            public function link(): ?URI
            {
                return $this->uri;
            }
        };

        $result = $renderer->toUIComponents($data);
        $this->assertSame(1, count($result));
        $this->assertInstanceOf(Link::class, $result[0]);
    }

    public function testToUIComponentsLinkWithoutFullName(): void
    {
        $link = $this->getMockLink();
        $link->expects($this->once())
             ->method('checkParams')
             ->with('link', 'link');
        $link->expects($this->once())
             ->method('withAdditionalRelationshipToReferencedResource')
             ->with(Relationship::LICENSE);
        $uri = $this->getMockURI('link');

        $renderer = $this->getMockRenderer(
            $this->getMockImage(),
            $link,
            $this->getMockLegacy(),
            ''
        );
        $data = new class ($uri) extends NullCopyrightData {
            public function __construct(protected URI $uri)
            {
            }

            public function link(): ?URI
            {
                return $this->uri;
            }
        };

        $result = $renderer->toUIComponents($data);
        $this->assertSame(1, count($result));
        $this->assertInstanceOf(Link::class, $result[0]);
    }

    public function testToUIComponentsWithImageFromLink(): void
    {
        $image = $this->getMockImage();
        $image->expects($this->once())
              ->method('checkParams')
              ->with('image link', 'alt text');
        $image->expects($this->never())->method('withAction');
        $uri = $this->getMockURI('image link');

        $renderer = $this->getMockRenderer(
            $image,
            $this->getMockLink(),
            $this->getMockLegacy(),
            ''
        );
        $data = new class ($uri) extends NullCopyrightData {
            public function __construct(protected URI $uri)
            {
            }

            public function hasImage(): bool
            {
                return true;
            }

            public function isImageLink(): bool
            {
                return true;
            }

            public function imageLink(): ?URI
            {
                return $this->uri;
            }

            public function altText(): string
            {
                return 'alt text';
            }
        };

        $result = $renderer->toUIComponents($data);
        $this->assertSame(1, count($result));
        $this->assertInstanceOf(Image::class, $result[0]);
    }

    public function testToUIComponentsWithImageFromIRSS(): void
    {
        $image = $this->getMockImage();
        $image->expects($this->once())
              ->method('checkParams')
              ->with('image link', 'alt text');
        $image->expects($this->never())->method('withAction');
        $uri = $this->getMockURI('image link');

        $renderer = $this->getMockRenderer(
            $image,
            $this->getMockLink(),
            $this->getMockLegacy(),
            'image link'
        );
        $data = new class ($uri) extends NullCopyrightData {
            public function __construct(protected URI $uri)
            {
            }

            public function hasImage(): bool
            {
                return true;
            }

            public function imageFile(): string
            {
                return 'some string';
            }

            public function altText(): string
            {
                return 'alt text';
            }
        };

        $result = $renderer->toUIComponents($data);
        $this->assertSame(1, count($result));
        $this->assertInstanceOf(Image::class, $result[0]);
    }

    public function testToUIComponentsWithFallbackImage(): void
    {
        $image = $this->getMockImage();
        $image->expects($this->once())
              ->method('checkParams')
              ->with('fallback src');

        $renderer = $this->getMockRenderer(
            $image,
            $this->getMockLink(),
            $this->getMockLegacy(),
            ''
        );
        $data = new class () extends NullCopyrightData {
            public function fallBackToDefaultImage(): bool
            {
                return true;
            }
        };

        $result = $renderer->toUIComponents($data);
        $this->assertSame(1, count($result));
        $this->assertInstanceOf(Image::class, $result[0]);
    }
}
