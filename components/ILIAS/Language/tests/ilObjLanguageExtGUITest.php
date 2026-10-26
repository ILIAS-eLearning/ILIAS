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

use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\URLBuilderToken;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

require_once __DIR__ . '/../../UI/tests/UITestHelper.php';

class ilObjLanguageExtGUITest extends TestCase
{
    use RestoresProcessState;

    use UITestHelper;
    use ilObjLanguageExtGUITestFixture {
        tearDown as private tearDownGuiTestSupport;
    }

    /** The real, static ilSession writes into $_SESSION. */
    protected function tearDown(): void
    {
        $_SESSION = [];
        $this->tearDownGuiTestSupport();
    }

    /** A data-changing command missing here would be triggerable via GET without the CSRF token. */
    public function testGetUnsafeGetCommandsListsEveryDataChangingCommand(): void
    {
        $gui = $this->newGuiWithoutConstructor(ilObjLanguageExtGUI::class);

        $this->assertSame(
            ['saveEditedEntries', 'upload', 'maintainExecute', 'saveSettings', 'saveNewEntry'],
            $gui->getUnsafeGetCommands()
        );
    }

    /** No POST command is exempt from the CSRF token. */
    public function testGetSafePostCommandsIsEmpty(): void
    {
        $gui = $this->newGuiWithoutConstructor(ilObjLanguageExtGUI::class);

        $this->assertSame([], $gui->getSafePostCommands());
    }

    /** @return array<string, array{0: string, 1: \Closure}> */
    public static function importedMessageProvider(): array
    {
        return [
            'HTML-significant characters are escaped' => [
                '<script>x</script>.lang',
                static function (self $test, string $message): void {
                    $test->assertStringNotContainsString('<script>', $message);
                    $test->assertStringContainsString('&lt;script&gt;', $message);
                    $test->assertStringContainsString('&lt;/script&gt;', $message);
                },
            ],
            'an ordinary file name is left unchanged' => [
                'ilias_de.lang',
                static function (self $test, string $message): void {
                    $test->assertStringContainsString('ilias_de.lang', $message);
                },
            ],
        ];
    }

    /** Calls the real private importedMessage() via reflection: an uploaded file name must come out HTML-escaped. */
    #[DataProvider('importedMessageProvider')]
    public function testImportedMessageEscapesHtmlSignificantCharactersAndKeepsTheLngText(
        string $file_name,
        \Closure $assertOnMessage
    ): void {
        $lng = $this->createStub(ilLanguage::class);
        $lng->method('txt')->willReturnCallback(
            static fn(string $key): string => $key === 'language_file_imported' ? 'Imported %s.' : $key
        );

        $gui = $this->newGuiWithoutConstructor(ilObjLanguageExtGUI::class);
        $this->inject($gui, 'lng', $lng);
        $this->inject($gui, 'refinery', new \ILIAS\Refinery\Factory(
            $this->createStub(\ILIAS\Data\Factory::class),
            $this->createStub(\ILIAS\Language\Language::class)
        ));

        $method = new ReflectionMethod(ilObjLanguageExtGUI::class, 'importedMessage');
        $message = $method->invoke($gui, $file_name);

        $this->assertStringStartsWith('Imported ', $message);
        $assertOnMessage($this, $message);
    }

    /** Only object and lng: effectiveRemarkOf()/remarkOf() need no UI collaborators. */
    private function entriesTableFor(ilObjLanguageExt $object, ilLanguage $lng): ilLanguageEntriesTable
    {
        $table = (new ReflectionClass(ilLanguageEntriesTable::class))->newInstanceWithoutConstructor();
        $this->inject($table, 'object', $object);
        $this->inject($table, 'lng', $lng);

        return $table;
    }

    /** @param array<string, string> $comments */
    private function callRemarkOf(array $comments, string $name): string
    {
        $table = $this->entriesTableFor($this->createStub(ilObjLanguageExt::class), $this->createStub(ilLanguage::class));

        return (new ReflectionMethod(ilLanguageEntriesTable::class, 'remarkOf'))->invoke($table, $comments, $name);
    }

    public function testRemarkOfReturnsTheCommentOfTheGivenKeyItselfWhenPresent(): void
    {
        $this->assertSame(
            'Bitte prüfen',
            $this->callRemarkOf(['pilot#:#greeting' => 'Bitte prüfen'], 'pilot#:#greeting')
        );
    }

    /** A plural form ("identifier [form]") without a comment of its own falls back to the comment of its identifier. */
    public function testRemarkOfFallsBackToTheIdentifiersCommentForAPluralFormKeyWithoutItsOwn(): void
    {
        $this->assertSame(
            'Bitte prüfen',
            $this->callRemarkOf(['pilot#:#item' => 'Bitte prüfen'], 'pilot#:#item [1]')
        );
    }

    /** A plural form's own comment wins over the identifier's. */
    public function testRemarkOfPrefersThePluralFormsOwnCommentOverTheIdentifiersWhenBothArePresent(): void
    {
        $this->assertSame(
            'Form-spezifisch',
            $this->callRemarkOf(
                ['pilot#:#item' => 'Identifier-Bemerkung', 'pilot#:#item [1]' => 'Form-spezifisch'],
                'pilot#:#item [1]'
            )
        );
    }

    public function testRemarkOfIsEmptyWithoutAnyMatchingComment(): void
    {
        $this->assertSame('', $this->callRemarkOf([], 'pilot#:#unknown'));
        $this->assertSame('', $this->callRemarkOf(['pilot#:#other' => 'X'], 'pilot#:#greeting'));
    }

    /** An ordinary identifier without a comment must not fall back to anything. */
    public function testRemarkOfIsEmptyForAnOrdinaryIdentifierWithoutAComment(): void
    {
        $this->assertSame('', $this->callRemarkOf(['pilot#:#item' => 'Bemerkung'], 'pilot#:#greeting'));
    }

    /** @return array<string, array{0: string|int, 1: string, 2: string}> topic, expected eid parameter, expected list text */
    public static function missingTopicProvider(): array
    {
        return [
            'HTML' => ['<b>x</b>', '%3Cb%3Ex%3C%2Fb%3E', '&lt;b&gt;x&lt;/b&gt;'],
            'quote, ampersand, space and hash' => ['a"b&c d#e', 'a%22b%26c%20d%23e', 'a&quot;b&amp;c d#e'],
            'a numeric topic arrives as int' => [123, '123', '123'],
        ];
    }

    /** The list text is HTML-escaped, the "eid" parameter URL-encoded and the link target safe inside the href attribute. */
    #[DataProvider('missingTopicProvider')]
    public function testBuildMissingEntriesEscapesTheTopicForTextParameterAndLink(string|int $topic, string $eid, string $text): void
    {
        $parameters = [];
        $ctrl = $this->createStub(ilCtrl::class);
        $ctrl->method('setParameter')->willReturnCallback(
            static function (object $gui, string $name, mixed $value) use (&$parameters): void {
                $parameters[] = [$name, $value];
            }
        );
        $ctrl->method('getLinkTarget')->willReturn('ilias.php?a=1&b="2"');
        $GLOBALS['DIC'] = new \ILIAS\DI\Container();
        $GLOBALS['DIC']['ilCtrl'] = $ctrl;
        $gui = $this->newGuiWithoutConstructor(ilObjLanguageExtGUI::class);
        $this->inject($gui, 'lng', $this->createLanguageMockReturningTopicAsIs());
        $this->inject($gui, 'refinery', new \ILIAS\Refinery\Factory(
            $this->createStub(\ILIAS\Data\Factory::class),
            $this->createStub(\ILIAS\Language\Language::class)
        ));

        $html = (new ReflectionMethod(ilObjLanguageExtGUI::class, 'buildMissingEntries'))->invoke($gui, [$topic]);

        $this->assertSame([['eid', $eid], ['eid', '']], $parameters);
        $this->assertStringContainsString('<li>' . $text . ' <a href="ilias.php&#x3F;a&#x3D;1&amp;b&#x3D;&quot;2&quot;">', $html);
    }

    // -----------------------------------------------------------------
    // edit modal: editSelectedObject(), buildEditEntriesModal(), saveEditedEntriesObject()
    //
    // Real KS factory (UITestHelper); initFilter()/resolveFilterData() are private, so they run for real
    // against a deactivated filter, the entries table is a mock.
    // -----------------------------------------------------------------

