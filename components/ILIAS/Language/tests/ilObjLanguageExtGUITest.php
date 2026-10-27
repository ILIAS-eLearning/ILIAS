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

use ILIAS\Data\Result\Ok as ResultOk;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Language\Activities\AddLanguageEntry;
use ILIAS\Language\Activities\RemoveLocalLanguageChanges;
use ILIAS\Language\Activities\SetLanguageTranslationEnabled;
use ILIAS\KeyValueStorage\Services as KeyValueStorage;
use ILIAS\Language\Tests\InMemoryKeyValueStorage;
use ILIAS\Language\Tests\InMemoryKeyValueStore;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\URLBuilderToken;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

require_once __DIR__ . '/../../UI/tests/UITestHelper.php';
require_once __DIR__ . '/InMemoryKeyValueStore.php';
require_once __DIR__ . '/InMemoryKeyValueStorage.php';

class ilObjLanguageExtGUITest extends TestCase
{
    use RestoresProcessState;

    use UITestHelper;
    use ilObjLanguageExtGUITestFixture {
        setUp as private setUpGuiTestSupport;
        tearDown as private tearDownGuiTestSupport;
    }

    private InMemoryKeyValueStorage $key_value_storage;

    protected function setUp(): void
    {
        $this->setUpGuiTestSupport();
        $_SESSION = [];
        $this->key_value_storage = new InMemoryKeyValueStorage();
    }

