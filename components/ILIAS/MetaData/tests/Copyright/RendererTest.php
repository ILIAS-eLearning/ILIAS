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
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Link\Link;
use ILIAS\UI\Component\Legacy\Content;
use ILIAS\UI\Implementation\Component\Symbol\Icon\Icon as IIcon;
use ILIAS\UI\Implementation\Component\Link\Link as ILink;
use ILIAS\UI\Implementation\Component\Legacy\Content as ILegacy;
use ILIAS\Filesystem\Filesystem as WebFiles;
use ILIAS\UI\Factory;
use ILIAS\ResourceStorage\Services as IRSS;
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\Data\URI;
use ILIAS\UI\Component\Link\Relationship;

class RendererTest extends TestCase
{
    protected function getMockRenderer(
        Icon $icon,
        Link $link,
        Content $legacy,
        string $src_from_irss
    ): Renderer {
        return new class ($icon, $link, $legacy, $src_from_irss) extends Renderer {
            protected ?string $icon_src = null;
            protected ?string $icon_alt = null;
            protected ?string $link_label = null;
            protected ?string $link_action = null;
            protected ?Relationship $link_relationship = null;
            protected ?bool $link_disabled = null;
            protected ?string $legacy_text = null;

            public function __construct(
                protected Icon $icon,
                protected Link $link,
                protected Content $legacy,
                protected string $src_from_irss
            ) {
            }

            protected function getFallBackSrc(): string
            {
                return 'fallback src';
            }

            protected function customIcon(string $src, string $alt): Icon
            {
                $this->icon_src = $src;
                $this->icon_alt = $alt;
                return $this->icon;
            }

            protected function standardLink(
                string $label,
                string $action,
                ?Relationship $relationship,
                bool $disabled
            ): Link {
                $this->link_label = $label;
                $this->link_action = $action;
                $this->link_relationship = $relationship;
                $this->link_disabled = $disabled;
                return $this->link;
            }

            protected function textInLegacy(string $text): Content
            {
                $this->legacy_text = $text;
                return $this->legacy;
            }

            protected function getSourceFromIRSS(string $string_id): string
            {
                return $this->src_from_irss;
            }

            public function exposeImageData(): array
            {
                return [
                    'icon_src' => $this->icon_src,
                    'icon_alt' => $this->icon_alt
                ];
            }

            public function exposeLinkData(): array
            {
                return [
                    'link_label' => $this->link_label,
                    'link_action' => $this->link_action,
                    'link_relationship' => $this->link_relationship,
                    'link_disabled' => $this->link_disabled
                ];
            }

            public function exposeLegacyData(): array
            {
                return [
                    'legacy_text' => $this->legacy_text
                ];
            }
        };
    }

