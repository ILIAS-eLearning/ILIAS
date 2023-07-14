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

namespace ILIAS\Component\Tests\Dependencies;

use PHPUnit\Framework\TestCase;
use ILIAS\Component\Component;
use ILIAS\Component\Dependencies as D;

interface TestInterface
{
}

interface TestInterface2
{
}

class ImplementsTestInterface implements TestInterface
{
}

class Implements2TestInterface implements TestInterface
{
}


class ReaderTest extends TestCase
{
    protected D\Reader $reader;

    public function setUp(): void
    {
        $this->reader = new D\Reader();
    }

    public function testNullComponent(): void
    {
        $component = new class () implements Component {
            public function init(
                array | \ArrayAccess &$define,
                array | \ArrayAccess &$implement,
                array | \ArrayAccess &$use,
                array | \ArrayAccess &$contribute,
                array | \ArrayAccess &$seek,
                array | \ArrayAccess &$provide,
                array | \ArrayAccess &$pull,
                array | \ArrayAccess &$internal,
            ): void {
            }
        };

        $result = $this->reader->read($component);

        $this->assertEquals(new D\OfComponent($component), $result);
    }

    public function testSimpleDefine(): void
    {
        $component = new class () implements Component {
            public function init(
                array | \ArrayAccess &$define,
                array | \ArrayAccess &$implement,
                array | \ArrayAccess &$use,
                array | \ArrayAccess &$contribute,
                array | \ArrayAccess &$seek,
                array | \ArrayAccess &$provide,
                array | \ArrayAccess &$pull,
                array | \ArrayAccess &$internal,
            ): void {
                $define[TestInterface::class] = null;
            }
        };
        $result = $this->reader->read($component);

        $name = new D\Name(TestInterface::class);
        $define = new D\Define($name, false);

        $this->assertEquals(new D\OfComponent($component, $define), $result);
        $this->assertFalse($result[(string) $define][0]->hasMinimalImplementation());
    }

    public function testDefineWithMinimalImplementation(): void
    {
        $component = new class () implements Component {
            public function init(
                array | \ArrayAccess &$define,
                array | \ArrayAccess &$implement,
                array | \ArrayAccess &$use,
                array | \ArrayAccess &$contribute,
                array | \ArrayAccess &$seek,
                array | \ArrayAccess &$provide,
                array | \ArrayAccess &$pull,
                array | \ArrayAccess &$internal,
            ): void {
                $define[TestInterface::class] = fn() => new class () implements TestInterface {};
            }
        };
        $result = $this->reader->read($component);

        $name = new D\Name(TestInterface::class);
        $define = new D\Define($name, true);

        $this->assertEquals(new D\OfComponent($component, $define), $result);
        $this->assertTrue($result[(string) $define][0]->hasMinimalImplementation());
    }

    public function testDefineWithNonMinimalImplementation(): void
    {
        $this->expectException(\LogicException::class);

        $result = $this->reader->read(new class () implements Component {
            public function init(
                array | \ArrayAccess &$define,
                array | \ArrayAccess &$implement,
                array | \ArrayAccess &$use,
                array | \ArrayAccess &$contribute,
                array | \ArrayAccess &$seek,
                array | \ArrayAccess &$provide,
                array | \ArrayAccess &$pull,
                array | \ArrayAccess &$internal,
            ): void {
                $define[TestInterface::class] = fn() => new class ($use[ILIAS\Component\Service::class]) implements TestInterface {};
            }
        });
    }

    public function testImplementWithoutImplementation(): void
    {
        $this->expectException(\LogicException::class);

        $result = $this->reader->read(new class () implements Component {
            public function init(
                array | \ArrayAccess &$define,
                array | \ArrayAccess &$implement,
                array | \ArrayAccess &$use,
                array | \ArrayAccess &$contribute,
                array | \ArrayAccess &$seek,
                array | \ArrayAccess &$provide,
                array | \ArrayAccess &$pull,
                array | \ArrayAccess &$internal,
            ): void {
                $implement[TestInterface::class] = null;
            }
        });
    }

