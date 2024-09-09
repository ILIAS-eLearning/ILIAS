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

namespace ILIAS\Test\Tests\Toolbars;

use ilAccess;
use ilCtrl;
use ilDBInterface;
use ilDBStatement;
use ilGlobalTemplateInterface;
use ilLanguage;
use ilObjTest;
use ilObjTestGUI;
use ilRepositoryGUI;
use ilTestBaseTestCase;
use ilTestInfoScreenToolbarGUI;
use ilTestPlayerAbstractGUI;
use ilTestQuestionSetConfig;
use ilTestSession;
use ilToolbarGUI;
use ilToolbarItem;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;
use stdClass;

/**
 * Class ilTestInfoScreenToolbarGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestInfoScreenToolbarGUITest extends ilTestBaseTestCase
{
    /**
     * @throws ReflectionException|Exception
     */
    public function testConstruct(): void
    {
        $il_test_info_screen_toolbar_gui = $this->createInstanceOf(ilTestInfoScreenToolbarGUI::class);
        $this->assertInstanceOf(ilTestInfoScreenToolbarGUI::class, $il_test_info_screen_toolbar_gui);
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testGetTestQuestionSetConfig(): void
    {
        $test_question_set_config = $this->createMock(ilTestQuestionSetConfig::class);

        $il_test_info_screen_toolbar_gui = $this->createInstanceOf(
            ilTestInfoScreenToolbarGUI::class,
            ['tst_question_set_config' => $test_question_set_config]
        );

        $this->assertEquals(
            $test_question_set_config,
            self::callMethod(
                $il_test_info_screen_toolbar_gui,
                'getTestQuestionSetConfig'
            )
        );
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testGetTestOBJ(): void
    {
        $test_obj = $this->createMock(ilObjTest::class);
        $il_test_info_screen_toolbar_gui = $this->createInstanceOf(
            ilTestInfoScreenToolbarGUI::class,
            ['test_obj' => $test_obj]
        );

        $this->assertEquals(
            $test_obj,
            self::callMethod(
                $il_test_info_screen_toolbar_gui,
                'getTestOBJ'
            )
        );
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testGetTestPlayerGUI(): void
    {
        $il_test_player_abstract_gui = $this->createMock(ilTestPlayerAbstractGUI::class);
        $il_test_info_screen_toolbar_gui = $this->createInstanceOf(
            ilTestInfoScreenToolbarGUI::class,
            ['test_player_gui' => $il_test_player_abstract_gui]
        );

        $this->assertEquals(
            $il_test_player_abstract_gui,
            self::callMethod(
                $il_test_info_screen_toolbar_gui,
                'getTestPlayerGUI'
            )
        );
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testGetTestSession(): void
    {
        $il_test_session = $this->createMock(ilTestSession::class);
        $il_test_info_screen_toolbar_gui = $this->createInstanceOf(
            ilTestInfoScreenToolbarGUI::class,
            ['test_session' => $il_test_session]
        );

        $this->assertEquals(
            $il_test_session,
            self::callMethod(
                $il_test_info_screen_toolbar_gui,
                'getTestSession'
            )
        );
    }

    /**
     * @dataProvider setAndGetSessionLockStringDataProvider
     * @throws Exception|ReflectionException
     */
    public function testSetAndGetSessionLockString(?string $IO): void
    {
        $il_test_info_screen_toolbar_gui = $this->createInstanceOf(ilTestInfoScreenToolbarGUI::class);

        if ($IO !== null) {
            $il_test_info_screen_toolbar_gui->setSessionLockString($IO);
        }
        $this->assertEquals($IO, $il_test_info_screen_toolbar_gui->getSessionLockString());
    }

    public static function setAndGetSessionLockStringDataProvider(): array
    {
        return [
            'default' => [null],
            'empty' => [''],
            'string' => ['string'],
            'strING' => ['strING']
        ];
    }

    /**
     * @dataProvider getAndAddInfoMessageDataProvider
     * @throws Exception|ReflectionException
     */
    public function testGetAndAddInfoMessage(?array $IO): void
    {
        $il_test_info_screen_toolbar_gui = $this->createInstanceOf(ilTestInfoScreenToolbarGUI::class);

        foreach ($IO ?? [] as $info_message) {
            $il_test_info_screen_toolbar_gui->addInfoMessage($info_message);
        }

        $this->assertEquals($IO ?? [], $il_test_info_screen_toolbar_gui->getInfoMessages());
    }

    public static function getAndAddInfoMessageDataProvider(): array
    {
        return [
            'default' => [null],
            'empty' => [['']],
            'string' => [['string']],
            'strING' => [['strING']],
            'empty_empty' => [['', '']],
            'string_string' => [['string', 'string']],
            'strING_strING' => [['strING', 'strING']],
            'empty_string' => [['', 'empty_string']],
            'string_strING' => [['string', 'strING']],
            'strING_empty' => [['strING', '']],
            'empty_strING' => [['', 'strING']],
            'string_empty' => [['string', '']],
            'strING_string' => [['strING', 'string']]
        ];
    }

    /**
     * @dataProvider getAndAddFailureMessageDataProvider
     * @throws Exception|ReflectionException
     */
    public function testGetAndAddFailureMessage(?array $IO): void
    {
        $il_test_info_screen_toolbar_gui = $this->createInstanceOf(ilTestInfoScreenToolbarGUI::class);

        foreach ($IO ?? [] as $failure_message) {
            $il_test_info_screen_toolbar_gui->addFailureMessage($failure_message);
        }

        $this->assertEquals($IO ?? [], $il_test_info_screen_toolbar_gui->getFailureMessages());
    }

    public static function getAndAddFailureMessageDataProvider(): array
    {
        return [
            'default' => [null],
            'empty' => [['']],
            'string' => [['string']],
            'strING' => [['strING']],
            'empty_empty' => [['', '']],
            'string_string' => [['string', 'string']],
            'strING_strING' => [['strING', 'strING']],
            'empty_string' => [['', 'empty_string']],
            'string_strING' => [['string', 'strING']],
            'strING_empty' => [['strING', '']],
            'empty_strING' => [['', 'strING']],
            'string_empty' => [['string', '']],
            'strING_string' => [['strING', 'string']]
        ];
    }

    /**
     * @dataProvider setFormActionDataProvider
     * @throws Exception|ReflectionException
     */
    public function testSetFormAction(array $IO): void
    {
        $il_test_info_screen_toolbar_gui = $this->createInstanceOf(ilTestInfoScreenToolbarGUI::class);

        $this->adaptDICServiceMock(ilToolbarGUI::class, function (ilToolbarGUI|MockObject $mock) use ($IO) {
            $mock
                ->expects($this->once())
                ->method('setFormAction')
                ->with($IO['val'], $IO['multipart'] ?? false, $IO['target'] ?? '');
        });

        if (isset($IO['multipart'], $IO['target'])) {
            $il_test_info_screen_toolbar_gui->setFormAction(
                $IO['val'],
                $IO['multipart'],
                $IO['target']
            );
            return;
        }

        if (isset($IO['multipart'])) {
            $il_test_info_screen_toolbar_gui->setFormAction(
                $IO['val'],
                $IO['multipart']
            );
            return;
        }

        if (isset($IO['target'])) {
            $il_test_info_screen_toolbar_gui->setFormAction(
                $IO['val'],
                a_target: $IO['target']
            );
            return;
        }

        $il_test_info_screen_toolbar_gui->setFormAction(
            $IO['val']
        );
    }

    public static function setFormActionDataProvider(): array
    {
        return [
            'default_empty' => [
                [
                    'val' => ''
                ]
            ],
            'default_string' => [
                [
                    'val' => 'string'
                ]
            ],
            'default_strING' => [
                [
                    'val' => 'strING'
                ]
            ],
            'empty_empty_false' => [
                [
                    'val' => '',
                    'multipart' => false,
                    'target' => ''
                ]
            ],
            'empty_empty_true' => [
                [
                    'val' => '',
                    'multipart' => true,
                    'target' => ''
                ]
            ],
            'string_string_false' => [
                [
                    'val' => 'string',
                    'multipart' => false,
                    'target' => 'string'
                ]
            ],
            'string_string_true' => [
                [
                    'val' => 'string',
                    'multipart' => true,
                    'target' => 'string'
                ]
            ],
            'strING_strING_false' => [
                [
                    'val' => 'strING',
                    'multipart' => false,
                    'target' => 'strING'
                ]
            ],
            'strING_strING_true' => [
                [
                    'val' => 'strING',
                    'multipart' => true,
                    'target' => 'strING'
                ]
            ],
            'empty_string_false' => [
                [
                    'val' => '',
                    'multipart' => false,
                    'target' => 'string'
                ]
            ],
            'empty_string_true' => [
                [
                    'val' => '',
                    'multipart' => true,
                    'target' => 'string'
                ]
            ],
            'empty_strING_false' => [
                [
                    'val' => '',
                    'multipart' => false,
                    'target' => 'strING'
                ]
            ],
            'empty_strING_true' => [
                [
                    'val' => '',
                    'multipart' => true,
                    'target' => 'strING'
                ]
            ],
            'string_empty_false' => [
                [
                    'val' => 'string',
                    'multipart' => false,
                    'target' => ''
                ]
            ],
            'string_empty_true' => [
                [
                    'val' => 'string',
                    'multipart' => true,
                    'target' => ''
                ]
            ],
            'string_strING_false' => [
                [
                    'val' => 'string',
                    'multipart' => false,
                    'target' => 'strING'
                ]
            ],
            'string_strING_true' => [
                [
                    'val' => 'string',
                    'multipart' => true,
                    'target' => 'strING'
                ]
            ],
            'strING_empty_false' => [
                [
                    'val' => 'strING',
                    'multipart' => false,
                    'target' => ''
                ]
            ],
            'strING_empty_true' => [
                [
                    'val' => 'strING',
                    'multipart' => true,
                    'target' => ''
                ]
            ],
            'strING_string_false' => [
                [
                    'val' => 'strING',
                    'multipart' => false,
                    'target' => 'string'
                ]
            ],
            'strING_string_true' => [
                [
                    'val' => 'strING',
                    'multipart' => true,
                    'target' => 'string'
                ]
            ]
        ];
    }

    /**
     * @dataProvider setCloseFormTagDataProvider
     * @throws Exception|ReflectionException
     */
    public function testSetCloseFormTag(bool $IO): void
    {
        $il_test_info_screen_toolbar_gui = $this->createInstanceOf(ilTestInfoScreenToolbarGUI::class);

        $this->adaptDICServiceMock(ilToolbarGUI::class, function (ilToolbarGUI|MockObject $mock) use ($IO) {
            $mock
                ->expects($this->once())
                ->method('setCloseFormTag')
                ->with($IO);
        });

        $il_test_info_screen_toolbar_gui->setCloseFormTag($IO);
    }

    public static function setCloseFormTagDataProvider(): array
    {
        return [
            'false' => [false],
            'true' => [true]
        ];
    }

    /**
     * @dataProvider addInputItemDataProvider
     * @throws Exception|ReflectionException
     */
    public function testAddInputItem(?bool $IO): void
    {
        $il_test_info_screen_toolbar_gui = $this->createInstanceOf(ilTestInfoScreenToolbarGUI::class);
        $il_toolbar_item = $this->createMock(ilToolbarItem::class);

        $this->adaptDICServiceMock(ilToolbarGUI::class, function (ilToolbarGUI|MockObject $mock) use ($IO, $il_toolbar_item) {
            $with = [$il_toolbar_item];
            if ($IO !== null) {
                $with[] = $IO;
            }

            $mock
                ->expects($this->once())
                ->method('addInputItem')
                ->with(...$with);
        });

        if ($IO === null) {
            $il_test_info_screen_toolbar_gui->addInputItem($il_toolbar_item);
            return;
        }

        $il_test_info_screen_toolbar_gui->addInputItem($il_toolbar_item, $IO);
    }

    public static function addInputItemDataProvider(): array
    {
        return [
            'default' => [null],
            'false' => [false],
            'true' => [true]
        ];
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testClearItems(): void
    {
        $il_test_info_screen_toolbar_gui = $this->createInstanceOf(ilTestInfoScreenToolbarGUI::class);

        $this->adaptDICServiceMock(ilToolbarGUI::class, function (ilToolbarGUI|MockObject $mock) {
            $mock
                ->expects($this->once())
                ->method('setItems')
                ->with([]);
        });

        $il_test_info_screen_toolbar_gui->clearItems();
    }

    /**
     * @dataProvider getClassNameDataProvider
     * @throws Exception|ReflectionException
     */
    public function testGetClassName(string|object $input, string $output): void
    {
        $il_test_info_screen_toolbar_gui = $this->createInstanceOf(ilTestInfoScreenToolbarGUI::class);
        $this->assertEquals($output, self::callMethod($il_test_info_screen_toolbar_gui, 'getClassName', [$input]));
    }

    public static function getClassNameDataProvider(): array
    {
        return [
            'string' => ['string', 'string'],
            'strING' => ['strING', 'strING'],
            'object' => [new stdClass(), stdClass::class],
        ];
    }

    /**
     * @dataProvider getClassNameArrayDataProvider
     * @throws Exception|ReflectionException
     */
    public function testGetClassNameArray(string|object|array $input, array $output): void
    {
        $il_test_info_screen_toolbar_gui = $this->createInstanceOf(ilTestInfoScreenToolbarGUI::class);
        $this->assertEquals($output, self::callMethod($il_test_info_screen_toolbar_gui, 'getClassNameArray', [$input]));
    }

    public static function getClassNameArrayDataProvider(): array
    {
        return [
            'empty' => ['', ['']],
            'string' => ['string', ['string']],
            'strING' => ['strING', ['strING']],
            'object' => [new stdClass(), [stdClass::class]],
            'array_empty' => [[''], ['']],
            'array_string' => [['string'], ['string']],
            'array_strING' => [['strING'], ['strING']],
            'array_object' => [[new stdClass()], [new stdClass()]],
            'array_string_empty' => [['string', ''], ['string', '']],
            'array_string_string' => [['string', 'string'], ['string', 'string']],
            'array_string_strING' => [['string', 'strING'], ['string', 'strING']],
            'array_string_object' => [['string', new stdClass()], ['string', new stdClass()]],
            'array_strING_empty' => [['strING', ''], ['strING', '']],
            'array_strING_string' => [['strING', 'string'], ['strING', 'string']],
            'array_strING_strING' => [['strING', 'strING'], ['strING', 'strING']],
            'array_strING_object' => [['strING', new stdClass()], ['strING', new stdClass()]],
            'array_object_empty' => [[new stdClass(), ''], [new stdClass(), '']],
            'array_object_string' => [[new stdClass(), 'string'], [new stdClass(), 'string']],
            'array_object_strING' => [[new stdClass(), 'strING'], [new stdClass(), 'strING']],
            'array_object_object' => [[new stdClass(), new stdClass()], [new stdClass(), new stdClass()]],
            'array_empty_empty' => [['', ''], ['', '']],
            'array_empty_string' => ['', ['']],
            'array_empty_strING' => [['', 'strING'], ['', 'strING']],
            'array_empty_object' => [['', new stdClass()], ['', new stdClass()]]
        ];
    }

    /**
     * @dataProvider getClassPathDataProvider
     * @throws Exception|ReflectionException
     */
    public function testGetClassPath(object|string|array $input, array $output): void
    {
        $il_test_info_screen_toolbar_gui = $this->createInstanceOf(ilTestInfoScreenToolbarGUI::class);
        $this->assertEquals($output, self::callMethod($il_test_info_screen_toolbar_gui, 'getClassPath', [$input]));
    }

    public static function getClassPathDataProvider(): array
    {
        return [
            'empty' => ['', [ilRepositoryGUI::class, ilObjTestGUI::class, '']],
            'string' => ['string', [ilRepositoryGUI::class, ilObjTestGUI::class, 'string']],
            'strING' => ['strING', [ilRepositoryGUI::class, ilObjTestGUI::class, 'strING']],
            'object' => [new stdClass(), [ilRepositoryGUI::class, ilObjTestGUI::class, stdClass::class]],
            'array_empty' => [[''], [ilRepositoryGUI::class, ilObjTestGUI::class, '']],
            'array_string' => [['string'], [ilRepositoryGUI::class, ilObjTestGUI::class, 'string']],
            'array_strING' => [['strING'], [ilRepositoryGUI::class, ilObjTestGUI::class, 'strING']],
            'array_object' => [[new stdClass()], [ilRepositoryGUI::class, ilObjTestGUI::class, new stdClass()]],
            'array_string_empty' => [['string', ''], [ilRepositoryGUI::class, ilObjTestGUI::class, 'string', '']],
            'array_string_string' => [['string', 'string'], [ilRepositoryGUI::class, ilObjTestGUI::class, 'string', 'string']],
            'array_string_strING' => [['string', 'strING'], [ilRepositoryGUI::class, ilObjTestGUI::class, 'string', 'strING']],
            'array_string_object' => [['string', new stdClass()], [ilRepositoryGUI::class, ilObjTestGUI::class, 'string', new stdClass()]],
            'array_strING_empty' => [['strING', ''], [ilRepositoryGUI::class, ilObjTestGUI::class, 'strING', '']],
            'array_strING_string' => [['strING', 'string'], [ilRepositoryGUI::class, ilObjTestGUI::class, 'strING', 'string']],
            'array_strING_strING' => [['strING', 'strING'], [ilRepositoryGUI::class, ilObjTestGUI::class, 'strING', 'strING']],
            'array_strING_object' => [['strING', new stdClass()], [ilRepositoryGUI::class, ilObjTestGUI::class, 'strING', new stdClass()]],
            'array_object_empty' => [[new stdClass(), ''], [ilRepositoryGUI::class, ilObjTestGUI::class, new stdClass(), '']],
            'array_object_string' => [[new stdClass(), 'string'], [ilRepositoryGUI::class, ilObjTestGUI::class, new stdClass(), 'string']],
            'array_object_strING' => [[new stdClass(), 'strING'], [ilRepositoryGUI::class, ilObjTestGUI::class, new stdClass(), 'strING']],
            'array_object_object' => [[new stdClass(), new stdClass()], [ilRepositoryGUI::class, ilObjTestGUI::class, new stdClass(), new stdClass()]],
            'array_empty_empty' => [['', ''], [ilRepositoryGUI::class, ilObjTestGUI::class, '', '']],
            'array_empty_string' => ['', [ilRepositoryGUI::class, ilObjTestGUI::class, '']],
            'array_empty_strING' => [['', 'strING'], [ilRepositoryGUI::class, ilObjTestGUI::class, '', 'strING']],
            'array_empty_object' => [['', new stdClass()], [ilRepositoryGUI::class, ilObjTestGUI::class, '', new stdClass()]]
        ];
    }

    /**
     * @dataProvider setParameterDataProvider
     * @throws Exception|ReflectionException
     */
    public function testSetParameter(array $input, string $output): void
    {
        $il_test_info_screen_toolbar_gui = $this->createInstanceOf(ilTestInfoScreenToolbarGUI::class);

        $this->adaptDICServiceMock(ilCtrl::class, function (ilCtrl|MockObject $mock) use ($output, $input) {
            $mock
                ->expects($this->once())
                ->method('setParameterByClass')
                ->with($output, $input['parameter'], $input['value']);
        });

        self::callMethod($il_test_info_screen_toolbar_gui, 'setParameter', $input);
    }

    public static function setParameterDataProvider(): array
    {
        return [
            'empty_empty_empty' => [['target' => '', 'parameter' => '', 'value' => ''], ''],
            'string_empty_empty' => [['target' => 'string', 'parameter' => '', 'value' => ''], 'string'],
            'strING_empty_empty' => [['target' => 'strING', 'parameter' => '', 'value' => ''], 'strING'],
            'object_empty_empty' => [['target' => new stdClass(), 'parameter' => '', 'value' => ''], stdClass::class],
            'empty_string_empty' => [['target' => '', 'parameter' => 'string', 'value' => ''], ''],
            'string_string_empty' => [['target' => 'string', 'parameter' => 'string', 'value' => ''], 'string'],
            'strING_string_empty' => [['target' => 'strING', 'parameter' => 'string', 'value' => ''], 'strING'],
            'object_string_empty' => [['target' => new stdClass(), 'parameter' => 'string', 'value' => ''], stdClass::class],
            'empty_empty_string' => [['target' => '', 'parameter' => '', 'value' => 'string'], ''],
            'string_empty_string' => [['target' => 'string', 'parameter' => '', 'value' => 'string'], 'string'],
            'strING_empty_string' => [['target' => 'strING', 'parameter' => '', 'value' => 'string'], 'strING'],
            'object_empty_string' => [['target' => new stdClass(), 'parameter' => '', 'value' => 'string'], stdClass::class]
        ];
    }

    /**
     * @dataProvider buildLinkTargetDataProvider
     * @throws Exception|ReflectionException
     */
    public function testBuildLinkTarget(array $input, string $output): void
    {
        $il_test_info_screen_toolbar_gui = $this->createInstanceOf(ilTestInfoScreenToolbarGUI::class);

        $this->adaptDICServiceMock(ilCtrl::class, function (ilCtrl|MockObject $mock) use ($input) {
            $class = ['ilRepositoryGUI', 'ilObjTestGUI'];
            $class[] = $input['class'];

            $with = [$class];
            $callback = static fn(array $class) => implode('/', $class);
            if (isset($input['cmd'])) {
                $with[] = $input['cmd'];
                $callback = static function (array $class, string $cmd): string {
                    $class[] = $cmd;
                    return implode('/', $class);
                };
            }

            $mock
                ->expects($this->once())
                ->method('getLinkTargetByClass')
                ->with(...$with)
                ->willReturnCallback($callback);
        });

        $this->assertEquals($output, self::callMethod($il_test_info_screen_toolbar_gui, 'buildLinkTarget', array_values($input)));
    }

    public static function buildLinkTargetDataProvider(): array
    {
        return [
            'empty' => [['class' => ''], 'ilRepositoryGUI/ilObjTestGUI/'],
            'string' => [['class' => 'string'], 'ilRepositoryGUI/ilObjTestGUI/string'],
            'strING' => [['class' => 'strING'], 'ilRepositoryGUI/ilObjTestGUI/strING'],
            'empty_empty' => [['class' => '', 'cmd' => ''], 'ilRepositoryGUI/ilObjTestGUI//'],
            'string_empty' => [['class' => 'string', 'cmd' => ''], 'ilRepositoryGUI/ilObjTestGUI/string/'],
            'strING_empty' => [['class' => 'strING', 'cmd' => ''], 'ilRepositoryGUI/ilObjTestGUI/strING/'],
            'empty_string' => [['class' => '', 'cmd' => 'string'], 'ilRepositoryGUI/ilObjTestGUI//string'],
            'string_string' => [['class' => 'string', 'cmd' => 'string'], 'ilRepositoryGUI/ilObjTestGUI/string/string'],
            'strING_string' => [['class' => 'strING', 'cmd' => 'string'], 'ilRepositoryGUI/ilObjTestGUI/strING/string']
        ];
    }

    /**
     * @dataProvider buildFormActionDataProvider
     * @throws Exception|ReflectionException
     */
    public function testBuildFormAction(string $input, string $output): void
    {
        $il_test_info_screen_toolbar_gui = $this->createInstanceOf(ilTestInfoScreenToolbarGUI::class);

        $this->adaptDICServiceMock(ilCtrl::class, function (ilCtrl|MockObject $mock) use ($input) {
            $class = ['ilRepositoryGUI', 'ilObjTestGUI', $input];

            $mock
                ->expects($this->once())
                ->method('getFormActionByClass')
                ->with($class)
                ->willReturnCallback(fn(array $class) => 'action: ' . implode('/', $class));
        });

        $this->assertEquals($output, self::callMethod($il_test_info_screen_toolbar_gui, 'buildFormAction', [$input]));
    }

    public static function buildFormActionDataProvider(): array
    {
        return [
            'empty' => ['', 'action: ilRepositoryGUI/ilObjTestGUI/'],
            'string' => ['string', 'action: ilRepositoryGUI/ilObjTestGUI/string'],
            'strING' => ['strING', 'action: ilRepositoryGUI/ilObjTestGUI/strING']
        ];
    }

    /**
     * @dataProvider ensureInitialisedSessionLockStringDataProvider
     * @throws Exception|ReflectionException
     */
    public function testEnsureInitialisedSessionLockString(?string $input, int $output): void
    {
        $il_test_info_screen_toolbar_gui = $this->createInstanceOf(ilTestInfoScreenToolbarGUI::class);

        if ($input !== null) {
            $il_test_info_screen_toolbar_gui->setSessionLockString($input);
        }

        $_COOKIE['PHPSESSID'] = '';

        self::callMethod($il_test_info_screen_toolbar_gui, 'ensureInitialisedSessionLockString');
        $this->assertEquals($output, strlen(self::getNonPublicPropertyValue($il_test_info_screen_toolbar_gui, 'sessionLockString')));

        unset($_COOKIE['PHPSESSID']);
    }

    public static function ensureInitialisedSessionLockStringDataProvider(): array
    {
        return [
            'null' => [null, 32],
            'empty' => ['', 32],
            'string' => ['string', 6],
            'strING' => ['strING', 6]
        ];
    }

    /**
     * @dataProvider buildSessionLockStringDataProvider
     * @throws Exception|ReflectionException
     */
    public function testBuildSessionLockString(string $input): void
    {
        $il_test_info_screen_toolbar_gui = $this->createInstanceOf(ilTestInfoScreenToolbarGUI::class);

        $_COOKIE['PHPSESSID'] = $input;

        $this->assertEquals(md5($input . time()), self::callMethod($il_test_info_screen_toolbar_gui, 'buildSessionLockString'));

        unset($_COOKIE['PHPSESSID']);
    }

    public static function buildSessionLockStringDataProvider(): array
    {
        return [
            'empty' => [''],
            'string' => ['string'],
            'strING' => ['strING']
        ];
    }

    /**
     * @dataProvider hasFixedQuestionSetSkillAssignsLowerThanBarrierDataProvider
     * @throws Exception|ReflectionException
     */
    public function testHasFixedQuestionSetSkillAssignsLowerThanBarrier(array $input, bool $output): void
    {
        $test_obj = $this->createMock(ilObjTest::class);
        $test_obj
            ->expects($this->once())
            ->method('isFixedTest')
            ->willReturn($input['is_fixed_test']);
        if ($input['is_fixed_test']) {
            $test_obj
                ->expects($this->once())
                ->method('getId')
                ->willReturn($input['id']);
            $il_db_statement = $this->createMock(ilDBStatement::class);

            $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) use ($il_db_statement) {
                $mock
                    ->method('query')
                    ->willReturn($il_db_statement);
            });

            $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) use ($il_db_statement) {
                $mock
                    ->method('fetchAssoc')
                    ->with($il_db_statement)
                    ->willReturn([]);
            });
        }

        $il_test_info_screen_toolbar_gui = $this->createInstanceOf(
            ilTestInfoScreenToolbarGUI::class,
            ['test_obj' => $test_obj]
        );

        $this->assertEquals($output, self::callMethod($il_test_info_screen_toolbar_gui, 'hasFixedQuestionSetSkillAssignsLowerThanBarrier'));
    }

    public static function hasFixedQuestionSetSkillAssignsLowerThanBarrierDataProvider(): array
    {
        return [
            'false' => [['is_fixed_test' => false], false],
            'true_-1' => [['is_fixed_test' => true, 'id' => -1], false],
            'true_0' => [['is_fixed_test' => true, 'id' => 0], false],
            'true_1' => [['is_fixed_test' => true, 'id' => 1], false],
            'false_empty' => [['is_fixed_test' => false, 'skill_assigns' => []], false],
            'false_null' => [['is_fixed_test' => false, 'skill_assigns' => null], false]
        ];
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testGetSkillAssignBarrierInfo(): void
    {
        $il_test_info_screen_toolbar_gui = $this->createInstanceOf(ilTestInfoScreenToolbarGUI::class);

        $txt = 'tst_skill_triggerings_num_req_answers_not_reached_warn';
        $this->adaptDICServiceMock(ilLanguage::class, function (ilLanguage|MockObject $mock) use ($txt) {
            $mock
                ->expects($this->once())
                ->method('txt')
                ->with($txt)
                ->willReturn($txt . '_%s');
        });

        $this->assertStringContainsString($txt, self::callMethod($il_test_info_screen_toolbar_gui, 'getSkillAssignBarrierInfo'));
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testBuild(): void
    {
        $this->adaptDICServiceMock(ilAccess::class, function (ilAccess|MockObject $mock) {
            $mock
                ->method('checkAccess')
                ->with('write', '', 0)
                ->willReturn(true);
        });
        $il_test_question_set_config = $this->createMock(ilTestQuestionSetConfig::class);
        $il_test_question_set_config
            ->method('areDepenciesBroken')
            ->willReturn(false);
        $il_obj_test = $this->createMock(ilObjTest::class);
        $il_obj_test
            ->method('getOfflineStatus')
            ->willReturn(true);
        $il_test_info_screen_toolbar_gui = $this->createInstanceOf(
            ilTestInfoScreenToolbarGUI::class,
            ['test_obj' => $il_obj_test, 'test_question_set_config' => $il_test_question_set_config]
        );

        $_COOKIE['PHPSESSID'] = '';

        $this->assertNull($il_test_info_screen_toolbar_gui->build());

        unset($_COOKIE['PHPSESSID']);
    }

    /**
     * @dataProvider populateMessageDataProvider
     * @throws Exception|ReflectionException
     */
    public function testPopulateMessage(string $input): void
    {
        $il_test_info_screen_toolbar_gui = $this->createInstanceOf(ilTestInfoScreenToolbarGUI::class);

        $this->adaptDICServiceMock(ilGlobalTemplateInterface::class, function (ilGlobalTemplateInterface|MockObject $mock) {
            $mock
                ->expects($this->once())
                ->method('setCurrentBlock')
                ->with('mess');
        });

        $this->adaptDICServiceMock(ilGlobalTemplateInterface::class, function (ilGlobalTemplateInterface|MockObject $mock) use ($input) {
            $mock
                ->expects($this->once())
                ->method('setVariable')
                ->with('MESSAGE', $input);
        });
        $this->adaptDICServiceMock(ilGlobalTemplateInterface::class, function (ilGlobalTemplateInterface|MockObject $mock) {
            $mock
                ->expects($this->once())
                ->method('parseCurrentBlock');
        });

        self::callMethod($il_test_info_screen_toolbar_gui, 'populateMessage', [$input]);
    }

    public static function populateMessageDataProvider(): array
    {
        return [
            'empty' => [''],
            'string' => ['string'],
            'strING' => ['strING']
        ];
    }

    /**
     * @dataProvider sendMessagesDataProvider
     * @throws Exception|ReflectionException
     */
    public function testSendMessages(array $input): void
    {
        $il_test_info_screen_toolbar_gui = $this->createInstanceOf(ilTestInfoScreenToolbarGUI::class);

        $info_message = (int) (bool) count($input['info_messages']);
        $failure_message = (int) (bool) count($input['failure_messages']);

        $this->adaptDICServiceMock(ilGlobalTemplateInterface::class, function (ilGlobalTemplateInterface|MockObject $mock) use ($info_message, $failure_message) {
            $mock
                ->expects($this->exactly($info_message + $failure_message))
                ->method('setOnScreenMessage');
        });

        foreach ($input['info_messages'] as $info_message) {
            $il_test_info_screen_toolbar_gui->addInfoMessage($info_message);
        }

        foreach ($input['failure_messages'] as $failure_message) {
            $il_test_info_screen_toolbar_gui->addFailureMessage($failure_message);
        }

        $il_test_info_screen_toolbar_gui->sendMessages();
    }

    public static function sendMessagesDataProvider(): array
    {
        return [
            'empty' => [
                [
                    'info_messages' => [],
                    'failure_messages' => []
                ]
            ],
            'info' => [
                [
                    'info_messages' => ['info'],
                    'failure_messages' => []
                ]
            ],
            'failure' => [
                [
                    'info_messages' => [],
                    'failure_messages' => ['failure']
                ]
            ],
            'info_failure' => [
                [
                    'info_messages' => ['info'],
                    'failure_messages' => ['failure']
                ]
            ],
            'info_info' => [
                [
                    'info_messages' => ['info', 'info'],
                    'failure_messages' => []
                ]
            ],
            'failure_failure' => [
                [
                    'info_messages' => [],
                    'failure_messages' => ['failure', 'failure']
                ]
            ],
            'info_info_failure' => [
                [
                    'info_messages' => ['info', 'info'],
                    'failure_messages' => ['failure']
                ]
            ],
            'info_failure_failure' => [
                [
                    'info_messages' => ['info'],
                    'failure_messages' => ['failure', 'failure']
                ]
            ]
        ];
    }
}