    private function maintenanceState(): InMemoryKeyValueStore
    {
        return $this->key_value_storage->session(['language', 'ext_maintenance']);
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
            ['saveEditedEntries', 'upload', 'importConfirmed', 'maintainExecute', 'maintainConfirmed', 'saveSettings', 'saveNewEntry'],
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

    /** The list text is HTML-escaped and the "eid" parameter URL-encoded; the link is a KS link. */
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
        $ctrl->method('getLinkTarget')->willReturn('ilias.php?a=1');
        $renderer = $this->createStub(\ILIAS\UI\Renderer::class);
        $renderer->method('render')->willReturn('<a>link</a>');
        $gui = $this->newGuiWithoutConstructor(ilObjLanguageExtGUI::class);
        $this->inject($gui, 'ctrl', $ctrl);
        $this->inject($gui, 'lng', $this->createLanguageMockReturningTopicAsIs());
        $this->inject($gui, 'ui_factory', $this->factory());
        $this->inject($gui, 'ui_renderer', $renderer);
        $this->inject($gui, 'refinery', new \ILIAS\Refinery\Factory(
            $this->createStub(\ILIAS\Data\Factory::class),
            $this->createStub(\ILIAS\Language\Language::class)
        ));

        $panel = (new ReflectionMethod(ilObjLanguageExtGUI::class, 'buildMissingEntries'))->invoke($gui, [$topic]);

        $this->assertSame([['eid', $eid]], $parameters);
        $this->assertSame([$text . ' <a>link</a>'], $panel->getContent()[0]->getItems());
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
        $_SESSION['lang_ext_edit_selection'] = array_map(
            static fn(array $names): array => ['obj_id' => 0, 'names' => $names],
            $session_selections
        );
        $dic = $this->init();
        $dic[KeyValueStorage::class] = $this->key_value_storage;
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

    #[DataProvider('remarkWithAmpersandProvider')]
    public function testTheCommentFieldShowsARemarkWithAmpersandsUnchanged(string $remark): void
    {
        $gui = $this->createGuiForEditModal(true, ['common#:#access' => 'A'], ['common#:#access' => $remark]);

        $value = $this->inputsOf($this->buildModal($gui, ['common#:#access']))['comment_0']->getValue();

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


    /** @return ilHelpGUI&MockObject */
    private function stubMaintenanceGranted(bool $granted = true): ilHelpGUI
    {
        $GLOBALS['DIC'] = new \ILIAS\DI\Container();

        $user = $this->createMock(ilObjUser::class);
        $user->method('getId')->willReturn(6);
        $GLOBALS['DIC']['ilUser'] = $user;

        $db = $this->createMock(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(static fn($value, string $type): string => "'" . $value . "'");
        $db->method('query')->willReturn($this->createMock(ilDBStatement::class));
        $db->method('fetchAssoc')->willReturn(['ref_id' => '5']);
        $GLOBALS['DIC']['ilDB'] = $db;

        $rbacsystem = $this->createMock(ilRbacSystem::class);
        $rbacsystem->method('checkAccess')->willReturn($granted);
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

    public function testExecuteCommandDispatchesEditActionAndNeverFallsThroughToViewObject(): void
    {
        $help = $this->stubMaintenanceGranted();
        $help->expects($this->once())->method('setScreenIdComponent')->with('lng');

        $ctrl = $this->createMock(ilCtrl::class);
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

    public function testExecuteCommandForwardsTheImportUploadHandlerClassToTheHandlerAndStopsThere(): void
    {
        $help = $this->stubMaintenanceGranted();
        $help->expects($this->never())->method('setScreenIdComponent');

        $handler = $this->createMock(ilLanguageImportUploadHandlerGUI::class);
        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->method('getNextClass')->willReturn('ilLanguageImportUploadHandlerGUI');
        $ctrl->expects($this->once())->method('forwardCommand')->with($this->identicalTo($handler));
        $ctrl->expects($this->never())->method('getCmd');

        $gui = $this->createGuiForExecuteCommand(false, null, [], $ctrl);
        $this->inject($gui, 'import_upload_handler', $handler);
        $gui->expects($this->never())->method('editSelectedObject');
        $gui->expects($this->never())->method('viewObject');

        $gui->executeCommand();
    }

    public function testExecuteCommandDoesNotForwardToTheImportUploadHandlerWithoutMaintenanceRight(): void
    {
        $this->stubMaintenanceGranted(false);

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->method('getNextClass')->willReturn('ilLanguageImportUploadHandlerGUI');
        $ctrl->expects($this->never())->method('forwardCommand');

        $gui = $this->createGuiForExecuteCommand(false, null, [], $ctrl);
        $this->inject(
            $gui,
            'import_upload_handler',
            $this->createMock(ilLanguageImportUploadHandlerGUI::class)
        );
        $lng = $this->createMock(ilLanguage::class);
        $lng->method('txt')->willReturnArgument(0);
        $this->inject($gui, 'lng', $lng);
        $error = $this->createMock(ilErrorHandling::class);
        $error->expects($this->once())->method('raiseError')
            ->with('permission_denied')
            ->willThrowException(new RuntimeException('denied'));
        $this->inject($gui, 'error', $error);

        $this->expectExceptionMessage('denied');
        $gui->executeCommand();
    }

    public function testTheGuiIsSecuredAgainstCsrfForTheStateChangingCommandsOnly(): void
    {
        $gui = (new ReflectionClass(ilObjLanguageExtGUI::class))->newInstanceWithoutConstructor();
        $this->assertInstanceOf(ilCtrlSecurityInterface::class, $gui);

        $this->assertEqualsCanonicalizing(
            ['saveEditedEntries', 'upload', 'importConfirmed', 'maintainExecute', 'maintainConfirmed', 'saveSettings', 'saveNewEntry'],
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

    /** @return array<string, array{string, string}> request method and the method that must run */
    public static function requestMethodsOfAnUnsafeCommand(): array
    {
        return [
            'POST runs the command' => ['POST', 'saveSettingsObject'],
            'GET only shows its view' => ['GET', 'settingsObject'],
            'HEAD only shows its view' => ['HEAD', 'settingsObject'],
        ];
    }

    #[DataProvider('requestMethodsOfAnUnsafeCommand')]
    public function testExecuteCommandRunsAnAllowlistedUnsafeCommandOnlyByPost(string $method, string $expected): void
    {
        $this->stubMaintenanceGranted();
        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->method('getCmd')->willReturn('saveSettings');
        $gui = $this->createGuiForExecuteCommand(
            false,
            null,
            [],
            $ctrl,
            ['viewObject', 'saveSettingsObject', 'settingsObject']
        );
        $request = $this->createStub(\Psr\Http\Message\ServerRequestInterface::class);
        $request->method('getMethod')->willReturn($method);
        $http = $this->createStub(\ILIAS\HTTP\GlobalHttpState::class);
        $http->method('request')->willReturn($request);
        $this->inject($gui, 'http', $http);

        foreach (['saveSettingsObject', 'settingsObject'] as $candidate) {
            $gui->expects($candidate === $expected ? $this->once() : $this->never())->method($candidate);
        }
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
            'importConfirmed' => ['importConfirmed', 'import'],
            'export' => ['export', 'export'],
            'download' => ['download', 'export'],
            'maintain' => ['maintain', 'maintain'],
            'maintainExecute' => ['maintainExecute', 'maintain'],
            'maintainConfirmed' => ['maintainConfirmed', 'maintain'],
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

    public function testGetIdsFromQueryTokenWrapsABareStringRawValueIntoASingleElementList(): void
    {
        $this->stubMaintenanceGranted();

        $gui = $this->createGuiForExecuteCommandExercisingRealIdsTransformation('common#:#access');
        $gui->expects($this->once())->method('editSelectedObject')->with(['common#:#access']);

        $gui->executeCommand();
    }

    public function testGetIdsFromQueryTokenFallsBackToAnEmptyListForAnUnexpectedRawShape(): void
    {
        $this->stubMaintenanceGranted();

        $gui = $this->createGuiForExecuteCommandExercisingRealIdsTransformation(123);
        $gui->expects($this->once())->method('editSelectedObject')->with([]);

        $gui->executeCommand();
    }


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

    /** A deactivated Filter yields null, not $filter->getInputs(), which ignores isActivated(). */
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

    /** request_wrapper stays unset on purpose: a dropped early return fails with an uninitialized property. */
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


    private const string MAINTAIN_PENDING = 'lang_ext_maintain_pending';
    private const string MAINTAIN_TOKEN = 'lang_maintain_token';
    private const string IMPORT_PENDING = 'lang_ext_import_pending';
    private const string IMPORT_TOKEN = 'lang_import_token';

    /** @var list<string> files of the current test, removed in cleanUpScratchFiles() */
    private array $scratch_paths = [];

    /** @var array<string, string> values of the settings stub of createGuiForForms() */
    private array $settings_values = [];

    #[\PHPUnit\Framework\Attributes\After]
    public function cleanUpScratchFiles(): void
    {
        foreach (array_reverse($this->scratch_paths) as $path) {
            is_dir($path) ? @rmdir($path) : @unlink($path);
        }
        $this->scratch_paths = [];
    }

    private function scratchDir(): string
    {
        $dir = sys_get_temp_dir() . '/lng_gui_test_' . bin2hex(random_bytes(6));
        mkdir($dir);
        $this->scratch_paths[] = $dir;

        return $dir;
    }

    /**
     * @param array<string, mixed> $post
     * @param array<string, string> $query token parameters of the request
     * @param list<string> $installed_languages
     * @return array{0: ilObjLanguageExtGUI, 1: stdClass} the GUI and what it did (redirects, messages, contents, rendered, params, object, handler)
     */
    private function createGuiForForms(
        string $langmode = '1',
        array $post = [],
        array $query = [],
        ?string $upload_name = null,
        string $key = 'de',
        array $installed_languages = [],
        string $method = 'POST'
    ): array {
        $dic = $this->init();
        $settings = $this->createStub(ilSetting::class);
        $settings->method('get')->willReturnCallback(
            fn(string $name, ?string $default = null): ?string => $this->settings_values[$name] ?? $default
        );
        $dic['ilSetting'] = $settings;
        $dic[KeyValueStorage::class] = $this->key_value_storage;
        $GLOBALS['DIC'] = $dic;
        $ctx = new stdClass();
        $ctx->redirects = [];
        $ctx->messages = [];
        $ctx->contents = [];
        $ctx->rendered = [];
        $ctx->params = [];

        $ctrl = $this->createStub(ilCtrl::class);
        $ctrl->method('setParameter')->willReturnCallback(
            static function ($gui, string $name, $value) use ($ctx): void {
                $ctx->params[$name] = $value;
            }
        );
        $ctrl->method('clearParameterByClass')->willReturnCallback(
            static function (string $class, string $name) use ($ctx): void {
                unset($ctx->params[$name]);
            }
        );
        $ctrl->method('getFormAction')->willReturnCallback(
            static fn($gui, string $cmd): string => 'action:' . $cmd . '?' . http_build_query($ctx->params)
        );
        $ctrl->method('getLinkTarget')->willReturnCallback(
            static fn($gui, string $cmd): string => 'link:' . $cmd . '?' . http_build_query($ctx->params)
        );
        $ctrl->method('redirect')->willReturnCallback(
            static function ($gui, string $cmd) use ($ctx): void {
                $ctx->redirects[] = [$cmd, $ctx->params];
            }
        );

        $tpl = $this->createStub(ilGlobalTemplateInterface::class);
        $tpl->method('setOnScreenMessage')->willReturnCallback(
            static function (string $type, string $text) use ($ctx): void {
                $ctx->messages[] = [$type, $text];
            }
        );
        $tpl->method('setContent')->willReturnCallback(
            static function (string $content) use ($ctx): void {
                $ctx->contents[] = $content;
            }
        );

        $ctx->panel_titles = [];
        $renderer = $this->createStub(\ILIAS\UI\Renderer::class);
        $renderer->method('render')->willReturnCallback(
            static function ($components) use ($ctx): string {
                $flat = [];
                foreach (is_array($components) ? $components : [$components] as $component) {
                    if ($component instanceof \ILIAS\UI\Component\Panel\Standard) {
                        $ctx->panel_titles[] = $component->getTitle();
                        $content = $component->getContent();
                        array_push($flat, ...(is_array($content) ? $content : [$content]));
                    } else {
                        $flat[] = $component;
                    }
                }
                $ctx->rendered[] = $flat;
                return 'html';
            }
        );

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn($post);
        $request->method('getQueryParams')->willReturn([]);
        $request->method('getMethod')->willReturn($method);
        $http = $this->createStub(\ILIAS\HTTP\GlobalHttpState::class);
        $http->method('request')->willReturn($request);
        $wrapper_factory = $this->createStub(\ILIAS\HTTP\Wrapper\WrapperFactory::class);
        $wrapper_factory->method('query')->willReturn(new \ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper($query));
        $http->method('wrapper')->willReturn($wrapper_factory);

        $request_wrapper = $this->createStub(RequestWrapper::class);
        $request_wrapper->method('has')->willReturnCallback(static fn(string $name): bool => isset($query[$name]));
        $request_wrapper->method('retrieve')->willReturnCallback(static fn(string $name) => $query[$name] ?? null);

        $ctx->object = $this->createFakeLanguageObjectMock($key);

        $ctx->handler = new class () extends ilLanguageImportUploadHandlerGUI {
            /** @var array<string, string> identifier => file name of the uploads of this session */
            public array $files = [];
            /** @var list<string> */
            public array $discarded = [];
            /** @var list<list<string>> */
            public array $discard_all_calls = [];
            /** @var list<string> */
            public array $local_files = [];

            public function __construct()
            {
            }

            public function getFileName(string $identifier): ?string
            {
                return $this->files[$identifier] ?? null;
            }

            public function copyToLocalFile(string $identifier): ?string
            {
                if (!isset($this->files[$identifier])) {
                    return null;
                }
                $path = sys_get_temp_dir() . '/lng_gui_local_' . bin2hex(random_bytes(6));
                file_put_contents($path, 'local copy');

                return $this->local_files[] = $path;
            }

            public function discard(string $identifier): void
            {
                $this->discarded[] = $identifier;
            }

            public function discardAll(array $except = []): void
            {
                $this->discard_all_calls[] = $except;
            }
        };
        if ($upload_name !== null) {
            $ctx->handler->files['up1'] = $upload_name;
        }

        $gui = $this->getMockBuilder(ilObjLanguageExtGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        $this->inject($gui, 'object', $ctx->object);
        $this->inject($gui, 'lng', $this->createLanguageMockReturningTopicAsIs($installed_languages));
        $this->inject($gui, 'ctrl', $ctrl);
        $this->inject($gui, 'ui_factory', $this->factory());
        $this->inject($gui, 'ui_renderer', $renderer);
        $this->inject($gui, 'tpl', $tpl);
        $this->inject($gui, 'http', $http);
        $this->inject($gui, 'request_wrapper', $request_wrapper);
        $this->inject($gui, 'refinery', $dic['refinery']);
        $this->inject($gui, 'activity_error_logger', $this->activityErrorLoggerForGui());
        $declaring = new ReflectionClass(ilObjLanguageExtGUI::class);
        $declaring->getProperty('langmode')->setValue($gui, $langmode);
        $declaring->getProperty('import_upload_handler')->setValue($gui, $ctx->handler);
        $ctx->remove_local_language_changes = $this->createMock(RemoveLocalLanguageChanges::class);
        $this->inject($gui, 'remove_local_language_changes', $ctx->remove_local_language_changes);
        $user = $this->createStub(ilObjUser::class);
        $user->method('getId')->willReturn(6);
        $this->inject($gui, 'user', $user);

        return [$gui, $ctx];
    }

    /**
     * @param array<string, mixed> $values
     * @param list<mixed> $args arguments of the builder
     * @param list<string> $installed_languages
     * @return array<string, mixed>
     */
    private function postFor(string $builder, array $values, array $args = [], array $installed_languages = []): array
    {
        [$probe] = $this->createGuiForForms(installed_languages: $installed_languages);
        $body = [];
        foreach ($this->invokeProtectedMethod($probe, $builder, $args)->getInputs() as $key => $input) {
            if (!array_key_exists($key, $values)) {
                continue;
            }
            if ($input instanceof \ILIAS\UI\Implementation\Component\Input\Field\File) {
                $template = $input->getTemplateForDynamicInputs();
                self::assertInstanceOf(\ILIAS\UI\Implementation\Component\Input\Field\FormInput::class, $template);
                preg_match('/\[([^\]]+)\]\[\]$/', $template->getName(), $inner);
                $body[$input->getName()] = [$inner[1] => $values[$key]];
            } else {
                $body[$input->getName()] = $values[$key];
            }
        }

        return $body;
    }

    /**
     * @param array<string, string> $query
     * @return array{0: ilObjLanguageExtGUI, 1: stdClass}
     */
    private function maintainGui(string $option, string $langmode = '1', array $query = []): array
    {
        return $this->createGuiForForms(
            $langmode,
            $this->postFor('buildMaintainForm', ['maintain' => $option]),
            $query
        );
    }

    private function givenDistributedLanguageFile(ilObjLanguageExt&MockObject $object): string
    {
        $dir = $this->scratchDir();
        $file = $dir . '/ilias_de.lang';
        file_put_contents($file, 'dist');
        $this->scratch_paths[] = $file;
        $object->method('getLangPath')->willReturn($dir);

        return $file;
    }

    /** @param array<string, mixed> $data */
    /** @param bool $show true: stored right before, the page shows the confirmation once */
    private function givenPending(
        string $session_key,
        array $data,
        string $token = 'tok',
        int $obj_id = 0,
        bool $show = false
    ): void {
        $_SESSION[$session_key] = ['token' => $token, 'obj_id' => $obj_id, 'show' => $show] + $data;
    }

    #[DataProvider('destructiveMaintainOptions')]
    public function testMaintainExecuteOnlyStoresADestructiveOptionAndRedirectsWithoutItsToken(
        string $option,
        string $langmode
    ): void {
        [$gui, $ctx] = $this->maintainGui($option, $langmode);
        $ctx->object->expects($this->never())->method('importLanguageFile');
        $ctx->object->expects($this->never())->method('setLocal');

        $gui->maintainExecuteObject();

        $pending = $_SESSION[self::MAINTAIN_PENDING];
        self::assertSame($option, $pending['option']);
        self::assertSame(0, $pending['obj_id']);
        self::assertMatchesRegularExpression('/^[0-9a-f]{16}$/', $pending['token']);
        self::assertTrue($pending['show']);
        self::assertSame([['maintain', []]], $ctx->redirects);
        self::assertSame([], $ctx->messages);
    }

    /** @return array<string, array{string, string}> */
    public static function destructiveMaintainOptions(): array
    {
        return [
            'load' => ['load', '0'],
            'clear' => ['clear', '0'],
            'delete_added' => ['delete_added', '1'],
            'remove_local_file' => ['remove_local_file', '1'],
            'merge' => ['merge', '1'],
        ];
    }

    public function testMaintainExecuteRunsSaveDistDirectlyWithoutVormerkung(): void
    {
        [$gui, $ctx] = $this->maintainGui('save_dist', '0');
        $source_dir = $this->scratchDir();
        $target_dir = $this->scratchDir();
        file_put_contents($source_dir . '/ilias_de.lang', 'dist');
        $this->scratch_paths[] = $source_dir . '/ilias_de.lang';
        $this->scratch_paths[] = $target_dir . '/ilias_de.lang';
        $ctx->object->method('getLangPath')->willReturn($source_dir);
        $ctx->object->method('getDataPath')->willReturn($target_dir);

        $gui->maintainExecuteObject();

        self::assertSame('dist', file_get_contents($target_dir . '/ilias_de.lang'));
        self::assertSame([['success', 'language_saved_dist']], $ctx->messages);
        self::assertArrayNotHasKey(self::MAINTAIN_PENDING, $_SESSION);
        self::assertSame([['maintain', []]], $ctx->redirects);
    }

    /** @return array<string, array{array<string, mixed>, string}> */
    public static function invalidMaintainSubmissions(): array
    {
        return [
            'unknown option' => [['maintain' => 'format_disk'], '1'],
            'langmode option without langmode' => [['maintain' => 'remove_local_file'], '0'],
            'merge without langmode' => [['maintain' => 'merge'], '0'],
            'nothing selected' => [[], '1'],
            'empty option' => [['maintain' => ''], '1'],
            'array instead of an option' => [['maintain' => ['clear']], '1'],
        ];
    }

    /** @param array<string, mixed> $values */
    #[DataProvider('invalidMaintainSubmissions')]
    public function testMaintainExecuteRefusesAnOptionThatIsNotOfferedAndRunsNothing(array $values, string $langmode): void
    {
        [$gui, $ctx] = $this->createGuiForForms(
            $langmode,
            $this->postFor('buildMaintainForm', $values)
        );
        $ctx->object->expects($this->never())->method('importLanguageFile');
        $ctx->object->expects($this->never())->method('setLocal');

        $gui->maintainExecuteObject();

        self::assertSame([['failure', 'form_input_not_valid']], $ctx->messages);
        self::assertArrayNotHasKey(self::MAINTAIN_PENDING, $_SESSION);
        self::assertSame([], $ctx->redirects);
        self::assertCount(1, $ctx->contents, 'the form is shown again');
    }

    public function testMaintainConfirmedRunsTheStoredOptionExactlyOnceAndClearsTheVormerkung(): void
    {
        [$gui, $ctx] = $this->createGuiForForms('0', [], [self::MAINTAIN_TOKEN => 'tok']);
        $this->givenPending(self::MAINTAIN_PENDING, ['option' => 'clear']);
        $ctx->remove_local_language_changes->expects($this->once())->method('maybePerformAs')
            ->with($this->anything(), 6, ['language_keys' => ['de']])
            ->willReturn(new ResultOk(['removed_local_changes_language_keys' => ['de']]));

        $gui->maintainConfirmedObject();
        self::assertArrayNotHasKey(self::MAINTAIN_PENDING, $_SESSION);
        self::assertSame([['success', 'language_cleared_local']], $ctx->messages);
        self::assertSame([['maintain', []]], $ctx->redirects);

        $gui->maintainConfirmedObject();

        self::assertSame([['success', 'language_cleared_local']], $ctx->messages);
    }

    /** @return array<string, array{?string, int, string}> token of the request, obj_id and option of the Vormerkung */
    public static function unusableMaintainConfirmations(): array
    {
        return [
            'wrong token' => ['other', 0, 'clear'],
            'missing token' => [null, 0, 'clear'],
            'foreign language' => ['tok', 99, 'clear'],
            'option not offered any more' => ['tok', 0, 'merge'],
        ];
    }

    #[DataProvider('unusableMaintainConfirmations')]
    public function testMaintainConfirmedAbortsWithoutRunningAnythingForAnUnusableVormerkung(
        ?string $token,
        int $obj_id,
        string $option
    ): void {
        [$gui, $ctx] = $this->createGuiForForms('0', [], $token === null ? [] : [self::MAINTAIN_TOKEN => $token]);
        $this->givenDistributedLanguageFile($ctx->object);
        $this->givenPending(self::MAINTAIN_PENDING, ['option' => $option], 'tok', $obj_id);
        $ctx->object->expects($this->never())->method('importLanguageFile');
        $ctx->object->expects($this->never())->method('setLocal');
        $ctx->remove_local_language_changes->expects($this->never())->method('maybePerformAs');

        $gui->maintainConfirmedObject();

        self::assertSame([['failure', 'action_aborted']], $ctx->messages);
        self::assertArrayNotHasKey(self::MAINTAIN_PENDING, $_SESSION);
        self::assertSame([['maintain', []]], $ctx->redirects);
    }

    public function testMaintainConfirmedNeverAcceptsAMissingTokenEvenForAVormerkungWithAnEmptyToken(): void
    {
        [$gui, $ctx] = $this->createGuiForForms('0');
        $this->givenPending(self::MAINTAIN_PENDING, ['option' => 'clear'], '');
        $ctx->object->expects($this->never())->method('importLanguageFile');

        $gui->maintainConfirmedObject();

        self::assertSame([['failure', 'action_aborted']], $ctx->messages);
    }

    public function testMaintainObjectShowsFormAndConfirmationModalOnceRightAfterTheVormerkung(): void
    {
        [$gui, $ctx] = $this->createGuiForForms('1');
        $this->givenPending(self::MAINTAIN_PENDING, ['option' => 'merge'], show: true);

        $gui->maintainObject();

        self::assertCount(1, $ctx->rendered);
        [$form, $modal] = $ctx->rendered[0];
        self::assertInstanceOf(\ILIAS\UI\Component\Input\Container\Form\Standard::class, $form);
        self::assertInstanceOf(\ILIAS\UI\Component\Modal\Interruptive::class, $modal);
        self::assertSame('action:maintainConfirmed?' . self::MAINTAIN_TOKEN . '=tok', $modal->getFormAction());
        self::assertStringContainsString('language_merge_local_changes', $modal->getMessage());
        self::assertSame(['0'], array_map(static fn($item) => $item->getId(), $modal->getAffectedItems()));
        self::assertSame('html', $ctx->contents[0]);
        self::assertArrayNotHasKey(self::MAINTAIN_TOKEN, $ctx->params);
        self::assertFalse($_SESSION[self::MAINTAIN_PENDING]['show'], 'the Vormerkung stays until it is confirmed');

        $gui->maintainObject();
        self::assertCount(1, $ctx->rendered[1]);
        self::assertArrayNotHasKey(self::MAINTAIN_PENDING, $_SESSION);
    }

    public function testMaintainObjectWithoutTheShowMarkerShowsOnlyTheFormAndDiscardsTheVormerkung(): void
    {
        foreach ([null, 'tok'] as $token) {
            [$gui, $ctx] = $this->createGuiForForms('1', [], $token === null ? [] : [self::MAINTAIN_TOKEN => $token]);
            $this->givenPending(self::MAINTAIN_PENDING, ['option' => 'merge']);

            $gui->maintainObject();

            self::assertCount(1, $ctx->rendered);
            self::assertCount(1, $ctx->rendered[0], (string) $token);
            self::assertArrayNotHasKey(self::MAINTAIN_PENDING, $_SESSION);
        }
    }

    /**
     * @param array<string, mixed> $values
     * @param array<string, string> $query
     * @return array{0: ilObjLanguageExtGUI, 1: stdClass}
     */
    private function importGui(array $values, array $query = [], string $upload_name = 'ilias_de.lang'): array
    {
        return $this->createGuiForForms(
            '1',
            $this->postFor('buildImportForm', $values),
            $query,
            $upload_name
        );
    }

    #[DataProvider('importModesWithoutConfirmation')]
    public function testUploadObjectImportsKeepModesDirectlyAndRemovesTheUpload(string $mode): void
    {
        [$gui, $ctx] = $this->importGui(['file' => ['up1'], 'mode' => $mode]);
        $ctx->object->expects($this->once())->method('importLanguageFile')
            ->with($this->callback(static fn(string $path): bool => is_file($path)), $mode);

        $gui->uploadObject();

        self::assertArrayNotHasKey(self::IMPORT_PENDING, $_SESSION);
        self::assertContains('up1', $ctx->handler->discarded);
        self::assertCount(1, $ctx->handler->local_files);
        self::assertFileDoesNotExist($ctx->handler->local_files[0]);
        self::assertSame([['success', 'language_file_imported']], $ctx->messages);
        self::assertSame([['import', []]], $ctx->redirects);
    }

    /** @return array<string, array{string}> */
    public static function importModesWithoutConfirmation(): array
    {
        return ['keepall' => ['keepall'], 'keepnew' => ['keepnew']];
    }

    #[DataProvider('importModesWithConfirmation')]
    public function testUploadObjectOnlyStoresDestructiveModesAndKeepsTheUploadForTheConfirmation(string $mode): void
    {
        [$gui, $ctx] = $this->importGui(['file' => ['up1'], 'mode' => $mode]);
        $ctx->object->expects($this->never())->method('importLanguageFile');

        $gui->uploadObject();

        $pending = $_SESSION[self::IMPORT_PENDING];
        self::assertSame($mode, $pending['mode']);
        self::assertSame('up1', $pending['upload']);
        self::assertSame(0, $pending['obj_id']);
        self::assertTrue($pending['show']);
        self::assertSame([['import', []]], $ctx->redirects);
        self::assertNotContains('up1', $ctx->handler->discarded);
        self::assertSame([['up1']], $ctx->handler->discard_all_calls);
        self::assertSame([], $ctx->handler->local_files);
        self::assertSame([], $ctx->messages);
    }

    /** @return array<string, array{string}> */
    public static function importModesWithConfirmation(): array
    {
        return ['replace' => ['replace'], 'delete' => ['delete']];
    }

    /** @return array<string, array{mixed}> */
    public static function forgedImportModes(): array
    {
        return [
            'unknown mode' => ['format_disk'],
            'array instead of a mode' => [['replace']],
            'no mode sent' => [null],
            'empty mode' => [''],
        ];
    }

    #[DataProvider('forgedImportModes')]
    public function testUploadObjectRejectsAnUnknownModeAndImportsNothing(mixed $mode): void
    {
        [$gui, $ctx] = $this->importGui(['file' => ['up1']] + ($mode === null ? [] : ['mode' => $mode]));
        $ctx->object->expects($this->never())->method('importLanguageFile');

        $gui->uploadObject();

        self::assertSame([['failure', 'form_input_not_valid']], $ctx->messages);
        self::assertCount(1, $ctx->contents);
        self::assertArrayNotHasKey(self::IMPORT_PENDING, $_SESSION);
        self::assertSame([], $ctx->redirects);
        self::assertSame([], $ctx->handler->discarded);
    }

    #[DataProvider('importModesWithConfirmation')]
    public function testUploadObjectRejectsAnUploadIdentifierOfAnotherSession(string $mode): void
    {
        [$gui, $ctx] = $this->importGui(['file' => ['foreign'], 'mode' => $mode]);
        $ctx->object->expects($this->never())->method('importLanguageFile');

        $gui->uploadObject();

        self::assertSame([['failure', 'upload_error_file_not_found']], $ctx->messages);
        self::assertCount(1, $ctx->contents);
        self::assertArrayNotHasKey(self::IMPORT_PENDING, $_SESSION);
        self::assertSame([], $ctx->redirects);
        self::assertSame([], $ctx->handler->discarded);
    }

    public function testImportConfirmedRunsTheStoredImportOnceAndRemovesUploadAndLocalCopy(): void
    {
        [$gui, $ctx] = $this->createGuiForForms('1', [], [self::IMPORT_TOKEN => 'tok'], 'ilias_de.lang');
        $this->givenPending(self::IMPORT_PENDING, ['mode' => 'replace', 'upload' => 'up1']);
        $ctx->object->expects($this->once())->method('importLanguageFile')
            ->with($this->callback(static fn(string $path): bool => is_file($path)), 'replace');

        $gui->importConfirmedObject();
        $gui->importConfirmedObject();

        self::assertSame(['up1'], $ctx->handler->discarded);
        self::assertCount(1, $ctx->handler->local_files);
        self::assertFileDoesNotExist($ctx->handler->local_files[0]);
        self::assertSame([['success', 'language_file_imported']], $ctx->messages);
        self::assertArrayNotHasKey(self::IMPORT_PENDING, $_SESSION);
        self::assertSame([['import', []], ['import', []]], $ctx->redirects);
    }

    public function testImportConfirmedRemovesUploadAndLocalCopyEvenWhenTheImportThrows(): void
    {
        $this->stubLoggerLangError('SELECT secret FROM lng_data failed', ilException::class);
        [$gui, $ctx] = $this->createGuiForForms('1', [], [self::IMPORT_TOKEN => 'tok'], 'ilias_de.lang');
        $this->givenPending(self::IMPORT_PENDING, ['mode' => 'delete', 'upload' => 'up1']);
        $ctx->object->expects($this->once())->method('importLanguageFile')
            ->willThrowException(new ilException('SELECT secret FROM lng_data failed'));

        $gui->importConfirmedObject();

        self::assertSame(['up1'], $ctx->handler->discarded);
        self::assertCount(1, $ctx->handler->local_files);
        self::assertFileDoesNotExist($ctx->handler->local_files[0]);
        self::assertSame([['failure', 'action_aborted']], $ctx->messages);
        self::assertSame([['import', []]], $ctx->redirects);
    }

    public function testImportConfirmedShowsAnUploadErrorWithCode400Escaped(): void
    {
        [$gui, $ctx] = $this->createGuiForForms('1', [], [self::IMPORT_TOKEN => 'tok'], 'ilias_de.lang');
        $this->givenPending(self::IMPORT_PENDING, ['mode' => 'delete', 'upload' => 'up1']);
        $ctx->object->expects($this->once())->method('importLanguageFile')
            ->willThrowException(new ilException('<b>not found</b>', 400));

        $gui->importConfirmedObject();

        self::assertSame([['failure', '&lt;b&gt;not found&lt;/b&gt;']], $ctx->messages);
    }

    /** @return array<string, array{?string, int, string, string}> token of the request, obj_id, mode and upload of the Vormerkung */
    public static function unusableImportConfirmations(): array
    {
        return [
            'wrong token' => ['other', 0, 'replace', 'up1'],
            'missing token' => [null, 0, 'replace', 'up1'],
            'foreign language' => ['tok', 99, 'replace', 'up1'],
            'manipulated mode' => ['tok', 0, 'format_disk', 'up1'],
        ];
    }

    #[DataProvider('unusableImportConfirmations')]
    public function testImportConfirmedAbortsAndDiscardsTheUploadForAnUnusableVormerkung(
        ?string $token,
        int $obj_id,
        string $mode,
        string $upload
    ): void {
        [$gui, $ctx] = $this->createGuiForForms('1', [], $token === null ? [] : [self::IMPORT_TOKEN => $token], 'x.lang');
        $this->givenPending(self::IMPORT_PENDING, ['mode' => $mode, 'upload' => $upload], 'tok', $obj_id);
        $ctx->object->expects($this->never())->method('importLanguageFile');

        $gui->importConfirmedObject();

        self::assertSame([['failure', 'action_aborted']], $ctx->messages);
        self::assertSame([$upload], $ctx->handler->discarded);
        self::assertArrayNotHasKey(self::IMPORT_PENDING, $_SESSION);
        self::assertSame([['import', []]], $ctx->redirects);
    }

    public function testImportObjectRightAfterTheVormerkungOpensTheModalOnceWithTheEscapedFileNameAndKeepsOnlyThatUpload(): void
    {
        [$gui, $ctx] = $this->createGuiForForms('1', [], [], '<img src=x onerror=alert(1)>.lang');
        $this->givenPending(self::IMPORT_PENDING, ['mode' => 'replace', 'upload' => 'up1'], show: true);

        $gui->importObject();

        [$form, $modal] = $ctx->rendered[0];
        self::assertInstanceOf(\ILIAS\UI\Component\Input\Container\Form\Standard::class, $form);
        self::assertInstanceOf(\ILIAS\UI\Component\Modal\Interruptive::class, $modal);
        self::assertSame('action:importConfirmed?' . self::IMPORT_TOKEN . '=tok', $modal->getFormAction());
        self::assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;.lang', $modal->getMessage());
        self::assertStringNotContainsString('<img', $modal->getMessage());
        self::assertStringContainsString('language_mode_existing_replace', $modal->getMessage());
        self::assertSame([['up1']], $ctx->handler->discard_all_calls);
        self::assertFalse($_SESSION[self::IMPORT_PENDING]['show']);

        $gui->importObject();
        self::assertCount(1, $ctx->rendered[1]);
        self::assertSame(['up1'], $ctx->handler->discarded);
        self::assertArrayNotHasKey(self::IMPORT_PENDING, $_SESSION);
    }

    public function testTheImportFileFieldFiltersNoFileTypesButLimitsTheSize(): void
    {
        [$gui] = $this->createGuiForForms();
        $file = $this->invokeProtectedMethod($gui, 'buildImportForm')->getInputs()['file'];

        self::assertInstanceOf(\ILIAS\UI\Implementation\Component\Input\Field\File::class, $file);
        self::assertSame([], $file->getAcceptedMimeTypes());
        self::assertLessThanOrEqual(ilLanguageImportUploadHandlerGUI::MAX_FILE_SIZE, $file->getMaxFileSize());
    }

    public function testImportObjectWithoutTheShowMarkerShowsOnlyTheFormAndCleansUp(): void
    {
        foreach ([null, 'tok'] as $token) {
            [$gui, $ctx] = $this->createGuiForForms('1', [], $token === null ? [] : [self::IMPORT_TOKEN => $token]);
            $this->givenPending(self::IMPORT_PENDING, ['mode' => 'replace', 'upload' => 'up1']);

            $gui->importObject();

            self::assertCount(1, $ctx->rendered[0], (string) $token);
            self::assertSame(['up1'], $ctx->handler->discarded);
            self::assertSame([[]], $ctx->handler->discard_all_calls);
            self::assertArrayNotHasKey(self::IMPORT_PENDING, $_SESSION);
        }
    }

    /** @return array<string, array{string, string, string}> request method, unsafe command, view shown instead of a non-POST */
    public static function unsafeCommandsByRequestMethod(): array
    {
        $views = [
            'saveEditedEntries' => 'view',
            'upload' => 'import',
            'importConfirmed' => 'import',
            'maintainExecute' => 'maintain',
            'maintainConfirmed' => 'maintain',
            'saveSettings' => 'settings',
            'saveNewEntry' => 'addNewEntry',
        ];
        $cases = [];
        foreach ($views as $cmd => $view) {
            foreach (['GET', 'HEAD', 'POST'] as $method) {
                $cases[$cmd . ' by ' . $method] = [$method, $cmd, $view];
            }
        }

        return $cases;
    }

    #[DataProvider('unsafeCommandsByRequestMethod')]
    public function testEachUnsafeCommandRunsOnlyByPostAndOtherwiseOnlyItsViewIsRendered(
        string $method,
        string $cmd,
        string $view
    ): void {
        $this->stubMaintenanceGranted();
        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->method('getCmd')->willReturn($cmd);
        $mocked = [
            'viewObject', 'importObject', 'maintainObject', 'settingsObject', 'addNewEntryObject',
            'saveEditedEntriesObject', 'uploadObject', 'importConfirmedObject', 'maintainExecuteObject',
            'maintainConfirmedObject', 'saveSettingsObject', 'saveNewEntryObject',
        ];
        $gui = $this->createGuiForExecuteCommand(false, null, [], $ctrl, $mocked);
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn($method);
        $http = $this->createStub(\ILIAS\HTTP\GlobalHttpState::class);
        $http->method('request')->willReturn($request);
        $this->inject($gui, 'http', $http);

        $expected = ($method === 'POST' ? $cmd : $view) . 'Object';
        foreach ($mocked as $candidate) {
            $gui->expects($candidate === $expected ? $this->once() : $this->never())->method($candidate);
        }

        $gui->executeCommand();
    }

    /** An exception without a 4xx code may carry SQL: only a generic message is shown. */
    public function testUploadObjectShowsAGenericFailureForABrokenFileAndStillRemovesUploadAndLocalCopy(): void
    {
        [$gui, $ctx] = $this->importGui(['file' => ['up1'], 'mode' => 'keepall']);
        $ctx->object->expects($this->once())->method('importLanguageFile')
            ->willThrowException(new ilException('SELECT secret FROM usr_data'));

        $gui->uploadObject();

        self::assertSame([['failure', 'action_aborted']], $ctx->messages);
        self::assertContains('up1', $ctx->handler->discarded);
        self::assertCount(1, $ctx->handler->local_files);
        self::assertFileDoesNotExist($ctx->handler->local_files[0]);
        self::assertSame([['import', []]], $ctx->redirects);
    }

    /** @return array<string, array{string, string}> */
    public static function maintainOptionsImportingAFile(): array
    {
        return ['load' => ['load', 'ilias_de.lang.local']];
    }

    #[DataProvider('maintainOptionsImportingAFile')]
    public function testMaintainConfirmedShowsAGenericFailureForAnInvalidFileInsteadOfTheSuccessAndKeepsTheLocalFlag(
        string $option,
        string $file_name
    ): void {
        [$gui, $ctx] = $this->createGuiForForms('0', [], [self::MAINTAIN_TOKEN => 'tok']);
        $dir = $this->scratchDir();
        $this->scratch_paths[] = $dir . '/' . $file_name;
        file_put_contents($dir . '/' . $file_name, 'invalid');
        $ctx->object->method('getLangPath')->willReturn($dir);
        $ctx->object->method('getCustLangPath')->willReturn($dir);
        $this->givenPending(self::MAINTAIN_PENDING, ['option' => $option]);
        $ctx->object->expects($this->once())->method('importLanguageFile')
            ->with($dir . '/' . $file_name, 'replace')
            ->willThrowException(new ilException('SELECT secret FROM usr_data'));
        $ctx->object->expects($this->never())->method('setLocal');

        $gui->maintainConfirmedObject();

        self::assertSame([['failure', 'action_aborted']], $ctx->messages);
        self::assertSame([['maintain', []]], $ctx->redirects);
    }

    /** @return array<string, array{string, list<string>}> langmode, scopes in the order of the former form */
    public static function exportScopesByLangmode(): array
    {
        return [
            'langmode' => ['1', ['global', 'local', 'added', 'unchanged', 'merged']],
            'no langmode' => ['0', ['global', 'local', 'unchanged']],
        ];
    }

    /** @param list<string> $scopes */
    #[DataProvider('exportScopesByLangmode')]
    public function testTheExportFormOffersAddedAndMergedOnlyInLangmodeInTheFormerOrder(string $langmode, array $scopes): void
    {
        [$gui, $ctx] = $this->createGuiForForms($langmode);

        $gui->exportObject();

        $form = $ctx->rendered[0][0];
        self::assertInstanceOf(\ILIAS\UI\Component\Input\Container\Form\Standard::class, $form);
        self::assertSame('action:download?', $form->getPostURL());
        $radio = $form->getInputs()['scope'];
        self::assertInstanceOf(\ILIAS\UI\Implementation\Component\Input\Field\Radio::class, $radio);
        self::assertSame($scopes, array_keys($radio->getOptions()));
        self::assertFalse($radio->isRequired());
        foreach ($scopes as $scope) {
            self::assertSame('language_scope_' . $scope, $radio->getOptions()[$scope]);
            self::assertSame('language_scope_' . $scope . '_info', $radio->getBylineFor($scope));
        }
        self::assertSame('global', $radio->getValue());
    }

    /** @return array<string, array{string, ?string, string}> langmode, remembered scope, preselected scope */
    public static function exportPresets(): array
    {
        return [
            'remembered local' => ['0', 'local', 'local'],
            'remembered merged in langmode' => ['1', 'merged', 'merged'],
            'merged is not offered without langmode' => ['0', 'merged', 'global'],
            'unknown remembered scope' => ['1', 'bogus', 'global'],
            'nothing remembered' => ['1', null, 'global'],
        ];
    }

    #[DataProvider('exportPresets')]
    public function testTheExportFormPreselectsTheRememberedScopeIfItIsOffered(
        string $langmode,
        ?string $remembered,
        string $expected
    ): void {
        [$gui, $ctx] = $this->createGuiForForms($langmode);
        $this->maintenanceState()->values = $remembered === null ? [] : ['export_scope' => $remembered];

        $gui->exportObject();

        self::assertSame($expected, $ctx->rendered[0][0]->getInputs()['scope']->getValue());
    }

    /** @return array<string, array{string, array<string, mixed>}> langmode, post */
    public static function invalidExportSubmissions(): array
    {
        return [
            'merged without langmode' => ['0', ['scope' => 'merged']],
            'added without langmode' => ['0', ['scope' => 'added']],
            'unknown scope' => ['1', ['scope' => '../etc/passwd']],
            'no scope' => ['1', []],
            'empty scope' => ['1', ['scope' => '']],
            'array instead of a scope' => ['1', ['scope' => ['global']]],
            'nested array instead of a scope' => ['1', ['scope' => ['a' => ['global']]]],
        ];
    }

    /** @param array<string, mixed> $post */
    #[DataProvider('invalidExportSubmissions')]
    public function testDownloadObjectRefusesAScopeThatIsNotOfferedAndDeliversNothing(string $langmode, array $post): void
    {
        defined('ILIAS_VERSION') || define('ILIAS_VERSION', '10.0');
        [$gui, $ctx] = $this->createGuiForForms($langmode, $this->postFor('buildExportForm', $post));
        $ctx->object->expects($this->never())->method('getGlobalLanguageFile');
        $this->maintenanceState()->values = ['export_scope' => 'local'];

        $gui->downloadObject();

        self::assertSame([['failure', 'form_input_not_valid']], $ctx->messages);
        self::assertSame(['export_scope' => 'local'], $this->maintenanceState()->values, 'the remembered scope is kept');
        self::assertCount(1, $ctx->contents, 'the form is shown again');
    }

    public function testDownloadObjectOnlyShowsTheFormForARequestThatIsNotAPost(): void
    {
        [$gui, $ctx] = $this->createGuiForForms(
            '1',
            $this->postFor('buildExportForm', ['scope' => 'global']),
            [],
            null,
            'de',
            [],
            'GET'
        );
        $ctx->object->expects($this->never())->method('getGlobalLanguageFile');

        $gui->downloadObject();

        self::assertSame([], $ctx->messages);
        self::assertCount(1, $ctx->contents);
        self::assertSame([], $this->maintenanceState()->values);
    }

    /** @param list<string> $scopes */
    #[DataProvider('exportScopesByLangmode')]
    public function testDownloadObjectAcceptsEveryOfferedScopeAndRemembersItForThePreselection(string $langmode, array $scopes): void
    {
        defined('ILIAS_VERSION') || define('ILIAS_VERSION', '10.0');
        foreach ($scopes as $scope) {
            [$gui, $ctx] = $this->createGuiForForms($langmode, $this->postFor('buildExportForm', ['scope' => $scope]));
            $ctx->object->method('getGlobalLanguageFile')->willThrowException(new LogicException('delivery reached'));
            $_SESSION = [];
            $this->maintenanceState()->clear();

            try {
                $gui->downloadObject();
                self::fail('the export was not started for ' . $scope);
            } catch (LogicException $e) {
                self::assertSame('delivery reached', $e->getMessage());
            }

            self::assertSame([], $ctx->messages, $scope);
            self::assertSame(['export_scope' => $scope], $this->maintenanceState()->values, $scope);
        }
    }

    /** @return array<string, array{mixed, string}> stored value, preselected import mode */
    public static function importModePresets(): array
    {
        return [
            'remembered replace' => ['replace', 'replace'],
            'remembered delete' => ['delete', 'delete'],
            'unknown remembered mode' => ['bogus', 'keepall'],
            'nothing remembered' => [null, 'keepall'],
            'array instead of a string' => [['replace'], 'keepall'],
            'int instead of a string' => [5, 'keepall'],
        ];
    }

    #[DataProvider('importModePresets')]
    public function testTheImportFormPreselectsTheRememberedModeIfItIsOffered(mixed $stored, string $expected): void
    {
        [$gui] = $this->createGuiForForms();
        $this->maintenanceState()->values = $stored === null ? [] : ['import_mode_existing' => $stored];

        self::assertSame($expected, $this->invokeProtectedMethod($gui, 'buildImportForm')->getInputs()['mode']->getValue());
    }

    /** @return array<string, array{mixed, string}> stored value, preselected maintain option */
    public static function maintainPresets(): array
    {
        return [
            'remembered clear' => ['clear', 'clear'],
            'remembered merge in langmode' => ['merge', 'merge'],
            'unknown remembered option' => ['bogus', 'load'],
            'nothing remembered' => [null, 'load'],
            'array instead of a string' => [['clear'], 'load'],
            'bool instead of a string' => [true, 'load'],
        ];
    }

    #[DataProvider('maintainPresets')]
    public function testTheMaintainFormPreselectsTheRememberedOptionIfItIsOffered(mixed $stored, string $expected): void
    {
        [$gui] = $this->createGuiForForms();
        $this->maintenanceState()->values = $stored === null ? [] : ['maintain' => $stored];

        self::assertSame($expected, $this->invokeProtectedMethod($gui, 'buildMaintainForm')->getInputs()['maintain']->getValue());
    }

    public function testTheMaintainFormDoesNotPreselectAnOptionOfLangmodeWithoutLangmode(): void
    {
        [$gui] = $this->createGuiForForms('0');
        $this->maintenanceState()->values = ['maintain' => 'merge'];

        self::assertSame('load', $this->invokeProtectedMethod($gui, 'buildMaintainForm')->getInputs()['maintain']->getValue());
    }

    /** @return array<string, array{string, string, string}> builder, input, default */
    public static function presetBuilders(): array
    {
        return [
            'import mode' => ['buildImportForm', 'mode', 'keepall'],
            'export scope' => ['buildExportForm', 'scope', 'global'],
            'maintain option' => ['buildMaintainForm', 'maintain', 'load'],
        ];
    }

    #[DataProvider('presetBuilders')]
    public function testAnUnreadableStoredPresetFallsBackToTheDefault(string $builder, string $input, string $default): void
    {
        [$gui] = $this->createGuiForForms();
        $store = $this->createStub(\ILIAS\KeyValueStorage\Store::class);
        $store->method('get')->willThrowException(new \ILIAS\KeyValueStorage\Exception\InvalidStoredValueException('not valid JSON'));
        $storage = $this->createStub(KeyValueStorage::class);
        $storage->method('session')->willReturn($store);
        unset($GLOBALS['DIC'][KeyValueStorage::class]);
        $GLOBALS['DIC'][KeyValueStorage::class] = $storage;

        self::assertSame($default, $this->invokeProtectedMethod($gui, $builder)->getInputs()[$input]->getValue());
    }

    #[DataProvider('importModesWithoutConfirmation')]
    public function testUploadObjectRemembersAValidImportModeForThePreselection(string $mode): void
    {
        [$gui] = $this->importGui(['file' => ['up1'], 'mode' => $mode]);

        $gui->uploadObject();

        self::assertSame(['import_mode_existing' => $mode], $this->maintenanceState()->values);
    }

    #[DataProvider('importModesWithConfirmation')]
    public function testUploadObjectRemembersADestructiveImportModeAlreadyBeforeTheConfirmation(string $mode): void
    {
        [$gui] = $this->importGui(['file' => ['up1'], 'mode' => $mode]);

        $gui->uploadObject();

        self::assertSame(['import_mode_existing' => $mode], $this->maintenanceState()->values);
    }

    #[DataProvider('forgedImportModes')]
    public function testUploadObjectKeepsTheRememberedImportModeForAnInvalidMode(mixed $mode): void
    {
        [$gui] = $this->importGui(['file' => ['up1']] + ($mode === null ? [] : ['mode' => $mode]));
        $this->maintenanceState()->values = ['import_mode_existing' => 'delete'];

        $gui->uploadObject();

        self::assertSame(['import_mode_existing' => 'delete'], $this->maintenanceState()->values);
    }

    public function testUploadObjectKeepsTheRememberedImportModeForAnUploadOfAnotherSession(): void
    {
        [$gui] = $this->importGui(['file' => ['foreign'], 'mode' => 'replace']);
        $this->maintenanceState()->values = ['import_mode_existing' => 'delete'];

        $gui->uploadObject();

        self::assertSame(['import_mode_existing' => 'delete'], $this->maintenanceState()->values);
    }

    #[DataProvider('destructiveMaintainOptions')]
    public function testMaintainExecuteRemembersTheOptionAlreadyBeforeTheConfirmationWithoutTouchingOtherPresets(
        string $option,
        string $langmode
    ): void {
        [$gui] = $this->maintainGui($option, $langmode);
        $this->maintenanceState()->values = ['export_scope' => 'local', 'import_mode_existing' => 'replace'];

        $gui->maintainExecuteObject();

        self::assertSame(
            ['export_scope' => 'local', 'import_mode_existing' => 'replace', 'maintain' => $option],
            $this->maintenanceState()->values
        );
    }

    /** @param array<string, mixed> $values */
    #[DataProvider('invalidMaintainSubmissions')]
    public function testMaintainExecuteKeepsTheRememberedOptionForAnOptionThatIsNotOffered(array $values, string $langmode): void
    {
        [$gui] = $this->createGuiForForms($langmode, $this->postFor('buildMaintainForm', $values));
        $this->maintenanceState()->values = ['maintain' => 'clear'];

        $gui->maintainExecuteObject();

        self::assertSame(['maintain' => 'clear'], $this->maintenanceState()->values);
    }

    public function testTheSettingsFormPreselectsTheStoredTranslationFlagOfThisLanguage(): void
    {
        foreach ([[true, '1', '0'], [false, '0', '1']] as [$expected, $stored, $other]) {
            $this->settings_values = ['lang_translate_de' => $stored, 'lang_translate_en' => $other];
            [$gui, $ctx] = $this->createGuiForForms();

            $gui->settingsObject();

            $form = $ctx->rendered[0][0];
            self::assertSame('action:saveSettings?', $form->getPostURL());
            $checkbox = $form->getInputs()['translation'];
            self::assertSame($expected, $checkbox->getValue(), $stored);
            self::assertSame('language_translation_enabled', $checkbox->getLabel());
            self::assertSame('language_note_translation', $checkbox->getByline());
        }
    }

    public function testTheSettingsFormIsNotCheckedWithoutAStoredFlag(): void
    {
        [$gui, $ctx] = $this->createGuiForForms();

        $gui->settingsObject();

        self::assertFalse($ctx->rendered[0][0]->getInputs()['translation']->getValue());
    }

    /** @return array<string, array{array<string, string>, bool}> */
    public static function settingsValues(): array
    {
        return ['checked' => [['translation' => 'checked'], true], 'unchecked box is not submitted' => [[], false]];
    }

    /** @param array<string, string> $values */
    #[DataProvider('settingsValues')]
    public function testSaveSettingsObjectHandsTheStateOfTheRealCheckboxToTheActivityAndShowsTheFormAgain(array $values, bool $enabled): void
    {
        [$gui, $ctx] = $this->createGuiForForms('1', $this->postFor('buildSettingsForm', $values));
        $user = $this->createStub(ilObjUser::class);
        $user->method('getId')->willReturn(6);
        $this->inject($gui, 'user', $user);
        $activity = $this->createMock(SetLanguageTranslationEnabled::class);
        $activity->expects($this->once())->method('maybePerformAs')
            ->with($this->anything(), 6, ['language_key' => 'de', 'enabled' => $enabled])
            ->willReturn(new ResultOk(['changed' => true]));
        $this->inject($gui, 'set_language_translation_enabled', $activity);

        $gui->saveSettingsObject();

        self::assertSame([['success', 'settings_saved']], $ctx->messages);
        self::assertCount(1, $ctx->contents);
    }

    private const array NEW_ENTRY_LANGUAGES = ['de', 'en', 'fr'];

    /**
     * @param array<string, mixed> $values form fields by key
     * @param array<string, string> $query
     * @return array{0: ilObjLanguageExtGUI, 1: stdClass}
     */
    private function newEntryGui(array $values, array $query = ['eid' => 'sometopic'], string $method = 'POST'): array
    {
        $this->maintenanceState()->values = [
            'used_topics' => ['sometopic', 'other'],
            'used_modules' => ['common', 'user'],
        ];
        $post = $this->postFor('buildAddNewEntryForm', $values, ['sometopic'], self::NEW_ENTRY_LANGUAGES);

        return $this->createGuiForForms('1', $post, $query, null, 'de', self::NEW_ENTRY_LANGUAGES, $method);
    }

    public function testTheNewEntryFormHasModuleIdentifierAndOneFieldPerInstalledLanguageWithDeAndEnRequired(): void
    {
        [$gui, $ctx] = $this->newEntryGui([]);

        $form = $this->invokeProtectedMethod($gui, 'buildAddNewEntryForm', ['sometopic']);

        self::assertSame('action:saveNewEntry?eid=sometopic', $form->getPostURL());
        self::assertSame([], $ctx->params, 'the eid is not left on the ctrl');
        $inputs = $form->getInputs();
        self::assertSame(['mod', 'id', 'trans_de', 'trans_en', 'trans_fr'], array_keys($inputs));
        self::assertTrue($inputs['mod']->isRequired());
        self::assertSame(['common' => 'common', 'user' => 'user'], $inputs['mod']->getOptions());
        self::assertTrue($inputs['id']->isDisabled());
        self::assertSame('sometopic', $inputs['id']->getValue());
        self::assertTrue($inputs['trans_de']->isRequired());
        self::assertTrue($inputs['trans_en']->isRequired());
        self::assertFalse($inputs['trans_fr']->isRequired());
        self::assertSame('meta_l_fr', $inputs['trans_fr']->getLabel());
    }

    /** @return array<string, array{string, string}> posted text, value handed to the activity (like ilTextInputGUI) */
    public static function postedNewEntryTexts(): array
    {
        return [
            'plain text is kept' => ['Hallo Welt', 'Hallo Welt'],
            'surrounding blanks are trimmed' => ['  Hallo  ', 'Hallo'],
            'allowed tags are kept' => ['<b>x</b> <i>y</i>', '<b>x</b> <i>y</i>'],
            'script is neutralized, not executable' => ['a<script>alert(1)</script>b', 'a< script>alert(1)< /script>b'],
            'tags outside the allowlist are neutralized' => ['<table>t</table><b>x</b>', '< table>t< /table>< b>x< /b>'],
            'lone less-than is kept with a blank' => ['1 <2', '1 < 2'],
        ];
    }

    #[DataProvider('postedNewEntryTexts')]
    public function testSaveNewEntryObjectFiltersTheTextsLikeATextInputAndTakesTheIdentifierFromTheEid(
        string $posted,
        string $expected
    ): void {
        [$gui, $ctx] = $this->newEntryGui([
            'mod' => 'user',
            'id' => 'tampered',
            'trans_de' => $posted,
            'trans_en' => 'Hello',
            'trans_fr' => $posted,
        ]);
        $user = $this->createStub(ilObjUser::class);
        $user->method('getId')->willReturn(6);
        $this->inject($gui, 'user', $user);
        $activity = $this->createMock(AddLanguageEntry::class);
        $activity->expects($this->once())->method('maybePerformAs')
            ->with($this->anything(), 6, [
                'module' => 'user',
                'identifier' => 'sometopic',
                'translations' => ['de' => $expected, 'en' => 'Hello', 'fr' => $expected],
            ])
            ->willReturn(new ResultOk(['added_language_keys' => ['de', 'en', 'fr']]));
        $this->inject($gui, 'add_language_entry', $activity);

        $gui->saveNewEntryObject();

        self::assertSame([['success', 'settings_saved']], $ctx->messages);
        self::assertSame([['view', []]], $ctx->redirects);
    }

    /** @return array<string, array{array<string, mixed>}> */
    public static function invalidNewEntries(): array
    {
        $valid = ['mod' => 'common', 'trans_de' => 'Hallo', 'trans_en' => 'Hello', 'trans_fr' => 'Salut'];

        return [
            'de missing' => [array_diff_key($valid, ['trans_de' => 1])],
            'de blank' => [['trans_de' => "  \t"] + $valid],
            'module not offered' => [['mod' => 'secrets'] + $valid],
            'module missing' => [array_diff_key($valid, ['mod' => 1])],
            'array instead of a module' => [['mod' => ['common']] + $valid],
            'array instead of a translation' => [['trans_de' => ['Hallo']] + $valid],
        ];
    }

    /** @param array<string, mixed> $values */
    #[DataProvider('invalidNewEntries')]
    public function testSaveNewEntryObjectRefusesMissingRequiredFieldsAndForeignModulesAndShowsTheFormAgain(array $values): void
    {
        [$gui, $ctx] = $this->newEntryGui($values);
        $activity = $this->createMock(AddLanguageEntry::class);
        $activity->expects($this->never())->method('maybePerformAs');
        $this->inject($gui, 'add_language_entry', $activity);

        $gui->saveNewEntryObject();

        self::assertSame([['failure', 'form_input_not_valid']], $ctx->messages);
        self::assertSame([], $ctx->redirects);
        self::assertCount(1, $ctx->contents);
    }

    public function testAFilledOptionalLanguageIsNotRequiredAndABlankOneIsHandedOnAsEmpty(): void
    {
        [$gui] = $this->newEntryGui(['mod' => 'common', 'trans_de' => 'Hallo', 'trans_en' => 'Hello', 'trans_fr' => '']);
        $user = $this->createStub(ilObjUser::class);
        $this->inject($gui, 'user', $user);
        $activity = $this->createMock(AddLanguageEntry::class);
        $activity->expects($this->once())->method('maybePerformAs')
            ->with($this->anything(), $this->anything(), $this->callback(
                static fn(array $p): bool => $p['translations'] === ['de' => 'Hallo', 'en' => 'Hello', 'fr' => '']
            ))
            ->willReturn(new ResultOk(['added_language_keys' => ['de', 'en']]));
        $this->inject($gui, 'add_language_entry', $activity);

        $gui->saveNewEntryObject();
    }

    /** @return array<string, array{array<string, string>}> */
    public static function unusableEids(): array
    {
        return [
            'unknown topic' => [['eid' => 'not_stored']],
            'empty' => [['eid' => '']],
            'blank' => [['eid' => '   ']],
            'missing' => [[]],
        ];
    }

    /** @param array<string, string> $query */
    #[DataProvider('unusableEids')]
    public function testAddNewEntryAndSaveNewEntryLeadBackToTheViewForAnEidThatIsNoStoredTopic(array $query): void
    {
        foreach (['addNewEntryObject', 'saveNewEntryObject'] as $command) {
            [$gui, $ctx] = $this->newEntryGui(['mod' => 'common', 'trans_de' => 'a', 'trans_en' => 'b'], $query);
            $activity = $this->createMock(AddLanguageEntry::class);
            $activity->expects($this->never())->method('maybePerformAs');
            $this->inject($gui, 'add_language_entry', $activity);

            $gui->$command();

            self::assertSame([['view', []]], $ctx->redirects, $command);
            self::assertSame([], $ctx->contents, $command);
            self::assertSame([], $ctx->messages, $command);
        }
    }

    public function testAddNewEntryObjectShowsTheFormOfTheTrimmedStoredEidWithACancelButtonToTheView(): void
    {
        [$gui, $ctx] = $this->newEntryGui([], ['eid' => ' sometopic ']);

        $gui->addNewEntryObject();

        self::assertSame([], $ctx->redirects);
        [$form, $cancel] = $ctx->rendered[0];
        self::assertSame('action:saveNewEntry?eid=sometopic', $form->getPostURL());
        self::assertSame('sometopic', $form->getInputs()['id']->getValue());
        self::assertSame('cancel', $cancel->getLabel());
        self::assertSame('link:view?', $cancel->getAction());
    }

    public function testBuildMissingEntriesListsEscapedEntriesWithAnAddLinkCarryingTheEidAndLeavesNoParameterBehind(): void
    {
        [$gui, $ctx] = $this->createGuiForForms();
        $this->inject($gui, 'ui_renderer', $this->renderer());
        // like ilCtrl, which puts the already URL-encoded parameter value into the link as given
        $ctrl = $this->createStub(ilCtrl::class);
        $ctrl->method('setParameter')->willReturnCallback(
            static function ($gui, string $name, $value) use ($ctx): void {
                $ctx->params[$name] = $value;
            }
        );
        $ctrl->method('clearParameterByClass')->willReturnCallback(
            static function (string $class, string $name) use ($ctx): void {
                unset($ctx->params[$name]);
            }
        );
        $ctrl->method('getLinkTarget')->willReturnCallback(
            static fn($gui, string $cmd): string => 'link:' . $cmd . '?eid=' . ($ctx->params['eid'] ?? '')
        );
        $this->inject($gui, 'ctrl', $ctrl);
        $evil = '<script>alert(1)</script>';

        $panel = $this->invokeProtectedMethod($gui, 'buildMissingEntries', [[$evil, 'a&b', 'plain']]);

        self::assertInstanceOf(\ILIAS\UI\Component\Panel\Standard::class, $panel);
        self::assertSame('adm_missing_entries', $panel->getTitle());
        $html = $this->renderer()->render($panel);
        self::assertStringNotContainsString('<script>', $html);
        self::assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        self::assertStringContainsString('a&amp;b', $html);
        self::assertStringNotContainsString('a&amp;amp;b', $html, 'escaped once only');
        self::assertSame(3, substr_count($html, 'adm_missing_entry_add_action'));
        self::assertStringContainsString('link:addNewEntry?eid=' . rawurlencode($evil), str_replace('+', '%20', $html));
        self::assertStringContainsString('eid=a%26b', str_replace('&amp;', '&', $html));
        self::assertStringContainsString('eid=plain', $html);
        self::assertSame([], $ctx->params);
    }

    public function testBuildMissingEntriesReturnsNoPanelWithoutEntries(): void
    {
        [$gui] = $this->createGuiForForms();

        self::assertNull($this->invokeProtectedMethod($gui, 'buildMissingEntries', [[]]));
    }
}