    public function testImplementWithSimpleImplementation(): void
    {
        $component = new class () implements Component {
            public function init(
                array | \ArrayAccess &$define,
                array | \ArrayAccess &$implement,
                array | \ArrayAccess &$use,
                array | \ArrayAccess &$contribute,
                array | \ArrayAccess &$seek,
                array | \ArrayAccess &$provide,
                array | \ArrayAccess &$pull,
                array | \ArrayAccess &$internal,
            ): void {
                $implement[TestInterface::class] = fn() => new ImplementsTestInterface();
            }
        };
        $result = $this->reader->read($component);

        $name = TestInterface::class;
        $implement = new D\Out(D\OutType::IMPLEMENT, $name, ["class" => ImplementsTestInterface::class, "position" => 0], []);

        $this->assertEquals(new D\OfComponent($component, $implement), $result);
    }

    public function testImplementWithWrongImplementation(): void
    {
        $this->expectException(\LogicException::class);

        $result = $this->reader->read(new class () implements Component {
            public function init(
                array | \ArrayAccess &$define,
                array | \ArrayAccess &$implement,
                array | \ArrayAccess &$use,
                array | \ArrayAccess &$contribute,
                array | \ArrayAccess &$seek,
                array | \ArrayAccess &$provide,
                array | \ArrayAccess &$pull,
                array | \ArrayAccess &$internal,
            ): void {
                $implement[TestInterface::class] = fn() => new class () {};
            }
        });
    }

    public function testImplementWithElaborateImplementation(): void
    {
        $component = new class () implements Component {
            public function init(
                array | \ArrayAccess &$define,
                array | \ArrayAccess &$implement,
                array | \ArrayAccess &$use,
                array | \ArrayAccess &$contribute,
                array | \ArrayAccess &$seek,
                array | \ArrayAccess &$provide,
                array | \ArrayAccess &$pull,
                array | \ArrayAccess &$internal,
            ): void {
                $implement[TestInterface::class] = fn() => new ImplementsTestInterface($use[TestInterface::class], $seek[TestInterface::class], $pull[TestInterface::class], $internal["something"]);

                $internal["something"] = fn() => new Implements2TestInterface();
            }
        };

        $result = $this->reader->read($component);

        $name = TestInterface::class;
        $use = new D\In(D\InType::USE, $name);
        $seek = new D\In(D\InType::SEEK, $name);
        $pull = new D\In(D\InType::PULL, $name);
        $internal_in = new D\In(D\InType::INTERNAL, "something");
        $internal_out = new D\Out(D\OutType::INTERNAL, "something", null, []);
        $implement = new D\Out(D\OutType::IMPLEMENT, $name, ["class" => ImplementsTestInterface::class, "position" => 0], [$use, $seek, $pull, $internal_in]);

        $expected = new D\OfComponent($component, $use, $seek, $pull, $internal_in, $internal_out, $implement);

        $this->assertEquals($expected, $result);

        $this->assertEquals([$implement], $result[(string) $implement]);
        $this->assertEquals([$use], $result[(string) $use]);
        $this->assertEquals([$seek], $result[(string) $seek]);
        $this->assertEquals([$pull], $result[(string) $pull]);
        $this->assertEquals([$internal_in], $result[(string) $internal_in]);
        $this->assertEquals([$internal_out], $result[(string) $internal_out]);
    }

    public function testImplementWithTwoImplementations(): void
    {
        $component = new class () implements Component {
            public function init(
                array | \ArrayAccess &$define,
                array | \ArrayAccess &$implement,
                array | \ArrayAccess &$use,
                array | \ArrayAccess &$contribute,
                array | \ArrayAccess &$seek,
                array | \ArrayAccess &$provide,
                array | \ArrayAccess &$pull,
                array | \ArrayAccess &$internal,
            ): void {
                $implement[TestInterface::class] = fn() => new ImplementsTestInterface();
                $implement[TestInterface::class] = fn() => new Implements2TestInterface();
            }
        };
        $result = $this->reader->read($component);

        $name = TestInterface::class;
        $implement = new D\Out(D\OutType::IMPLEMENT, $name, ["class" => ImplementsTestInterface::class, "position" => 0], []);
        $implement2 = new D\Out(D\OutType::IMPLEMENT, $name, ["class" => Implements2TestInterface::class, "position" => 1], []);

        $this->assertEquals(new D\OfComponent($component, $implement, $implement2), $result);
        $this->assertCount(2, $result[(string) $implement]);
    }

