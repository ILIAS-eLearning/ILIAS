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

namespace ILIAS\MetaData\XML\Writer\Standard;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\XML\Dictionary\NullDictionary;
use ILIAS\MetaData\XML\Copyright\NullCopyrightHandler;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Elements\NullSet;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\NullElement;
use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;
use ILIAS\MetaData\Structure\Definitions\NullDefinition;
use ILIAS\MetaData\Elements\Data\DataInterface;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Elements\Base\BaseElementInterface;
use ILIAS\MetaData\XML\Version;
use ILIAS\MetaData\XML\Dictionary\TagInterface;
use ILIAS\MetaData\XML\Dictionary\NullTag;
use ILIAS\MetaData\XML\SpecialCase;
use ILIAS\MetaData\Paths\NullFactory as NullPathFactory;
use ILIAS\MetaData\Paths\BuilderInterface;
use ILIAS\MetaData\Paths\NullBuilder;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\NullPath;
use ILIAS\MetaData\Manipulator\NullManipulator;
use ILIAS\MetaData\Elements\Data\NullData;

class StandardTest extends TestCase
{
    protected function getSet(array $set_as_array): SetInterface
    {
        $root = new class () extends NullElement {
            public array $element_as_array;

            public function getSubElements(): \Generator
            {
                foreach ($this->element_as_array['subs'] as $sub_array) {
                    $sub = clone $this;
                    $sub->element_as_array = $sub_array;
                    yield $sub;
                }
            }

            public function getDefinition(): DefinitionInterface
            {
                return new class ($this->element_as_array) extends NullDefinition {
                    public function __construct(protected array $element_as_array)
                    {
                    }

                    public function name(): string
                    {
                        return $this->element_as_array['name'];
                    }
                };
            }

            public function getData(): DataInterface
            {
                return new class ($this->element_as_array) extends NullData {
                    public function __construct(protected array $element_as_array)
                    {
                    }

                    public function type(): Type
                    {
                        return $this->element_as_array['type'];
                    }

                    public function value(): string
                    {
                        return $this->element_as_array['value'];
                    }
                };
            }
        };
        $root->element_as_array = $set_as_array;

        return new class ($root) extends NullSet {
            public function __construct(protected ElementInterface $root)
            {
            }

            public function getRoot(): ElementInterface
            {
                return $this->root;
            }
        };
    }

    protected function getStandardWriter(bool $cp_selection_active = false): Standard
    {
        $dictionary = new class () extends NullDictionary {
            /**
             * What version is selected is not part of unit tests.
             */
            public function tagForElement(
                BaseElementInterface $element,
                Version $version
            ): ?TagInterface {
                if (!isset($element->element_as_array['specials'])) {
                    return null;
                }

                return new class ($element->element_as_array['specials']) extends NullTag {
                    public function __construct(
                        protected array $specials
                    ) {
                    }

                    public function isExportedAsLangString(): bool
                    {
                        return in_array(SpecialCase::LANGSTRING, $this->specials);
                    }

                    public function isTranslatedAsCopyright(): bool
                    {
                        return in_array(SpecialCase::COPYRIGHT, $this->specials);
                    }

                    public function isOmitted(): bool
                    {
                        return in_array(SpecialCase::OMITTED, $this->specials);
                    }

                    public function isExportedAsAttribute(): bool
                    {
                        return in_array(SpecialCase::AS_ATTRIBUTE, $this->specials);
                    }
                };
            }
        };

        $copyright_handler = new class ($cp_selection_active) extends NullCopyrightHandler {
            public function __construct(protected bool $cp_selection_active)
            {
            }

            public function copyrightForExport(string $copyright): string
            {
                return '~parsed:' . $copyright . '~';
            }

            public function isCopyrightSelectionActive(): bool
            {
                return $this->cp_selection_active;
            }
        };

        $path_factory = new class () extends NullPathFactory {
            public function custom(): BuilderInterface
            {
                return new class () extends NullBuilder {
                    protected array $path = [];

                    public function withNextStep(string $name, bool $add_as_first = false): BuilderInterface
                    {
                        $clone = clone $this;
                        $clone->path[] = $name;
                        return $clone;
                    }

                    public function get(): PathInterface
                    {
                        $string = implode('>', $this->path);
                        return new class ($string) extends NullPath {
                            public function __construct(protected string $string)
                            {
                            }

                            public function toString(): string
                            {
                                return $this->string;
                            }
                        };
                    }
                };
            }
        };

        $manipulator = new class () extends NullManipulator {
            public function prepareCreateOrUpdate(
                SetInterface $set,
                PathInterface $path,
                string ...$values
            ): SetInterface {
                if (
                    $path->toString() !== 'rights>description>string' ||
                    count($values) !== 1
                ) {
                    throw new \ilMDXMLException(
                        'Unexpected preparation, path: "' . $path->toString() .
                        '", value count: "' . count($values) . '"'
                    );
                }

                $insert_array = [
                    'name' => 'copyright',
                    'type' => Type::NULL,
                    'value' => '',
                    'subs' => [[
                        'name' => 'string',
                        'type' => Type::STRING,
                        'value' => '',
                        'subs' => [],
                        'specials' => [SpecialCase::COPYRIGHT]
                    ]]
                ];

                $set->getRoot()->element_as_array['subs'][] = $insert_array;
                return $set;
            }
        };

        return new Standard($dictionary, $copyright_handler, $path_factory, $manipulator);
    }