    private function invokeProtectedMethod(object $object, string $method_name, array $args = []): mixed
    {
        return (new ReflectionMethod($object, $method_name))->invoke($object, ...$args);
    }

    private static function maxInputVarsLimit(): int
    {
        $configured_limit = ini_get('max_input_vars');

        return ($configured_limit === false || $configured_limit === '') ? 1000 : (int) $configured_limit;
    }

    /** @var list<\ILIAS\UI\Component\Component> components handed to the renderer's renderAsync() */
    private array $rendered_async = [];
    private (ilDBInterface&MockObject)|null $edit_db = null;
    private (ilGlobalTemplateInterface&MockObject)|null $edit_tpl = null;
    private (ilCtrl&MockObject)|null $edit_ctrl = null;
    private (ServerRequestInterface&MockObject)|null $edit_request = null;
    private (ilLanguageEntriesTable&MockObject)|null $edit_table = null;

    /**
     * @param array<string, string> $values entry name => translation of the edited language
     * @param array<string, string> $remarks entry name => DB remark
     * @param array<string, string> $compare_values entry name => value of the compare language
     * @param array<string, list<string>> $session_selections stored edit selections: token => entry names
     * @param string|null $token edit selection token of the save request's query
     * @param array<string, mixed> $post parsed body of the save request
     * @param list<string> $modules modules of the language in the database (options of the module filter)
     */
    private function createGuiForEditModal(
        bool $langmode,
        array $values,
        array $remarks = [],
        array $compare_values = [],
        array $session_selections = [],
        ?string $token = null,
        array $post = [],
        array $filtered_names = [],
        array $modules = ['administration']
    ): ilObjLanguageExtGUI {
        // obj_id 0 is the id of the fake language object
        $_SESSION['lang_ext_edit_selection'] = array_map(
            static fn(array $names): array => ['obj_id' => 0, 'names' => $names],
            $session_selections
        );
        $dic = $this->init();
        $GLOBALS['DIC'] = $dic;

        $query_wrapper = $this->createMock(\ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper::class);
        $query_wrapper->method('has')->willReturn(false);
        $wrapper_factory = $this->createMock(\ILIAS\HTTP\Wrapper\WrapperFactory::class);
        $wrapper_factory->method('query')->willReturn($query_wrapper);

        $this->edit_request = $this->createMock(ServerRequestInterface::class);
        $this->edit_request->method('getParsedBody')->willReturn($post);
        $this->edit_request->method('getQueryParams')->willReturn([]);
        $this->edit_request->method('getUri')->willReturn($this->createMock(\Psr\Http\Message\UriInterface::class));

        $response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        $response->method('withBody')->willReturnSelf();
        $http = $this->createMock(\ILIAS\HTTP\Services::class);
        $http->method('wrapper')->willReturn($wrapper_factory);
        $http->method('request')->willReturn($this->edit_request);
        $http->method('response')->willReturn($response);
        $http->method('close')->willThrowException(new RuntimeException('http closed'));
        $dic['http'] = $http;

        $this->edit_db = $this->createMock(ilDBInterface::class);
        // only ilObjLanguageExt::_getModules() (the filter's module options) gets a row
        $this->edit_db->method('query')->willReturnCallback(function (string $sql) use ($modules): ilDBStatement {
            $statement = $this->createMock(ilDBStatement::class);
            $statement->method('fetchRow')->willReturnOnConsecutiveCalls(
                ...(str_contains($sql, 'SELECT DISTINCT module')
                    ? [...array_map(static fn(string $module): array => ['module' => $module], $modules), false]
                    : [false])
            );
            return $statement;
        });
        // the check after a lng_modules write reads the row back with queryF()
        $written_module_statement = $this->createMock(ilDBStatement::class);
        $this->edit_db->method('queryF')->willReturn($written_module_statement);
        $this->edit_db->method('fetchAssoc')->willReturnCallback(
            static fn(ilDBStatement $statement): ?array => $statement === $written_module_statement
                ? ['lang_array' => serialize([])]
                : null
        );
        $this->edit_db->method('quote')->willReturnCallback(static fn($value, string $type): string => "'" . $value . "'");
        $dic['ilDB'] = $this->edit_db;
        // only the "module not found" warning of _saveValues() is logged
        $dic['ilLoggerFactory'] = new class ($this->createMock(ilLogger::class)) {
            public function __construct(private readonly ilLogger $logger)
            {
            }

            public function getRootLogger(): ilLogger
            {
                return $this->logger;
            }

            public function getComponentLogger(string $component_id): ilLogger
            {
                return $this->logger;
            }
        };

        $lng = $this->createLanguageMockReturningTopicAsIs(['en', 'de']);
        $lng->method('getLangKey')->willReturn('de');
        $lng->method('getDefaultLanguage')->willReturn('en');

        $object = $this->createFakeLanguageObjectMock('de');
        $object->method('getAllValues')->willReturn($values);
        $object->method('getAllRemarks')->willReturn($remarks);

        $this->edit_table = $this->createMock(ilLanguageEntriesTable::class);
        $this->edit_table->method('getCompareTitle')->willReturn('English');
        $this->edit_table->method('getCompareValues')->willReturn($compare_values);
        $this->edit_table->method('effectiveRemarkOf')->willReturnCallback(
            static fn(string $name): string => (string) ($remarks[$name] ?? '')
        );
        $this->edit_table->method('getFilteredEntryNames')->willReturn($filtered_names ?: array_keys($values));

        $ui_renderer = $this->createMock(\ILIAS\UI\Renderer::class);
        $ui_renderer->method('render')->willReturn('rendered');
        $ui_renderer->method('renderAsync')->willReturnCallback(function ($component): string {
            $this->rendered_async[] = $component;
            return 'rendered';
        });
        $this->rendered_async = [];

        $this->edit_tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $this->edit_ctrl = $this->createMock(ilCtrl::class);
        $this->edit_ctrl->method('getFormAction')->willReturn('ilias.php?cmd=saveEditedEntries');
        $this->edit_ctrl->method('getLinkTarget')->willReturn('ilias.php?cmd=view');

        $request_wrapper = $this->createMock(RequestWrapper::class);
        $request_wrapper->method('has')->willReturnCallback(
            static fn(string $name): bool => $token !== null && $name === 'lang_entries_edit_selection'
        );
        $request_wrapper->method('retrieve')->willReturn($token);

        $gui = $this->getMockBuilder(ilObjLanguageExtGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        $this->inject($gui, 'object', $object);
        $this->inject($gui, 'lng', $lng);
        $this->inject($gui, 'ctrl', $this->edit_ctrl);
        $this->inject($gui, 'ui_factory', $this->createMock(\ILIAS\UI\Factory::class));
        $this->inject($gui, 'tpl', $this->edit_tpl);
        $this->inject($gui, 'http', $http);
        $this->inject($gui, 'request', $this->edit_request);
        $this->inject($gui, 'request_wrapper', $request_wrapper);
        $this->inject($gui, 'refinery', $dic['refinery']);
        $this->inject($gui, 'ui_factory', $this->factory());
        $this->inject($gui, 'ui_renderer', $ui_renderer);
        $declaring = new ReflectionClass(ilObjLanguageExtGUI::class);
        $declaring->getProperty('langmode')->setValue($gui, $langmode ? '1' : '0');
        $declaring->getProperty('entries_table')->setValue($gui, $this->edit_table);

        return $gui;
    }

    /** editSelectedObject() ends the request via http->close(), which the mock turns into an exception. */
    private function editSelected(ilObjLanguageExtGUI $gui, array $names): void
    {
        try {
            $this->invokeProtectedMethod($gui, 'editSelectedObject', [$names]);
            self::fail('The async answer must close the response.');
        } catch (RuntimeException $e) {
            self::assertSame('http closed', $e->getMessage());
        }
    }

    /**
     * @return array<string, \ILIAS\UI\Implementation\Component\Input\Field\FormInput>
     */
    private function inputsOf(\ILIAS\UI\Implementation\Component\Modal\RoundTrip $modal): array
    {
        $inputs = [];
        foreach ($modal->getInputs() as $key => $input) {
            self::assertInstanceOf(\ILIAS\UI\Implementation\Component\Input\Field\FormInput::class, $input);
            $inputs[$key] = $input;
        }

        return $inputs;
    }

    private function renderedModal(int $index): \ILIAS\UI\Implementation\Component\Modal\RoundTrip
    {
        self::assertInstanceOf(\ILIAS\UI\Implementation\Component\Modal\RoundTrip::class, $this->rendered_async[$index]);

        return $this->rendered_async[$index];
    }

    private function buildModal(
        ilObjLanguageExtGUI $gui,
        array $names,
        string $token = 'tok',
        bool $with_bylines = true
    ): \ILIAS\UI\Implementation\Component\Modal\RoundTrip {
        return $this->invokeProtectedMethod($gui, 'buildEditEntriesModal', [$names, $token, $with_bylines]);
    }

    /**
     * Post body for a modal: input name => value, addressed by the field keys of buildEditEntriesModal().
     *
     * @param array<string, string> $values field key => value
     */
    private function postBodyFor(\ILIAS\UI\Implementation\Component\Modal\RoundTrip $modal, array $values): array
    {
        $body = [];
        foreach ($this->inputsOf($modal) as $key => $input) {
            if (array_key_exists($key, $values)) {
                $body[$input->getName()] = $values[$key];
            }
        }

        return $body;
    }

    /**
     * Stands in for the static collaborators of ilObjLanguageExt::_saveValues() (global file, language cache).
     *
     * @return callable restores the static caches
     */
    private function stubSaveValuesStatics(): callable
    {
        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', realpath(__DIR__ . '/../../../../'));
        }
        $global_file = $this->createMock(ilLanguageFile::class);
        $global_file->method('getAllValues')->willReturn([]);
        $global_file->method('getAllComments')->willReturn([]);

        $files = new ReflectionProperty(ilLanguageFile::class, 'global_file_objects');
        $instances = new ReflectionProperty(ilCachedLanguage::class, 'instances');
        $files->setValue(null, ['de' => $global_file]);
        $instances->setValue(null, ['de' => $this->createMock(ilCachedLanguage::class)]);

        return static function () use ($files, $instances): void {
            $files->setValue(null, []);
            $instances->setValue(null, []);
        };
    }