    public function testUseWithoutContext(): void
    {
        $this->expectException(\LogicException::class);

        $result = $this->reader->read(new class () implements Component {
            public function init(
                array | \ArrayAccess &$define,
                array | \ArrayAccess &$implement,
                array | \ArrayAccess &$use,
                array | \ArrayAccess &$contribute,
                array | \ArrayAccess &$seek,
                array | \ArrayAccess &$provide,
                array | \ArrayAccess &$pull,
                array | \ArrayAccess &$internal,
            ): void {
                $foo = $use[TestInterface::class];
            }
        });
    }

    public function testContributeWithoutImplementation(): void
    {
        $this->expectException(\LogicException::class);

        $result = $this->reader->read(new class () implements Component {
            public function init(
                array | \ArrayAccess &$define,
                array | \ArrayAccess &$implement,
                array | \ArrayAccess &$use,
                array | \ArrayAccess &$contribute,
                array | \ArrayAccess &$seek,
                array | \ArrayAccess &$provide,
                array | \ArrayAccess &$pull,
                array | \ArrayAccess &$internal,
            ): void {
                $contribute[TestInterface::class] = null;
            }
        });
    }

    public function testContributeWithSimpleImplementation(): void
    {
        $component = new class () implements Component {
            public function init(
                array | \ArrayAccess &$define,
                array | \ArrayAccess &$implement,
                array | \ArrayAccess &$use,
                array | \ArrayAccess &$contribute,
                array | \ArrayAccess &$seek,
                array | \ArrayAccess &$provide,
                array | \ArrayAccess &$pull,
                array | \ArrayAccess &$internal,
            ): void {
                $contribute[TestInterface::class] = fn() => new ImplementsTestInterface();
            }
        };
        $result = $this->reader->read($component);

        $name = TestInterface::class;
        $contribute = new D\Out(D\OutType::CONTRIBUTE, $name, ["position" => 0], []);

        $this->assertEquals(new D\OfComponent($component, $contribute), $result);
    }

    public function testContributeWithWrongImplementation(): void
    {
        $this->expectException(\LogicException::class);

        $result = $this->reader->read(new class () implements Component {
            public function init(
                array | \ArrayAccess &$define,
                array | \ArrayAccess &$implement,
                array | \ArrayAccess &$use,
                array | \ArrayAccess &$contribute,
                array | \ArrayAccess &$seek,
                array | \ArrayAccess &$provide,
                array | \ArrayAccess &$pull,
                array | \ArrayAccess &$internal,
            ): void {
                $contribute[TestInterface::class] = fn() => new class () {};
            }
        });
    }

    public function testContributeWithElaborateImplementation(): void
    {
        $component = new class () implements Component {
            public function init(
                array | \ArrayAccess &$define,
                array | \ArrayAccess &$implement,
                array | \ArrayAccess &$use,
                array | \ArrayAccess &$contribute,
                array | \ArrayAccess &$seek,
                array | \ArrayAccess &$provide,
                array | \ArrayAccess &$pull,
                array | \ArrayAccess &$internal,
            ): void {
                $contribute[TestInterface::class] = fn() => new ImplementsTestInterface($use[TestInterface::class], $seek[TestInterface::class], $pull[TestInterface::class], $internal["something"]);
                $internal["something"] = fn() => new Implements2TestInterface();
            }
        };
        $result = $this->reader->read($component);

        $name = TestInterface::class;
        $use = new D\In(D\InType::USE, $name);
        $seek = new D\In(D\InType::SEEK, $name);
        $pull = new D\In(D\InType::PULL, $name);
        $internal_in = new D\In(D\InType::INTERNAL, "something");
        $internal_out = new D\Out(D\OutType::INTERNAL, "something", null, []);
        $contribute = new D\Out(D\OutType::CONTRIBUTE, $name, ["position" => 0], [$use, $seek, $pull, $internal_in]);

        $expected = new D\OfComponent($component, $use, $seek, $pull, $internal_in, $internal_out, $contribute);

        $this->assertEquals([$contribute], $result[(string) $contribute]);
        $this->assertEquals([$use], $result[(string) $use]);
        $this->assertEquals([$seek], $result[(string) $seek]);
        $this->assertEquals([$pull], $result[(string) $pull]);
        $this->assertEquals([$internal_in], $result[(string) $internal_in]);
        $this->assertEquals([$internal_out], $result[(string) $internal_out]);
    }