    public function testWrite(): void
    {
        $set_array = [
            'name' => 'el1',
            'type' => Type::NULL,
            'value' => '',
            'subs' => [
                [
                    'name' => 'el1.1',
                    'type' => Type::STRING,
                    'value' => 'val1.1',
                    'subs' => []
                ],
                [
                    'name' => 'el1.2',
                    'type' => Type::NULL,
                    'value' => '',
                    'subs' => [
                        [
                            'name' => 'el1.2.1',
                            'type' => Type::NON_NEG_INT,
                            'value' => 'val1.2.1',
                            'subs' => []
                        ],
                        [
                            'name' => 'el1.2.2',
                            'type' => Type::DURATION,
                            'value' => 'val1.2.2',
                            'subs' => []
                        ]
                    ]
                ]
            ]
        ];

        $expected_xml = <<<XML
<?xml version="1.0"?>
<el1>
    <el1.1>val1.1</el1.1>
    <el1.2>
        <el1.2.1>val1.2.1</el1.2.1>
        <el1.2.2>val1.2.2</el1.2.2>
    </el1.2>
</el1>
XML;

        $writer = $this->getStandardWriter();
        $set = $this->getSet($set_array);
        $xml = $writer->write($set);

        $this->assertXmlStringEqualsXmlString($expected_xml, $xml->asXML());
    }

    public function testWriteWithLanguageNone(): void
    {
        $set_array = [
            'name' => 'el1',
            'type' => Type::NULL,
            'value' => '',
            'subs' => [
                [
                    'name' => 'el1.1',
                    'type' => Type::LANG,
                    'value' => 'xx',
                    'subs' => []
                ]
            ]
        ];

        $expected_xml = <<<XML
<?xml version="1.0"?>
<el1>
    <el1.1>none</el1.1>
</el1>
XML;

        $writer = $this->getStandardWriter();
        $set = $this->getSet($set_array);
        $xml = $writer->write($set);

        $this->assertXmlStringEqualsXmlString($expected_xml, $xml->asXML());
    }

    public function testWriteWithLangString(): void
    {
        $set_array = [
            'name' => 'el1',
            'type' => Type::NULL,
            'value' => '',
            'subs' => [
                [
                    'name' => 'el1.1',
                    'type' => Type::NULL,
                    'value' => '',
                    'specials' => [SpecialCase::LANGSTRING],
                    'subs' => [
                        [
                            'name' => 'string',
                            'type' => Type::STRING,
                            'value' => 'some text',
                            'subs' => []
                        ],
                        [
                            'name' => 'language',
                            'type' => Type::LANG,
                            'value' => 'br',
                            'subs' => []
                        ]
                    ]
                ]
            ]
        ];

        $expected_xml = <<<XML
<?xml version="1.0"?>
<el1>
    <el1.1>
        <string language="br">some text</string>
    </el1.1>
</el1>
XML;

        $writer = $this->getStandardWriter();
        $set = $this->getSet($set_array);
        $xml = $writer->write($set);

        $this->assertXmlStringEqualsXmlString($expected_xml, $xml->asXML());
    }