    /**
     * Creates a GUI for a save request: the modal of $names built like the save does, filled with $fields.
     *
     * @param array<string, string> $fields field key => posted value
     * @param array<string, string> $values entries that exist
     * @param array<string, list<string>> $selections stored selections: token => names
     * @return array{0: ilObjLanguageExtGUI, 1: ArrayObject<string, array<string, array<int, mixed>>>} the GUI and the lng_data rows written, name => replace() data
     */
    private function createGuiForSave(
        bool $langmode,
        array $values,
        array $selections,
        ?string $token,
        array $fields,
        array $remarks = [],
        ?array $modal_names = null
    ): array {
        $factory = fn(array $post): ilObjLanguageExtGUI => $this->createGuiForEditModal(
            $langmode,
            $values,
            $remarks,
            [],
            $selections,
            $token,
            $post
        );
        $names = $modal_names ?? array_values(array_intersect($selections[$token] ?? [], array_keys($values)));
        $gui = $factory($this->postBodyFor($this->buildModal($factory([]), $names, '', false), $fields));

        /** @var ArrayObject<string, array<string, array<int, mixed>>> $replaced */
        $replaced = new ArrayObject();
        $this->edit_db->method('replace')->willReturnCallback(
            function (string $table, array $pk, array $data) use ($replaced): int {
                /** @var array<string, array<int, mixed>> $data */
                if ($table === 'lng_data') {
                    $replaced[$pk['module'][1] . '#:#' . $pk['identifier'][1]] = $data;
                }
                return 1;
            }
        );

        return [$gui, $replaced];
    }

    private function expectSaveRefused(ilObjLanguageExtGUI $gui, string $message_key): void
    {
        $this->edit_db->expects($this->never())->method('replace');
        $this->edit_tpl->expects($this->once())->method('setOnScreenMessage')->with('failure', $message_key, true);
        $this->edit_ctrl->expects($this->once())->method('redirect')->with($gui, 'view');
    }

    public function testEditSelectedObjectStoresTheExistingEntriesUnderATokenAndAnswersWithTheModal(): void
    {
        $gui = $this->createGuiForEditModal(false, ['common#:#access' => 'A', 'common#:#other' => 'B']);

        $this->editSelected($gui, ['common#:#access', 'forged#:#<b>x</b>', 'common#:#other']);

        self::assertCount(1, $this->rendered_async);
        $modal = $this->rendered_async[0];
        self::assertInstanceOf(\ILIAS\UI\Component\Modal\RoundTrip::class, $modal);
        self::assertSame(
            ['translation_0', 'translation_1', 'expected_entry_count'],
            array_keys($modal->getInputs())
        );
        $stored = $_SESSION['lang_ext_edit_selection'];
        self::assertCount(1, $stored);
        self::assertSame(['obj_id' => 0, 'names' => ['common#:#access', 'common#:#other']], reset($stored));
        self::assertStringEndsWith('&lang_entries_edit_selection=' . key($stored), $modal->getPostURL());
        self::assertStringNotContainsString('forged', $modal->getPostURL());
    }

    public function testEditSelectedObjectAnswersWithAFailureBoxWhenNoExistingEntryIsLeft(): void
    {
        $gui = $this->createGuiForEditModal(false, ['common#:#access' => 'A']);

        $this->editSelected($gui, ['forged#:#nonexistent']);

        self::assertCount(1, $this->rendered_async);
        self::assertInstanceOf(\ILIAS\UI\Component\MessageBox\MessageBox::class, $this->rendered_async[0]);
        self::assertSame('failure', $this->rendered_async[0]->getType());
        self::assertSame('no_checkbox', $this->rendered_async[0]->getMessageText());
        self::assertSame([], $_SESSION['lang_ext_edit_selection']);
    }

    public function testEditSelectedObjectResolvesAllObjectsAgainstTheFilteredEntries(): void
    {
        $gui = $this->createGuiForEditModal(
            false,
            ['common#:#access' => 'A', 'common#:#other' => 'B'],
            filtered_names: ['common#:#other', 'gone#:#entry']
        );
        $this->edit_table->expects($this->once())->method('getFilteredEntryNames');

        $this->editSelected($gui, ['ALL_OBJECTS']);

        self::assertSame(
            [['obj_id' => 0, 'names' => ['common#:#other']]],
            array_values($_SESSION['lang_ext_edit_selection'])
        );
        self::assertSame(['translation_0', 'expected_entry_count'], array_keys($this->renderedModal(0)->getInputs()));
    }

    public function testEditSelectedObjectKeepsOnlyTheTenNewestSelections(): void
    {
        $gui = $this->createGuiForEditModal(false, ['common#:#access' => 'A']);

        for ($i = 0; $i < 11; $i++) {
            $this->editSelected($gui, ['common#:#access']);
        }

        self::assertCount(10, $_SESSION['lang_ext_edit_selection']);
        $newest = array_key_last($_SESSION['lang_ext_edit_selection']);
        self::assertStringEndsWith($newest, $this->renderedModal(10)->getPostURL());
    }

    /**
     * @return array<string, array{0: bool, 1: int, 2: bool}> langmode, entry count, too many
     */
    public static function maxInputVarsProvider(): array
    {
        $limit = self::maxInputVarsLimit();

        return [
            'far above the limit' => [false, $limit + 10, true],
            'langmode needs two fields per entry' => [true, intdiv($limit, 2) + 1, true],
            'same count fits without langmode' => [false, intdiv($limit, 2) + 1, false],
        ];
    }

    #[DataProvider('maxInputVarsProvider')]
    public function testEditSelectedObjectRefusesSelectionsThatCannotBeSubmittedBack(
        bool $langmode,
        int $entry_count,
        bool $too_many
    ): void {
        $names = [];
        for ($i = 0; $i < $entry_count; $i++) {
            $names['common#:#entry' . $i] = 'v';
        }
        $gui = $this->createGuiForEditModal($langmode, $names);

        $this->editSelected($gui, array_keys($names));

        self::assertCount(1, $this->rendered_async);
        if ($too_many) {
            self::assertInstanceOf(\ILIAS\UI\Component\MessageBox\MessageBox::class, $this->rendered_async[0]);
            self::assertSame('language_too_many_entries_selected', $this->rendered_async[0]->getMessageText());
            self::assertSame([], $_SESSION['lang_ext_edit_selection']);
        } else {
            self::assertInstanceOf(\ILIAS\UI\Component\Modal\RoundTrip::class, $this->rendered_async[0]);
        }
    }