    public function testSeekWithoutContext(): void
    {
        $this->expectException(\LogicException::class);

        $result = $this->reader->read(new class () implements Component {
            public function init(
                array | \ArrayAccess &$define,
                array | \ArrayAccess &$implement,
                array | \ArrayAccess &$use,
                array | \ArrayAccess &$contribute,
                array | \ArrayAccess &$seek,
                array | \ArrayAccess &$provide,
                array | \ArrayAccess &$pull,
                array | \ArrayAccess &$internal,
            ): void {
                $foo = $seek[TestInterface::class];
            }
        });
    }

    public function testProvideWithoutImplementation(): void
    {
        $this->expectException(\LogicException::class);

        $result = $this->reader->read(new class () implements Component {
            public function init(
                array | \ArrayAccess &$define,
                array | \ArrayAccess &$implement,
                array | \ArrayAccess &$use,
                array | \ArrayAccess &$contribute,
                array | \ArrayAccess &$seek,
                array | \ArrayAccess &$provide,
                array | \ArrayAccess &$pull,
                array | \ArrayAccess &$internal,
            ): void {
                $provide[TestInterface::class] = null;
            }
        });
    }

    public function testProvideWithSimpleImplementation(): void
    {
        $component = new class () implements Component {
            public function init(
                array | \ArrayAccess &$define,
                array | \ArrayAccess &$implement,
                array | \ArrayAccess &$use,
                array | \ArrayAccess &$contribute,
                array | \ArrayAccess &$seek,
                array | \ArrayAccess &$provide,
                array | \ArrayAccess &$pull,
                array | \ArrayAccess &$internal,
            ): void {
                $provide[TestInterface::class] = fn() => new ImplementsTestInterface();
            }
        };
        $result = $this->reader->read($component);

        $name = TestInterface::class;
        $provide = new D\Out(D\OutType::PROVIDE, $name, null, []);

        $this->assertEquals(new D\OfComponent($component, $provide), $result);
    }

    public function testProvideWithWrongImplementation(): void
    {
        $this->expectException(\LogicException::class);

        $result = $this->reader->read(new class () implements Component {
            public function init(
                array | \ArrayAccess &$define,
                array | \ArrayAccess &$implement,
                array | \ArrayAccess &$use,
                array | \ArrayAccess &$contribute,
                array | \ArrayAccess &$seek,
                array | \ArrayAccess &$provide,
                array | \ArrayAccess &$pull,
                array | \ArrayAccess &$internal,
            ): void {
                $provide[TestInterface::class] = fn() => new class () {};
            }
        });
    }

    public function testProvideWithElaborateImplementation(): void
    {
        $component = new class () implements Component {
            public function init(
                array | \ArrayAccess &$define,
                array | \ArrayAccess &$implement,
                array | \ArrayAccess &$use,
                array | \ArrayAccess &$contribute,
                array | \ArrayAccess &$seek,
                array | \ArrayAccess &$provide,
                array | \ArrayAccess &$pull,
                array | \ArrayAccess &$internal,
            ): void {
                $provide[TestInterface::class] = fn() => new ImplementsTestInterface($use[TestInterface::class], $seek[TestInterface::class], $pull[TestInterface::class], $internal["something"]);

                $internal["something"] = fn() => new Implements2TestInterface();
            }
        };
        $result = $this->reader->read($component);

        $name = TestInterface::class;
        $use = new D\In(D\InType::USE, $name);
        $seek = new D\In(D\InType::SEEK, $name);
        $pull = new D\In(D\InType::PULL, $name);
        $internal_in = new D\In(D\InType::INTERNAL, "something");
        $internal_out = new D\Out(D\OutType::INTERNAL, "something", null, []);
        $provide = new D\Out(D\OutType::PROVIDE, $name, null, [$use, $seek, $pull, $internal_in]);

        $expected = new D\OfComponent($component, $use, $seek, $pull, $internal_in, $internal_out, $provide);

        $this->assertEquals($expected, $result);

        $this->assertEquals([$provide], $result[(string) $provide]);
        $this->assertEquals([$use], $result[(string) $use]);
        $this->assertEquals([$seek], $result[(string) $seek]);
        $this->assertEquals([$pull], $result[(string) $pull]);
        $this->assertEquals([$internal_in], $result[(string) $internal_in]);
        $this->assertEquals([$internal_out], $result[(string) $internal_out]);
    }