    public function testWriteWithLangStringNoLanguage(): void
    {
        $set_array = [
            'name' => 'el1',
            'type' => Type::NULL,
            'value' => '',
            'subs' => [
                [
                    'name' => 'el1.1',
                    'type' => Type::NULL,
                    'value' => '',
                    'specials' => [SpecialCase::LANGSTRING],
                    'subs' => [
                        [
                            'name' => 'string',
                            'type' => Type::STRING,
                            'value' => 'some text',
                            'subs' => []
                        ]
                    ]
                ]
            ]
        ];

        $expected_xml = <<<XML
<?xml version="1.0"?>
<el1>
    <el1.1>
        <string>some text</string>
    </el1.1>
</el1>
XML;

        $writer = $this->getStandardWriter();
        $set = $this->getSet($set_array);
        $xml = $writer->write($set);

        $this->assertXmlStringEqualsXmlString($expected_xml, $xml->asXML());
    }

    public function testWriteWithLangStringLanguageNone(): void
    {
        $set_array = [
            'name' => 'el1',
            'type' => Type::NULL,
            'value' => '',
            'subs' => [
                [
                    'name' => 'el1.1',
                    'type' => Type::NULL,
                    'value' => '',
                    'specials' => [SpecialCase::LANGSTRING],
                    'subs' => [
                        [
                            'name' => 'string',
                            'type' => Type::STRING,
                            'value' => 'some text',
                            'subs' => []
                        ],
                        [
                            'name' => 'language',
                            'type' => Type::LANG,
                            'value' => 'xx',
                            'subs' => []
                        ]
                    ]
                ]
            ]
        ];

        $expected_xml = <<<XML
<?xml version="1.0"?>
<el1>
    <el1.1>
        <string language="none">some text</string>
    </el1.1>
</el1>
XML;

        $writer = $this->getStandardWriter();
        $set = $this->getSet($set_array);
        $xml = $writer->write($set);

        $this->assertXmlStringEqualsXmlString($expected_xml, $xml->asXML());
    }

    public function testWriteWithLangStringNoString(): void
    {
        $set_array = [
            'name' => 'el1',
            'type' => Type::NULL,
            'value' => '',
            'subs' => [
                [
                    'name' => 'el1.1',
                    'type' => Type::NULL,
                    'value' => '',
                    'specials' => [SpecialCase::LANGSTRING],
                    'subs' => [
                        [
                            'name' => 'language',
                            'type' => Type::LANG,
                            'value' => 'br',
                            'subs' => []
                        ]
                    ]
                ]
            ]
        ];

        $expected_xml = <<<XML
<?xml version="1.0"?>
<el1>
    <el1.1>
        <string language="br"/>
    </el1.1>
</el1>
XML;

        $writer = $this->getStandardWriter();
        $set = $this->getSet($set_array);
        $xml = $writer->write($set);

        $this->assertXmlStringEqualsXmlString($expected_xml, $xml->asXML());
    }

    public function testWriteWithCopyright(): void
    {
        $set_array = [
            'name' => 'el1',
            'type' => Type::NULL,
            'value' => '',
            'subs' => [
                [
                    'name' => 'cp',
                    'type' => Type::STRING,
                    'value' => 'some license',
                    'specials' => [SpecialCase::COPYRIGHT],
                    'subs' => []
                ]
            ]
        ];

        $expected_xml = <<<XML
<?xml version="1.0"?>
<el1>
    <cp>~parsed:some license~</cp>
</el1>
XML;

        $writer = $this->getStandardWriter();
        $set = $this->getSet($set_array);
        $xml = $writer->write($set);

        $this->assertXmlStringEqualsXmlString($expected_xml, $xml->asXML());
    }