    public function testBuildEditEntriesModalShowsTheEscapedCompareValueAsBylineOnlyWhenThereIsOne(): void
    {
        $gui = $this->createGuiForEditModal(
            false,
            ['common#:#access' => 'A', 'common#:#empty' => 'B', 'common#:#missing' => 'C', 'common#:#lines' => 'D'],
            [],
            [
                'common#:#access' => '<b>Zugang</b> & "Co"',
                'common#:#empty' => '',
                'common#:#lines' => "one<br />two<br/>three<br>four",
            ]
        );

        $inputs = $this->inputsOf(
            $this->buildModal($gui, ['common#:#access', 'common#:#empty', 'common#:#missing', 'common#:#lines'])
        );

        self::assertSame(
            'English: &lt;b&gt;Zugang&lt;/b&gt; &amp; &quot;Co&quot;',
            $inputs['translation_0']->getByline()
        );
        self::assertSame('common / access', $inputs['translation_0']->getLabel());
        self::assertNull($inputs['translation_1']->getByline());
        self::assertNull($inputs['translation_2']->getByline());
        self::assertSame('English: one two three four', $inputs['translation_3']->getByline());
    }

    public function testBuildEditEntriesModalLoadsNoCompareValuesWithoutBylines(): void
    {
        $gui = $this->createGuiForEditModal(false, ['common#:#access' => 'A'], [], ['common#:#access' => 'Zugang']);
        $this->edit_table->expects($this->never())->method('getCompareValues');
        $this->edit_table->expects($this->never())->method('getCompareTitle');

        $inputs = $this->inputsOf($this->buildModal($gui, ['common#:#access'], 'tok', false));

        self::assertNull($inputs['translation_0']->getByline());
    }

    public function testBuildEditEntriesModalHasCommentFieldsOnlyInLangmodeAndEndsWithTheEntryCount(): void
    {
        foreach ([true, false] as $langmode) {
            $gui = $this->createGuiForEditModal($langmode, ['common#:#access' => 'A'], ['common#:#access' => 'Remark']);

            $inputs = $this->inputsOf($this->buildModal($gui, ['common#:#access']));

            if ($langmode) {
                self::assertSame(['translation_0', 'comment_0', 'expected_entry_count'], array_keys($inputs));
                self::assertSame('Remark', $inputs['comment_0']->getValue());
            } else {
                self::assertSame(['translation_0', 'expected_entry_count'], array_keys($inputs));
            }
            self::assertSame('1', $inputs['expected_entry_count']->getValue());
        }
    }

    public function testBuildEditEntriesModalPrefillsTheRemarkAsOneLineOfAtMost250CharactersWithoutByteLimit(): void
    {
        $gui = $this->createGuiForEditModal(
            true,
            ['a#:#lines' => 'A', 'a#:#long' => 'B'],
            ["a#:#lines" => " first\r\nsecond\nthird ", 'a#:#long' => str_repeat('ж', 300)]
        );

        $inputs = $this->inputsOf($this->buildModal($gui, ['a#:#lines', 'a#:#long']));

        self::assertInstanceOf(\ILIAS\UI\Component\Input\Field\Text::class, $inputs['comment_0']);
        self::assertNull($inputs['comment_0']->getMaxLength());
        self::assertSame('first second third', $inputs['comment_0']->getValue());
        self::assertSame(str_repeat('ж', 250), $inputs['comment_1']->getValue());
    }

    public function testTheTranslationsAreLoadedOnlyOncePerGui(): void
    {
        $gui = $this->createGuiForEditModal(false, ['common#:#access' => 'A']);
        $object = (new ReflectionProperty($gui, 'object'))->getValue($gui);
        $object->expects($this->once())->method('getAllValues');

        $this->editSelected($gui, ['common#:#access']);
        $this->buildModal($gui, ['common#:#access']);
    }

    /**
     * Outside langmode the form has no comment field: the existing remark is written back unchanged.
     */
    public function testSaveEditedEntriesObjectKeepsTheExistingRemarksOutsideLangmode(): void
    {
        $restore = $this->stubSaveValuesStatics();
        try {
            [$gui, $replaced] = $this->createGuiForSave(
                false,
                ['zztest#:#access' => 'Old'],
                ['t' => ['zztest#:#access']],
                't',
                ['translation_0' => 'New', 'expected_entry_count' => '1'],
                ['zztest#:#access' => 'Keep me']
            );
            $this->edit_table->expects($this->never())->method('getCompareValues');
            $this->edit_ctrl->expects($this->once())->method('redirect')->with($gui, 'view');
            $this->edit_tpl->expects($this->once())->method('setOnScreenMessage')
                ->with('success', 'language_variables_saved', true);

            $gui->saveEditedEntriesObject();

            self::assertSame(['zztest#:#access'], array_keys($replaced->getArrayCopy()));
            self::assertSame('New', $replaced['zztest#:#access']['value'][1]);
            self::assertSame('Keep me', $replaced['zztest#:#access']['remarks'][1]);
        } finally {
            $restore();
        }
    }

    public function testSaveEditedEntriesObjectSavesOnlyTheSelectionOfTheGivenToken(): void
    {
        $restore = $this->stubSaveValuesStatics();
        try {
            [$gui, $replaced] = $this->createGuiForSave(
                false,
                ['a#:#one' => 'Old1', 'a#:#two' => 'Old2'],
                ['A' => ['a#:#one'], 'B' => ['a#:#two']],
                'A',
                ['translation_0' => 'New', 'expected_entry_count' => '1']
            );

            $gui->saveEditedEntriesObject();

            self::assertSame(['a#:#one'], array_keys($replaced->getArrayCopy()));
        } finally {
            $restore();
        }
    }

    /**
     * @return array<string, array{0: string, 1: string}> posted remark, remark reaching the database
     */
    public static function remarkProvider(): array
    {
        return [
            'plain' => ['Remark', 'Remark'],
            'line break becomes a space' => ["first\nsecond", 'first second'],
            '130 multibyte characters (260 bytes) are kept' => [str_repeat('ж', 130), str_repeat('ж', 130)],
            '300 multibyte characters are cut to 250' => [str_repeat('ж', 300), str_repeat('ж', 250)],
        ];
    }

    #[DataProvider('remarkProvider')]
    public function testSaveEditedEntriesObjectTakesTheNormalizedRemarkFromTheFormInLangmode(
        string $posted,
        string $expected
    ): void {
        $restore = $this->stubSaveValuesStatics();
        try {
            [$gui, $replaced] = $this->createGuiForSave(
                true,
                ['zztest#:#access' => 'Old'],
                ['t' => ['zztest#:#access']],
                't',
                ['translation_0' => 'New', 'comment_0' => $posted, 'expected_entry_count' => '1'],
                ['zztest#:#access' => 'Old remark']
            );

            $gui->saveEditedEntriesObject();

            self::assertSame($expected, $replaced['zztest#:#access']['remarks'][1]);
        } finally {
            $restore();
        }
    }

    /**
     * @return array<string, array{0: ?string}> token of the request
     */
    public static function unusableTokenProvider(): array
    {
        return [
            'no token' => [null],
            'unknown token' => ['other'],
        ];
    }

    #[DataProvider('unusableTokenProvider')]
    public function testSaveEditedEntriesObjectRefusesARequestWithoutAStoredSelection(?string $token): void
    {
        [$gui] = $this->createGuiForSave(
            false,
            ['zztest#:#access' => 'A'],
            ['t' => ['zztest#:#access']],
            $token,
            ['translation_0' => 'X', 'expected_entry_count' => '1'],
            modal_names: ['zztest#:#access']
        );
        $this->expectSaveRefused($gui, 'action_aborted');

        $gui->saveEditedEntriesObject();
    }