    protected function getMockIcon(): MockObject|Icon
    {
        return $this->getMockBuilder(IIcon::class)
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    protected function getMockLink(): MockObject|Link
    {
        return $this->getMockBuilder(ILink::class)
                    ->disableOriginalConstructor()
                    ->onlyMethods(['withAdditionalRelationshipToReferencedResource'])
                    ->getMock();
    }

    protected function getMockLegacy(): MockObject|Content
    {
        return $this->getMockBuilder(ILegacy::class)
                    ->disableOriginalConstructor()
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
        $link = $this->getMockLink();
        $uri = $this->getMockURI('link');
        $img_uri = $this->getMockURI('image link');

        $renderer = $this->getMockRenderer(
            $this->getMockIcon(),
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
        $this->assertInstanceOf(Icon::class, $result[0]);
        $this->assertInstanceOf(Link::class, $result[1]);
        $this->assertSame(
            [
                'icon_src' => 'image link',
                'icon_alt' => 'alt text'
            ],
            $renderer->exposeImageData()
        );
        $this->assertSame(
            [
                'link_label' => 'full name',
                'link_action' => 'link',
                'link_relationship' => Relationship::LICENSE,
                'link_disabled' => false
            ],
            $renderer->exposeLinkData()
        );
    }

    public function testToUIComponentsEmpty(): void
    {
        $legacy = $this->getMockLegacy();

        $renderer = $this->getMockRenderer(
            $this->getMockIcon(),
            $this->getMockLink(),
            $legacy,
            ''
        );

        $result = $renderer->toUIComponents(new NullCopyrightData());
        $this->assertSame(0, count($result));
    }

    public function testToUIComponentsWithoutLink(): void
    {
        $uri = $this->getMockURI('image link');

        $renderer = $this->getMockRenderer(
            $this->getMockIcon(),
            $this->getMockLink(),
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
        $this->assertInstanceOf(Icon::class, $result[0]);
        $this->assertInstanceOf(Content::class, $result[1]);
        $this->assertSame(
            [
                'icon_src' => 'image link',
                'icon_alt' => 'alt text'
            ],
            $renderer->exposeImageData()
        );
        $this->assertSame(
            ['legacy_text' => 'full name'],
            $renderer->exposeLegacyData()
        );
    }

    public function testToUIComponentsWithLinkNoImage(): void
    {
        $link = $this->getMockLink();
        $uri = $this->getMockURI('link');

        $renderer = $this->getMockRenderer(
            $this->getMockIcon(),
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
        $this->assertSame(
            [
                'link_label' => 'full name',
                'link_action' => 'link',
                'link_relationship' => Relationship::LICENSE,
                'link_disabled' => false
            ],
            $renderer->exposeLinkData()
        );
    }

    public function testToUIComponentsLinkWithoutFullName(): void
    {
        $link = $this->getMockLink();
        $uri = $this->getMockURI('link');

        $renderer = $this->getMockRenderer(
            $this->getMockIcon(),
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
        $this->assertSame(
            [
                'link_label' => 'link',
                'link_action' => 'link',
                'link_relationship' => Relationship::LICENSE,
                'link_disabled' => false
            ],
            $renderer->exposeLinkData()
        );
    }

    public function testToUIComponentsWithImageFromLink(): void
    {
        $uri = $this->getMockURI('image link');

        $renderer = $this->getMockRenderer(
            $this->getMockIcon(),
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
        $this->assertInstanceOf(Icon::class, $result[0]);
        $this->assertSame(
            [
                'icon_src' => 'image link',
                'icon_alt' => 'alt text'
            ],
            $renderer->exposeImageData()
        );
    }

    public function testToUIComponentsWithImageFromIRSS(): void
    {
        $uri = $this->getMockURI('image link');

        $renderer = $this->getMockRenderer(
            $this->getMockIcon(),
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
        $this->assertInstanceOf(Icon::class, $result[0]);
        $this->assertSame(
            [
                'icon_src' => 'image link',
                'icon_alt' => 'alt text'
            ],
            $renderer->exposeImageData()
        );
    }

    public function testToUIComponentsWithFallbackImage(): void
    {
        $renderer = $this->getMockRenderer(
            $this->getMockIcon(),
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
        $this->assertInstanceOf(Icon::class, $result[0]);
        $this->assertSame(
            [
                'icon_src' => 'fallback src',
                'icon_alt' => ''
            ],
            $renderer->exposeImageData()
        );
    }

    public function testToImageOnlyWithImageFromLink(): void
    {
        $uri = $this->getMockURI('image link');

        $renderer = $this->getMockRenderer(
            $this->getMockIcon(),
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

        $result = $renderer->toImageOnly($data);
        $this->assertNotNull($result);
        $this->assertSame(
            [
                'icon_src' => 'image link',
                'icon_alt' => 'alt text'
            ],
            $renderer->exposeImageData()
        );
    }

    public function testToImageOnlyWithImageFromIRSS(): void
    {
        $uri = $this->getMockURI('image link');

        $renderer = $this->getMockRenderer(
            $this->getMockIcon(),
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

        $result = $renderer->toImageOnly($data);
        $this->assertNotNull($result);
        $this->assertSame(
            [
                'icon_src' => 'image link',
                'icon_alt' => 'alt text'
            ],
            $renderer->exposeImageData()
        );
    }

    public function testToImageOnlyWithFallbackImage(): void
    {
        $renderer = $this->getMockRenderer(
            $this->getMockIcon(),
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

        $result = $renderer->toImageOnly($data);
        $this->assertNotNull($result);
        $this->assertSame(
            [
                'icon_src' => 'fallback src',
                'icon_alt' => ''
            ],
            $renderer->exposeImageData()
        );
    }

    public function testToImageOnlyWithoutImage(): void
    {
        $renderer = $this->getMockRenderer(
            $this->getMockIcon(),
            $this->getMockLink(),
            $this->getMockLegacy(),
            ''
        );
        $data = new NullCopyrightData();

        $result = $renderer->toImageOnly($data);
        $this->assertNull($result);
    }

    public function testToLinkOnly(): void
    {
        $link = $this->getMockLink();
        $uri = $this->getMockURI('link');

        $renderer = $this->getMockRenderer(
            $this->getMockIcon(),
            $link,
            $this->getMockLegacy(),
            ''
        );
        $data = new class ($uri) extends NullCopyrightData {
            public function __construct(
                protected URI $uri
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
        };

        $result = $renderer->toLinkOnly($data);
        $this->assertNotNull($result);
        $this->assertSame(
            [
                'link_label' => 'full name',
                'link_action' => 'link',
                'link_relationship' => Relationship::LICENSE,
                'link_disabled' => false
            ],
            $renderer->exposeLinkData()
        );
    }

    public function testToLinkOnlyWithoutLinkOrFullName(): void
    {
        $renderer = $this->getMockRenderer(
            $this->getMockIcon(),
            $this->getMockLink(),
            $this->getMockLegacy(),
            ''
        );

        $result = $renderer->toLinkOnly(new NullCopyrightData());
        $this->assertNull($result);
    }

    public function testToLinkOnlyWithoutLink(): void
    {
        $link = $this->getMockLink();
        $renderer = $this->getMockRenderer(
            $this->getMockIcon(),
            $link,
            $this->getMockLegacy(),
            ''
        );
        $data = new class () extends NullCopyrightData {
            public function fullName(): string
            {
                return 'full name';
            }
        };

        $result = $renderer->toLinkOnly($data);
        $this->assertNotNull($result);
        $this->assertSame(
            [
                'link_label' => 'full name',
                'link_action' => '',
                'link_relationship' => null,
                'link_disabled' => true
            ],
            $renderer->exposeLinkData()
        );
    }

    public function testToLinkOnlyWithoutFullName(): void
    {
        $link = $this->getMockLink();
        $uri = $this->getMockURI('link');

        $renderer = $this->getMockRenderer(
            $this->getMockIcon(),
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

        $result = $renderer->toLinkOnly($data);
        $this->assertNotNull($result);
        $this->assertSame(
            [
                'link_label' => 'link',
                'link_action' => 'link',
                'link_relationship' => Relationship::LICENSE,
                'link_disabled' => false
            ],
            $renderer->exposeLinkData()
        );
    }

    public function testCopyrightAsStringHasFullName(): void
    {
        $renderer = $this->getMockRenderer(
            $this->getMockIcon(),
            $this->getMockLink(),
            $this->getMockLegacy(),
            ''
        );
        $data = new class () extends NullCopyrightData {
            public function fullName(): string
            {
                return 'full name of copyright';
            }

            public function link(): ?URI
            {
                return null;
            }
        };

        $this->assertSame(
            'full name of copyright',
            $renderer->toString($data)
        );
    }

    public function testCopyrightAsStringHasLink(): void
    {
        $renderer = $this->getMockRenderer(
            $this->getMockIcon(),
            $this->getMockLink(),
            $this->getMockLegacy(),
            ''
        );
        $uri = $this->getMockURI('http://www.example2.com');
        $data = new class ($uri) extends NullCopyrightData {
            public function __construct(protected URI $uri)
            {
            }

            public function fullName(): string
            {
                return '';
            }

            public function link(): ?URI
            {
                return $this->uri;
            }
        };

        $this->assertSame(
            'http://www.example2.com',
            $renderer->toString($data)
        );
    }

    public function testCopyrightAsStringHasFullNameAndLink(): void
    {
        $renderer = $this->getMockRenderer(
            $this->getMockIcon(),
            $this->getMockLink(),
            $this->getMockLegacy(),
            ''
        );
        $uri = $this->getMockURI('http://www.example2.com');
        $data = new class ($uri) extends NullCopyrightData {
            public function __construct(protected URI $uri)
            {
            }

            public function fullName(): string
            {
                return 'full name of copyright';
            }

            public function link(): ?URI
            {
                return $this->uri;
            }
        };

        $this->assertSame(
            'full name of copyright http://www.example2.com',
            $renderer->toString($data)
        );
    }

    public function testCopyrightAsStringHasNoFullNameOrLink(): void
    {
        $renderer = $this->getMockRenderer(
            $this->getMockIcon(),
            $this->getMockLink(),
            $this->getMockLegacy(),
            ''
        );
        $data = new class () extends NullCopyrightData {
            public function fullName(): string
            {
                return '';
            }

            public function link(): ?URI
            {
                return null;
            }
        };

        $this->assertSame(
            '',
            $renderer->toString($data)
        );
    }
}