    public function testWriteCPSelectionActive(): void
    {
        $set_array = [
            'name' => 'el1',
            'type' => Type::NULL,
            'value' => '',
            'subs' => [
                [
                    'name' => 'el2',
                    'type' => Type::STRING,
                    'value' => 'some value',
                    'subs' => []
                ]
            ]
        ];

        $expected_xml = <<<XML
<?xml version="1.0"?>
<el1>
    <el2>some value</el2>
    <copyright><string>~parsed:~</string></copyright>
</el1>
XML;

        $writer = $this->getStandardWriter(true);
        $set = $this->getSet($set_array);
        $xml = $writer->write($set);

        $this->assertXmlStringEqualsXmlString($expected_xml, $xml->asXML());
    }

    public function testWriteWithOmittedDataCarryingElement(): void
    {
        $set_array = [
            'name' => 'el1',
            'type' => Type::NULL,
            'value' => '',
            'subs' => [
                [
                    'name' => 'el1.1',
                    'type' => Type::STRING,
                    'value' => 'val1.1',
                    'specials' => [SpecialCase::OMITTED],
                    'subs' => []
                ]
            ]
        ];

        $expected_xml = <<<XML
<?xml version="1.0"?>
<el1/>
XML;

        $writer = $this->getStandardWriter();
        $set = $this->getSet($set_array);
        $xml = $writer->write($set);

        $this->assertXmlStringEqualsXmlString($expected_xml, $xml->asXML());
    }

    public function testWriteWithOmittedContainerElement(): void
    {
        $set_array = [
            'name' => 'el1',
            'type' => Type::NULL,
            'value' => '',
            'subs' => [
                [
                    'name' => 'el1.1',
                    'type' => Type::NULL,
                    'value' => '',
                    'specials' => [SpecialCase::OMITTED],
                    'subs' => [
                        [
                            'name' => 'el1.1.1',
                            'type' => Type::STRING,
                            'value' => 'val1.1.1',
                            'subs' => []
                        ],
                        [
                            'name' => 'el1.1.2',
                            'type' => Type::STRING,
                            'value' => 'val1.1.2',
                            'subs' => []
                        ]
                    ]
                ]
            ]
        ];

        $expected_xml = <<<XML
<?xml version="1.0"?>
<el1/>
XML;

        $writer = $this->getStandardWriter();
        $set = $this->getSet($set_array);
        $xml = $writer->write($set);

        $this->assertXmlStringEqualsXmlString($expected_xml, $xml->asXML());
    }

    public function testWriteWithExportedAsAttribute(): void
    {
        $set_array = [
            'name' => 'el1',
            'type' => Type::NULL,
            'value' => '',
            'subs' => [
                [
                    'name' => 'el1.1',
                    'type' => Type::NULL,
                    'value' => '',
                    'subs' => [
                        [
                            'name' => 'el1.1.1',
                            'type' => Type::STRING,
                            'value' => 'val1.1.1',
                            'specials' => [SpecialCase::AS_ATTRIBUTE],
                            'subs' => []
                        ],
                        [
                            'name' => 'el1.1.2',
                            'type' => Type::STRING,
                            'value' => 'val1.1.2',
                            'subs' => []
                        ]
                    ]
                ]
            ]
        ];

        $expected_xml = <<<XML
<?xml version="1.0"?>
<el1>
    <el1.1 el1.1.1="val1.1.1">
        <el1.1.2>val1.1.2</el1.1.2>
    </el1.1>
</el1>
XML;

        $writer = $this->getStandardWriter();
        $set = $this->getSet($set_array);
        $xml = $writer->write($set);

        $this->assertXmlStringEqualsXmlString($expected_xml, $xml->asXML());
    }

    public function testWriteWithoutDataCarryingElement(): void
    {
        $set_array = [
            'name' => 'el1',
            'type' => Type::NULL,
            'value' => '',
            'subs' => [
                [
                    'name' => 'el1.1',
                    'type' => Type::NULL,
                    'value' => '',
                    'subs' => []
                ],
                [
                    'name' => 'el1.2',
                    'type' => Type::NULL,
                    'value' => '',
                    'subs' => []
                ]
            ]
        ];

        $expected_xml = <<<XML
<?xml version="1.0"?>
<el1>
    <el1.1/>
    <el1.2/>
</el1>
XML;

        $writer = $this->getStandardWriter();
        $set = $this->getSet($set_array);
        $xml = $writer->write($set);

        $this->assertXmlStringEqualsXmlString($expected_xml, $xml->asXML());
    }
}