    public function testSaveEditedEntriesObjectRefusesASelectionWhoseEntryWasDeletedMeanwhile(): void
    {
        [$gui] = $this->createGuiForSave(
            false,
            ['a#:#one' => 'A'],
            ['t' => ['a#:#gone', 'a#:#one']],
            't',
            ['translation_0' => 'X', 'expected_entry_count' => '1'],
            modal_names: ['a#:#one']
        );
        $this->expectSaveRefused($gui, 'msg_form_save_error');

        $gui->saveEditedEntriesObject();
    }

    /**
     * @return array<string, array{0: mixed}> stored selection that does not belong to the edited language
     */
    public static function foreignSelectionProvider(): array
    {
        return [
            'selection of another language' => [['obj_id' => 99, 'names' => ['zztest#:#access']]],
            'selection without obj_id' => [['names' => ['zztest#:#access']]],
            'old format, bare list of names' => [['zztest#:#access']],
        ];
    }

    #[DataProvider('foreignSelectionProvider')]
    public function testSaveEditedEntriesObjectRefusesASelectionStoredForAnotherLanguage(mixed $stored): void
    {
        [$gui] = $this->createGuiForSave(
            false,
            ['zztest#:#access' => 'A'],
            ['t' => ['zztest#:#access']],
            't',
            ['translation_0' => 'X', 'expected_entry_count' => '1']
        );
        $_SESSION['lang_ext_edit_selection']['t'] = $stored;
        $this->expectSaveRefused($gui, 'action_aborted');

        $gui->saveEditedEntriesObject();
    }

    public function testSaveEditedEntriesObjectRefusesASelectionWhoseEntriesAreAllGone(): void
    {
        [$gui] = $this->createGuiForSave(
            false,
            ['a#:#other' => 'A'],
            ['t' => ['a#:#gone']],
            't',
            [],
            modal_names: []
        );
        $this->expectSaveRefused($gui, 'msg_form_save_error');

        $gui->saveEditedEntriesObject();
    }

    public function testSaveEditedEntriesObjectRemovesOnlyTheUsedSelectionFromTheSessionOnSuccess(): void
    {
        $restore = $this->stubSaveValuesStatics();
        try {
            [$gui] = $this->createGuiForSave(
                false,
                ['a#:#one' => 'Old1', 'a#:#two' => 'Old2'],
                ['A' => ['a#:#one'], 'B' => ['a#:#two']],
                'A',
                ['translation_0' => 'New', 'expected_entry_count' => '1']
            );

            $gui->saveEditedEntriesObject();

            self::assertSame(['B'], array_keys($_SESSION['lang_ext_edit_selection']));
        } finally {
            $restore();
        }
    }

    public function testSaveEditedEntriesObjectKeepsTheSelectionInTheSessionWhenTheSaveIsRefused(): void
    {
        [$gui] = $this->createGuiForSave(
            false,
            ['a#:#one' => 'A', 'a#:#two' => 'B'],
            ['t' => ['a#:#one', 'a#:#two']],
            't',
            ['translation_0' => 'X', 'translation_1' => 'Y', 'expected_entry_count' => '3']
        );

        $gui->saveEditedEntriesObject();

        self::assertSame(['t'], array_keys($_SESSION['lang_ext_edit_selection']));
    }

    /**
     * @return array<string, array{0: string, 1: string}> posted translation, stored value
     */
    public static function postedMarkupProvider(): array
    {
        return [
            'allowed tags are kept' => ['<b>x</b> <i>y</i>', '<b>x</b> <i>y</i>'],
            'line break becomes br' => ["one\ntwo", 'one<br />two'],
            'carriage return and line feed become one br' => ["one\r\ntwo", 'one<br />two'],
            'double less-than becomes a guillemet' => ['a << b', 'a « b'],
        ];
    }

    #[DataProvider('postedMarkupProvider')]
    public function testSaveEditedEntriesObjectKeepsAllowedMarkupAndLineBreaksOfTheTranslation(
        string $posted,
        string $expected
    ): void {
        $restore = $this->stubSaveValuesStatics();
        try {
            [$gui, $replaced] = $this->createGuiForSave(
                false,
                ['zztest#:#access' => 'Old'],
                ['t' => ['zztest#:#access']],
                't',
                ['translation_0' => $posted, 'expected_entry_count' => '1']
            );

            $gui->saveEditedEntriesObject();

            self::assertSame($expected, $replaced['zztest#:#access']['value'][1]);
        } finally {
            $restore();
        }
    }

    /**
     * @return array<string, array{0: string}> posted translation
     */
    public static function disallowedMarkupProvider(): array
    {
        return [
            'script' => ['a<script>alert(1)</script>b'],
            'tag outside the allowlist' => ['<table>t</table><b>x</b>'],
        ];
    }

    /** Nothing is saved, the failure names the key (escaped) and the edit modal is shown again with a fresh selection. */
    #[DataProvider('disallowedMarkupProvider')]
    public function testSaveEditedEntriesObjectRejectsDisallowedMarkupAndSavesNothing(string $posted): void
    {
        $restore = $this->stubSaveValuesStatics();
        try {
            [$gui] = $this->createGuiForSave(
                false,
                ['zztest#:#access' => 'Old'],
                ['t' => ['zztest#:#access']],
                't',
                ['translation_0' => $posted, 'expected_entry_count' => '1']
            );
            // the view shown again builds the missing entries with the global ilCtrl
            $GLOBALS['DIC']['ilCtrl'] = $this->edit_ctrl;
            $this->edit_db->expects($this->never())->method('replace');
            $this->edit_db->expects($this->never())->method('insert');
            $this->edit_ctrl->expects($this->never())->method('redirect');
            $this->edit_tpl->expects($this->once())->method('setOnScreenMessage')->with(
                'failure',
                $this->callback(static fn(string $message): bool => str_contains($message, 'zztest#:#access')
                    && !str_contains($message, '<script>'))
            );

            $gui->saveEditedEntriesObject();

            $stored = $_SESSION['lang_ext_edit_selection'];
            self::assertNotContains('t', array_keys($stored));
            self::assertCount(1, $stored);
            self::assertSame(['obj_id' => 0, 'names' => ['zztest#:#access']], reset($stored));
        } finally {
            $restore();
        }
    }

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function arrayInsteadOfTextProvider(): array
    {
        return [
            'translation as array' => [['translation_0' => ['x'], 'expected_entry_count' => '1']],
            'comment as array' => [['translation_0' => 'New', 'comment_0' => ['y'], 'expected_entry_count' => '1']],
        ];
    }

    /** @param array<string, mixed> $post */
    #[DataProvider('arrayInsteadOfTextProvider')]
    public function testSaveEditedEntriesObjectRefusesAValueOrCommentSentAsArrayWithoutWarnings(array $post): void
    {
        [$gui] = $this->createGuiForSave(
            true,
            ['zztest#:#access' => 'Old'],
            ['t' => ['zztest#:#access']],
            't',
            $post
        );
        $this->expectSaveRefused($gui, 'msg_form_save_error');
        $warnings = [];
        set_error_handler(static function (int $number, string $message) use (&$warnings): bool {
            $warnings[] = $message;
            return true;
        });
        try {
            $gui->saveEditedEntriesObject();
        } finally {
            restore_error_handler();
        }

        self::assertSame([], $warnings);
    }

    public function testSaveEditedEntriesObjectStoresTheMarkupOfTheCommentUnfiltered(): void
    {
        $restore = $this->stubSaveValuesStatics();
        try {
            [$gui, $replaced] = $this->createGuiForSave(
                true,
                ['zztest#:#access' => 'Old'],
                ['t' => ['zztest#:#access']],
                't',
                ['translation_0' => 'New', 'comment_0' => '<b>why</b> & <script>', 'expected_entry_count' => '1']
            );

            $gui->saveEditedEntriesObject();

            self::assertSame('<b>why</b> & <script>', $replaced['zztest#:#access']['remarks'][1]);
        } finally {
            $restore();
        }
    }

    public function testBuildEditEntriesModalEscapesTheLabelOfTheTranslationField(): void
    {
        $gui = $this->createGuiForEditModal(false, ['<b>m#:#"t"' => 'A']);

        $inputs = $this->inputsOf($this->buildModal($gui, ['<b>m#:#"t"']));

        self::assertSame('&lt;b&gt;m / &quot;t&quot;', $inputs['translation_0']->getLabel());
    }