    public function testPullWithoutContext(): void
    {
        $this->expectException(\LogicException::class);

        $result = $this->reader->read(new class () implements Component {
            public function init(
                array | \ArrayAccess &$define,
                array | \ArrayAccess &$implement,
                array | \ArrayAccess &$use,
                array | \ArrayAccess &$contribute,
                array | \ArrayAccess &$seek,
                array | \ArrayAccess &$provide,
                array | \ArrayAccess &$pull,
                array | \ArrayAccess &$internal,
            ): void {
                $foo = $pull[TestInterface::class];
            }
        });
    }

    public function testSimpleInternal(): void
    {
        $this->expectException(\LogicException::class);

        $raw_name = "some_name";

        $result = $this->reader->read(new class ($raw_name) implements Component {
            public function __construct(
                protected string $raw_name
            ) {
            }

            public function init(
                array | \ArrayAccess &$define,
                array | \ArrayAccess &$implement,
                array | \ArrayAccess &$use,
                array | \ArrayAccess &$contribute,
                array | \ArrayAccess &$seek,
                array | \ArrayAccess &$provide,
                array | \ArrayAccess &$pull,
                array | \ArrayAccess &$internal,
            ): void {
                $internal[$this->raw_name] = fn() => $internal[$this->raw_name];
            }
        });
    }

    public function testReaderProvidesMocks(): void
    {
        $raw_name = "some_name";
        $results = [];

        $result = $this->reader->read(new class ($raw_name, $results) implements Component {
            public function __construct(
                protected string $raw_name,
                protected array &$results
            ) {
            }

            public function init(
                array | \ArrayAccess &$define,
                array | \ArrayAccess &$implement,
                array | \ArrayAccess &$use,
                array | \ArrayAccess &$contribute,
                array | \ArrayAccess &$seek,
                array | \ArrayAccess &$provide,
                array | \ArrayAccess &$pull,
                array | \ArrayAccess &$internal,
            ): void {
                $provide[TestInterface::class] = fn() => new class (
                    $this->results,
                    $use[TestInterface::class],
                    $seek[TestInterface::class],
                    $pull[TestInterface::class]
                ) implements TestInterface {
                    public function __construct(
                        array &$results,
                        $a,
                        $b,
                        $c
                    ) {
                        $results[] = $a;
                        $results[] = $b;
                        $results[] = $c;
                    }
                };
            }
        });

        $this->assertInstanceOf(TestInterface::class, $results[0]);
        $this->assertEquals([], $results[1]);
        $this->assertInstanceOf(TestInterface::class, $results[2]);
    }

    public function testReaderResolvesInternal(): void
    {
        $raw_name = "some_name";
        $results = [];

        $result = $this->reader->read(new class ($raw_name, $results) implements Component {
            public function __construct(
                protected string $raw_name,
                protected array &$results
            ) {
            }

            public function init(
                array | \ArrayAccess &$define,
                array | \ArrayAccess &$implement,
                array | \ArrayAccess &$use,
                array | \ArrayAccess &$contribute,
                array | \ArrayAccess &$seek,
                array | \ArrayAccess &$provide,
                array | \ArrayAccess &$pull,
                array | \ArrayAccess &$internal,
            ): void {
                $provide[TestInterface::class] = fn() => new class (
                    $this->results,
                    $internal["foo"],
                    $internal["bar"],
                    $internal["baz"]
                ) implements TestInterface {
                    public function __construct(
                        array &$results,
                        $a,
                        $b,
                        $c
                    ) {
                        $results[] = $a;
                        $results[] = $b;
                        $results[] = $c;
                    }
                };

                $internal["foo"] = fn() => $use[TestInterface::class];
                $internal["bar"] = fn() => $seek[TestInterface::class];
                $internal["baz"] = fn() => $pull[TestInterface2::class];
            }
        });

        $this->assertInstanceOf(TestInterface::class, $results[0]);
        $this->assertEquals([], $results[1]);
        $this->assertInstanceOf(TestInterface2::class, $results[2]);
    }
}