    public function testTheModuleFilterEscapesTheOptionTextsButKeepsTheKeysRaw(): void
    {
        $gui = $this->createGuiForEditModal(false, [], modules: ['a<b', 'administration']);

        $filter = $this->invokeProtectedMethod($gui, 'initFilter');

        $options = $filter->getInputs()['module']->getOptions();
        self::assertSame('a&lt;b', $options['a<b']);
        self::assertSame('administration', $options['administration']);
        self::assertSame(ilLanguageEntriesTable::DEFAULT_MODULE, $filter->getInputs()['module']->getValue());
    }

    /**
     * @return array<string, array{0: string}> stored remark
     */
    public static function remarkWithAmpersandProvider(): array
    {
        return [
            'encoded entity' => ['Tom &amp; Jerry'],
            'plain ampersand' => ['Tom & Jerry'],
            'encoded tag' => ['&lt;b&gt;'],
        ];
    }

    /** The comment field must show the stored remark unchanged, so saving it back does not alter it. */
    #[DataProvider('remarkWithAmpersandProvider')]
    public function testTheCommentFieldShowsARemarkWithAmpersandsUnchanged(string $remark): void
    {
        $gui = $this->createGuiForEditModal(true, ['common#:#access' => 'A'], ['common#:#access' => $remark]);

        $value = $this->inputsOf($this->buildModal($gui, ['common#:#access']))['comment_0']->getValue();

        // what the browser shows of KS' escaping (htmlspecialchars without double encoding)
        $shown = html_entity_decode(htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        self::assertSame($remark, $shown);
    }

    public function testTheModuleFilterPresetsAllModulesIfTheLanguageLacksTheDefaultModule(): void
    {
        $gui = $this->createGuiForEditModal(false, [], modules: ['a<b', 'common']);
        $this->edit_table->expects($this->once())->method('setDefaultModule')->with('all');

        $filter = $this->invokeProtectedMethod($gui, 'initFilter');

        self::assertSame('all', $filter->getInputs()['module']->getValue());
    }

    public function testSaveEditedEntriesObjectRefusesAnEntryCountThatDiffersFromTheSelection(): void
    {
        [$gui] = $this->createGuiForSave(
            false,
            ['a#:#one' => 'A', 'a#:#two' => 'B'],
            ['t' => ['a#:#one', 'a#:#two']],
            't',
            ['translation_0' => 'X', 'translation_1' => 'Y', 'expected_entry_count' => '3']
        );
        $this->expectSaveRefused($gui, 'msg_form_save_error');

        $gui->saveEditedEntriesObject();
    }

    /**
     * @return array<string, array{0: list<string>}> field keys that survive a POST cut off by max_input_vars
     */
    public static function truncatedPostProvider(): array
    {
        return [
            'only the count is lost' => [['translation_0', 'translation_1']],
            'tail of the entries is lost too' => [['translation_0']],
        ];
    }

    /**
     * @param list<string> $surviving_fields
     */
    #[DataProvider('truncatedPostProvider')]
    public function testSaveEditedEntriesObjectRefusesATruncatedPost(array $surviving_fields): void
    {
        [$gui] = $this->createGuiForSave(
            false,
            ['a#:#one' => 'A', 'a#:#two' => 'B'],
            ['t' => ['a#:#one', 'a#:#two']],
            't',
            array_fill_keys($surviving_fields, 'X')
        );
        $this->expectSaveRefused($gui, 'msg_form_save_error');

        $gui->saveEditedEntriesObject();
    }

    // -----------------------------------------------------------------
    // executeCommand()
    //
    // ilObjLanguageAccess::_checkMaintenance() (the method's very first
    // statement) is a static call that itself reaches into $DIC->user(),
    // $DIC->rbac()->system() and, once a user id is present,
    // $DIC->database() (via _lookupLangFolderRefId()) - all real DIC
    // services, stubbed here (via $GLOBALS['DIC']) purely so that call
    // returns true and execution proceeds; none of *this* class' own logic
    // is inside _checkMaintenance() itself; ilObjLanguageAccessTest is the
    // right place for that method's own behaviour.
    // -----------------------------------------------------------------

    /**
     * @return ilHelpGUI&MockObject the "ilHelp" collaborator, so a test can
     *         still add its own expectation on setScreenIdComponent()
     */
    private function stubMaintenanceGranted(): ilHelpGUI
    {
        $GLOBALS['DIC'] = new \ILIAS\DI\Container();

        $user = $this->createMock(ilObjUser::class);
        $user->method('getId')->willReturn(6);
        $GLOBALS['DIC']['ilUser'] = $user;

        // _checkMaintenance() -> _lookupLangFolderRefId() runs one query,
        // then rbacsystem->checkAccess() is asked - both stubbed to grant
        // access unconditionally, since none of executeCommand()'s own
        // behaviour depends on which ref_id/permission was actually used.
        $db = $this->createMock(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(static fn($value, string $type): string => "'" . $value . "'");
        $db->method('query')->willReturn($this->createMock(ilDBStatement::class));
        $db->method('fetchAssoc')->willReturn(['ref_id' => '5']);
        $GLOBALS['DIC']['ilDB'] = $db;

        $rbacsystem = $this->createMock(ilRbacSystem::class);
        $rbacsystem->method('checkAccess')->willReturn(true);
        $GLOBALS['DIC']['rbacsystem'] = $rbacsystem;

        $GLOBALS['DIC']['ilSetting'] = $this->createMock(ilSetting::class);

        $help = $this->createMock(ilHelpGUI::class);
        $GLOBALS['DIC']['ilHelp'] = $help;

        return $help;
    }

    /**
     * @param list<string> $row_ids
     * @param list<non-empty-string> $mocked_methods
     * @return ilObjLanguageExtGUI&MockObject
     */
    private function createGuiForExecuteCommand(
        bool $has_action_token,
        ?string $action_value,
        array $row_ids,
        ilCtrl $ctrl,
        array $mocked_methods = ['editSelectedObject', 'viewObject']
    ): ilObjLanguageExtGUI {
        $gui = $this->getMockBuilder(ilObjLanguageExtGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods($mocked_methods)
            ->getMock();

        $action_token = $this->createMock(URLBuilderToken::class);
        $action_token->method('getName')->willReturn('table_action');
        $row_id_token = $this->createMock(URLBuilderToken::class);
        $row_id_token->method('getName')->willReturn('entry_names');

        $request_wrapper = $this->createMock(RequestWrapper::class);
        $request_wrapper->method('has')->willReturnCallback(
            static fn(string $name): bool => match ($name) {
                'table_action' => $has_action_token,
                'entry_names' => $row_ids !== [],
                default => false,
            }
        );
        $request_wrapper->method('retrieve')->willReturnCallback(
            static fn(string $name, $trafo) => match ($name) {
                'table_action' => $action_value,
                'entry_names' => $row_ids,
                default => null,
            }
        );

        // A real (not mocked) Refinery Factory: getCommandFromQueryToken()/
        // getIdsFromQueryToken() only use it to build the Transformation
        // objects handed to $request_wrapper->retrieve() above, which
        // ignores them and returns the canned value directly - kindlyTo()/
        // custom() need no further collaborators to construct those.
        $refinery = new RefineryFactory(
            new \ILIAS\Data\Factory(),
            $this->createMock(\ILIAS\Language\Language::class)
        );

        $this->inject($gui, 'request_wrapper', $request_wrapper);
        $this->inject($gui, 'refinery', $refinery);
        $this->inject($gui, 'ctrl', $ctrl);
        $this->inject($gui, 'action_token', $action_token);
        $this->inject($gui, 'row_id_token', $row_id_token);

        return $gui;
    }

    /**
     * Regression test for the "no fallthrough" contract documented on
     * executeCommand(): once the 'edit' action is dispatched via the query
     * token, the method must return immediately - never reach the
     * `$cmd = ...; $this->$cmd();` fallthrough below (which would call
     * viewObject() and overwrite editSelectedObject()'s content/response).
     * If the `return` after the case's body were ever removed/moved, this
     * test's `expects($this->never())->method('viewObject')` would fail.
     */
    public function testExecuteCommandDispatchesEditActionAndNeverFallsThroughToViewObject(): void
    {
        $help = $this->stubMaintenanceGranted();
        $help->expects($this->once())->method('setScreenIdComponent')->with('lng');

        $ctrl = $this->createMock(ilCtrl::class);
        // If the dispatch ever fell through, $ctrl->getCmd() would be asked
        // for the default command - not expected here at all.
        $ctrl->expects($this->never())->method('getCmd');

        $gui = $this->createGuiForExecuteCommand(
            has_action_token: true,
            action_value: 'edit',
            row_ids: ['ALL_OBJECTS'],
            ctrl: $ctrl
        );
        $gui->expects($this->once())->method('editSelectedObject')->with(['ALL_OBJECTS']);
        $gui->expects($this->never())->method('viewObject');

        $gui->executeCommand();
    }

    /**
     * Control case: with no 'table_action' query token present at all, the
     * normal ilCtrl-driven dispatch (here: the default "view" command) must
     * still run exactly as before - the new query-token dispatch must not
     * interfere with the untouched fallthrough path.
     */
    public function testExecuteCommandFallsThroughToTheNormalDispatchWhenNoActionTokenIsPresent(): void
    {
        $help = $this->stubMaintenanceGranted();
        $help->expects($this->once())->method('setScreenIdComponent')->with('lng');

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->atLeastOnce())->method('getCmd')->with('view')->willReturn('view');

        $gui = $this->createGuiForExecuteCommand(
            has_action_token: false,
            action_value: null,
            row_ids: [],
            ctrl: $ctrl
        );
        $gui->expects($this->never())->method('editSelectedObject');
        $gui->expects($this->once())->method('viewObject');

        $gui->executeCommand();
    }

    public function testTheGuiIsSecuredAgainstCsrfForTheStateChangingCommandsOnly(): void
    {
        $gui = (new ReflectionClass(ilObjLanguageExtGUI::class))->newInstanceWithoutConstructor();
        $this->assertInstanceOf(ilCtrlSecurityInterface::class, $gui);

        $this->assertEqualsCanonicalizing(
            ['saveEditedEntries', 'upload', 'maintainExecute', 'saveSettings', 'saveNewEntry'],
            $gui->getUnsafeGetCommands()
        );
        $this->assertSame([], $gui->getSafePostCommands());
    }

    /**
     * @return array<string, array{0: string}> commands that are no exact spelling of a dispatchable command
     */
    public static function commandsThatMustFallBackToViewProvider(): array
    {
        return [
            'unknown' => ['doesNotExist'],
            'wrong case of an unsafe command' => ['SaveSettings'],
            'upper case' => ['SAVESETTINGS'],
            'inherited command' => ['save'],
            'inherited command with delete' => ['delete'],
            'already suffixed' => ['saveSettingsObject'],
            'empty' => [''],
        ];
    }

    #[DataProvider('commandsThatMustFallBackToViewProvider')]
    public function testExecuteCommandShowsTheViewForCommandsOutsideTheExactAllowlist(string $cmd): void
    {
        $this->stubMaintenanceGranted();
        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->method('getCmd')->willReturn($cmd);
        $gui = $this->createGuiForExecuteCommand(
            false,
            null,
            [],
            $ctrl,
            ['viewObject', 'saveObject', 'deleteObject', 'saveSettingsObject']
        );
        $gui->expects($this->once())->method('viewObject');
        $gui->expects($this->never())->method('saveObject');
        $gui->expects($this->never())->method('deleteObject');
        $gui->expects($this->never())->method('saveSettingsObject');

        $gui->executeCommand();
    }

    public function testExecuteCommandCallsTheMethodOfAnAllowlistedCommand(): void
    {
        $this->stubMaintenanceGranted();
        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->method('getCmd')->willReturn('saveSettings');
        $gui = $this->createGuiForExecuteCommand(false, null, [], $ctrl, ['viewObject', 'saveSettingsObject']);
        $gui->expects($this->once())->method('saveSettingsObject');
        $gui->expects($this->never())->method('viewObject');

        $gui->executeCommand();
    }

    /**
     * @return array<string, array{0: string, 1: string}> command, tab
     */
    public static function tabOfCommandProvider(): array
    {
        return [
            'empty' => ['', 'edit'],
            'view' => ['view', 'edit'],
            'saveEditedEntries' => ['saveEditedEntries', 'edit'],
            'unknown' => ['doesNotExist', 'edit'],
            'import' => ['import', 'import'],
            'upload' => ['upload', 'import'],
            'export' => ['export', 'export'],
            'download' => ['download', 'export'],
            'maintain' => ['maintain', 'maintain'],
            'maintainExecute' => ['maintainExecute', 'maintain'],
            'settings' => ['settings', 'settings'],
            'saveSettings' => ['saveSettings', 'settings'],
            'statistics' => ['statistics', 'statistics'],
        ];
    }

    #[DataProvider('tabOfCommandProvider')]
    public function testGetAdminTabsActivatesTheTabOfTheCommand(string $cmd, string $expected_tab): void
    {
        $this->stubGlobalDicIsPageTranslation(false);
        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->method('getCmd')->willReturn($cmd);
        $GLOBALS['DIC']['ilCtrl'] = $ctrl;

        $tabs = $this->createMock(ilTabsGUI::class);
        $tabs->expects($this->once())->method('activateTab')->with($expected_tab);
        $gui = $this->getMockBuilder(ilObjLanguageExtGUI::class)->disableOriginalConstructor()->onlyMethods([])->getMock();
        $this->inject($gui, 'tabs_gui', $tabs);
        $this->inject($gui, 'ctrl', $this->createMock(ilCtrl::class));
        $this->inject($gui, 'lng', $this->createLanguageMockReturningTopicAsIs());
        $this->inject($gui, 'id', 7);

        $gui->getAdminTabs();
    }

    // -----------------------------------------------------------------
    // getIdsFromQueryToken() (private) - exercised indirectly through
    // executeCommand()'s query-token dispatch, exactly like the 'edit'
    // action tests above, but this time with a REAL RequestWrapper::retrieve()
    // stand-in that actually applies the given Transformation to a raw query
    // value (createGuiForExecuteCommand() above deliberately ignores the
    // transformation and returns its canned $row_ids as-is, which is not
    // suitable for testing the transformation itself). Regression coverage
    // for the reflected-XSS fix: whatever shape the raw
    // "lang_entries_entry_names[]" query value has, editSelectedObject()
    // must always receive a genuine list<string> - never a raw/untransformed
    // value, and never a TypeError.
    // -----------------------------------------------------------------

    /**
     * @return ilObjLanguageExtGUI&MockObject
     */
    private function createGuiForExecuteCommandExercisingRealIdsTransformation(mixed $raw_entry_names_value): ilObjLanguageExtGUI
    {
        $gui = $this->getMockBuilder(ilObjLanguageExtGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['editSelectedObject', 'viewObject'])
            ->getMock();

        $action_token = $this->createMock(URLBuilderToken::class);
        $action_token->method('getName')->willReturn('table_action');
        $row_id_token = $this->createMock(URLBuilderToken::class);
        $row_id_token->method('getName')->willReturn('entry_names');

        $request_wrapper = $this->createMock(RequestWrapper::class);
        $request_wrapper->method('has')->willReturn(true);
        // Unlike createGuiForExecuteCommand() above, this actually applies
        // the transformation handed in by getIdsFromQueryToken() to a raw
        // value under this test's control - only this way is the
        // byTrying([...]) shape-normalisation itself exercised, rather than
        // bypassed.
        $request_wrapper->method('retrieve')->willReturnCallback(
            static fn(string $name, $trafo) => match ($name) {
                'table_action' => 'edit',
                'entry_names' => $trafo->transform($raw_entry_names_value),
                default => null,
            }
        );

        $refinery = new RefineryFactory(
            new \ILIAS\Data\Factory(),
            $this->createMock(\ILIAS\Language\Language::class)
        );

        $this->inject($gui, 'request_wrapper', $request_wrapper);
        $this->inject($gui, 'refinery', $refinery);
        $this->inject($gui, 'ctrl', $this->createMock(ilCtrl::class));
        (new ReflectionClass(ilObjLanguageExtGUI::class))->getProperty('action_token')->setValue($gui, $action_token);
        (new ReflectionClass(ilObjLanguageExtGUI::class))->getProperty('row_id_token')->setValue($gui, $row_id_token);

        return $gui;
    }

    public function testGetIdsFromQueryTokenCastsEachElementOfAGenuineArrayToStringElementwise(): void
    {
        $this->stubMaintenanceGranted();

        $gui = $this->createGuiForExecuteCommandExercisingRealIdsTransformation(['a', 123]);
        $gui->expects($this->once())->method('editSelectedObject')->with(['a', '123']);

        $gui->executeCommand();
    }

    /**
     * A raw query value of "...&entry_names=foo" (a single string, no "[]")
     * must be wrapped into a single-element list rather than causing a
     * TypeError further down (editSelectedObject(array $names)).
     */
    public function testGetIdsFromQueryTokenWrapsABareStringRawValueIntoASingleElementList(): void
    {
        $this->stubMaintenanceGranted();

        $gui = $this->createGuiForExecuteCommandExercisingRealIdsTransformation('common#:#access');
        $gui->expects($this->once())->method('editSelectedObject')->with(['common#:#access']);

        $gui->executeCommand();
    }

    /**
     * Any raw shape that is neither a genuine array nor a string (e.g. an
     * int, from a malformed/forged query string) must fall back to an empty
     * list, not a TypeError or the untransformed raw value.
     */
    public function testGetIdsFromQueryTokenFallsBackToAnEmptyListForAnUnexpectedRawShape(): void
    {
        $this->stubMaintenanceGranted();

        $gui = $this->createGuiForExecuteCommandExercisingRealIdsTransformation(123);
        $gui->expects($this->once())->method('editSelectedObject')->with([]);

        $gui->executeCommand();
    }

    // -----------------------------------------------------------------
    // resolveFilterData()
    // -----------------------------------------------------------------

    public function testResolveFilterDataReturnsTheFiltersInputsWhenItIsActivated(): void
    {
        $inputs = ['mode' => $this->createMock(\ILIAS\UI\Component\Input\Input::class)];
        $filter = $this->createMock(\ILIAS\UI\Component\Input\Container\Filter\Filter::class);
        $filter->method('isActivated')->willReturn(true);
        $filter->expects($this->once())->method('getInputs')->willReturn($inputs);

        $gui = $this->getMockBuilder(ilObjLanguageExtGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        self::assertSame($inputs, $this->invokeProtectedMethod($gui, 'resolveFilterData', [$filter]));
    }

    /**
     * A deactivated Filter must resolve to `null` - NOT to
     * $filter->getInputs() (which, per the Filter\Standard implementation,
     * is not itself gated by isActivated() at all) - so that
     * ilLanguageEntriesTable::filterValue()'s "!is_array($filter_data)" guard
     * falls back to every field's default, exactly as if no filter had been
     * applied.
     */
    public function testResolveFilterDataReturnsNullWhenTheFilterIsDeactivatedAndNeverReadsItsInputs(): void
    {
        $filter = $this->createMock(\ILIAS\UI\Component\Input\Container\Filter\Filter::class);
        $filter->method('isActivated')->willReturn(false);
        $filter->expects($this->never())->method('getInputs');

        $gui = $this->getMockBuilder(ilObjLanguageExtGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        self::assertNull($this->invokeProtectedMethod($gui, 'resolveFilterData', [$filter]));
    }

    // -----------------------------------------------------------------
    // resetEntriesTableRangeIfRequested()
    //
    // ilObjLanguageAccess::_isPageTranslation() (the method's own first
    // check) is a static call reaching into `global $DIC->http()`/
    // `$DIC->refinery()` - stubbed here via $GLOBALS['DIC'], independently
    // of the GUI instance's OWN request_wrapper/refinery properties (used
    // afterwards to read the "reset_offset" query parameter itself) - same
    // two-DIC-levels distinction as ilLanguageEntriesTableTest's own
    // setIsPageTranslation() helper.
    // -----------------------------------------------------------------

    private function stubGlobalDicIsPageTranslation(bool $is_page_translation): void
    {
        $query_wrapper = $this->createMock(\ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper::class);
        if ($is_page_translation) {
            $query_wrapper->method('has')->willReturn(true);
            $query_wrapper->method('retrieve')->willReturnCallback(
                static fn(string $key): string => match ($key) {
                    'cmdClass' => 'ilobjlanguageextgui',
                    'view_mode' => 'translate',
                    default => '',
                }
            );
        } else {
            $query_wrapper->method('has')->willReturn(false);
        }

        $wrapper_factory = $this->createMock(\ILIAS\HTTP\Wrapper\WrapperFactory::class);
        $wrapper_factory->method('query')->willReturn($query_wrapper);

        $http = $this->createMock(\ILIAS\HTTP\Services::class);
        $http->method('wrapper')->willReturn($wrapper_factory);

        $GLOBALS['DIC'] = new \ILIAS\DI\Container();
        $GLOBALS['DIC']['http'] = $http;
        $GLOBALS['DIC']['refinery'] = new RefineryFactory(
            new \ILIAS\Data\Factory(),
            $this->createMock(\ILIAS\Language\Language::class)
        );
    }

    /**
     * @return ilObjLanguageExtGUI&MockObject
     */
    private function createGuiForResetRange(bool $reset_offset_present, bool $reset_offset_value): ilObjLanguageExtGUI
    {
        $gui = $this->getMockBuilder(ilObjLanguageExtGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $request_wrapper = $this->createMock(RequestWrapper::class);
        $request_wrapper->method('has')->willReturnCallback(
            static fn(string $name): bool => $name === 'reset_offset' && $reset_offset_present
        );
        $request_wrapper->method('retrieve')->willReturn($reset_offset_value);

        $this->inject($gui, 'request_wrapper', $request_wrapper);
        $this->inject($gui, 'refinery', new RefineryFactory(
            new \ILIAS\Data\Factory(),
            $this->createMock(\ILIAS\Language\Language::class)
        ));

        return $gui;
    }

    public function testResetEntriesTableRangeIfRequestedClearsTheTableSessionKeyInPageTranslationModeWhenResetOffsetIsTrue(): void
    {
        $this->stubGlobalDicIsPageTranslation(true);
        $session_key = \ILIAS\UI\Implementation\Component\Table\Data::STORAGE_ID_PREFIX . 'lang_ext_entries_trans';
        $_SESSION[$session_key] = ['some' => 'stale view-control state'];

        $gui = $this->createGuiForResetRange(reset_offset_present: true, reset_offset_value: true);
        $this->invokeProtectedMethod($gui, 'resetEntriesTableRangeIfRequested');

        self::assertArrayNotHasKey($session_key, $_SESSION);
    }

    public function testResetEntriesTableRangeIfRequestedLeavesTheSessionKeyUntouchedWhenResetOffsetIsAbsent(): void
    {
        $this->stubGlobalDicIsPageTranslation(true);
        $session_key = \ILIAS\UI\Implementation\Component\Table\Data::STORAGE_ID_PREFIX . 'lang_ext_entries_trans';
        $_SESSION[$session_key] = ['some' => 'state that must survive'];

        $gui = $this->createGuiForResetRange(reset_offset_present: false, reset_offset_value: false);
        $this->invokeProtectedMethod($gui, 'resetEntriesTableRangeIfRequested');

        self::assertSame(['some' => 'state that must survive'], $_SESSION[$session_key]);
    }

    /**
     * Outside page-translation mode, resetEntriesTableRangeIfRequested()
     * must return immediately without ever reading $this->request_wrapper -
     * deliberately left unset on this GUI instance, so a regression that
     * dropped the early return would fatal-error here (accessing an
     * uninitialized typed property) rather than silently passing.
     */
    public function testResetEntriesTableRangeIfRequestedDoesNothingOutsidePageTranslationModeEvenWithResetOffsetTrue(): void
    {
        $this->stubGlobalDicIsPageTranslation(false);

        $gui = $this->getMockBuilder(ilObjLanguageExtGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->invokeProtectedMethod($gui, 'resetEntriesTableRangeIfRequested');

        $this->addToAssertionCount(1); // reaching here without a fatal error IS the assertion
    }
}
