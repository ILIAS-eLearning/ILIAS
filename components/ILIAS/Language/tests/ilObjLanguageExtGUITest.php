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

class ilObjLanguageExtGUITest extends TestCase
{
    use RestoresProcessState;

    use ilObjLanguageExtGUITestFixture {
        tearDown as private tearDownGuiTestSupport;
    }

    /** The resetEntriesTableRangeIfRequested() tests write into $_SESSION via the static ilSession. */
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
    // editSelectedObject()
    //
    // The ALL_OBJECTS resolution branch (`in_array('ALL_OBJECTS', $names,
    // true)`) additionally calls `$this->initFilter()` - a *private* method
    // that unconditionally builds a real ILIAS\UI\Component\Input\Container\
    // Filter\Filter via `$DIC->uiService()->filter()->standard(...)`. Being
    // private, it cannot be stubbed away via a partial mock (PHP does not
    // allow overriding private methods from a subclass), and building the
    // real filter service is exactly the kind of brittle
    // session-/request-state coupling the task description already
    // anticipates for initFilter()/viewObject(). This branch is therefore a
    // deliberate coverage gap (see final report) - only the two branches
    // that do NOT go through initFilter() are covered here. The
    // ALL_OBJECTS -> getFilteredEntryNames() *translation logic itself* is
    // still covered, at the ilLanguageEntriesTable level (see
    // ilLanguageEntriesTableTest), which is the part that is actually
    // reachable in isolation.
    // -----------------------------------------------------------------

    private function invokeProtectedMethod(object $object, string $method_name, array $args = []): mixed
    {
        // ReflectionMethod::setAccessible() has had no effect (and has been
        // deprecated) since PHP 8.1 - invoke() already bypasses visibility.
        return (new ReflectionMethod($object, $method_name))->invoke($object, ...$args);
    }

    /**
     * @return ilObjLanguageExtGUI&MockObject
     */
    private function createGuiForEditSelected(
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ctrl,
        ilLanguage $lng,
        ?ilPropertyFormGUI $form = null
    ): ilObjLanguageExtGUI {
        $gui = $this->getMockBuilder(ilObjLanguageExtGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['initEditEntriesForm'])
            ->getMock();

        if ($form !== null) {
            $gui->method('initEditEntriesForm')->willReturn($form);
        } else {
            $gui->expects($this->never())->method('initEditEntriesForm');
        }

        $this->inject($gui, 'ui_factory', $this->createMock(\ILIAS\UI\Factory::class));
        $this->inject($gui, 'tpl', $tpl);
        $this->inject($gui, 'ctrl', $ctrl);
        $this->inject($gui, 'lng', $lng);

        return $gui;
    }

    /**
     * Regression test for the "empty selection" guard: it must fire BEFORE
     * initEditEntriesForm() is ever built, showing the established
     * 'no_checkbox' failure message and redirecting to "view".
     */
    public function testEditSelectedObjectWithEmptyNamesShowsNoCheckboxMessageAndRedirectsToView(): void
    {
        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())
            ->method('setOnScreenMessage')
            ->with('failure', 'no_checkbox', true);

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect')->with(
            $this->isInstanceOf(ilObjLanguageExtGUI::class),
            'view'
        );

        $gui = $this->createGuiForEditSelected(
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $this->invokeProtectedMethod($gui, 'editSelectedObject', [[]]);
    }

    /**
     * A non-empty, non-ALL_OBJECTS selection is rendered directly (no
     * redirect) via initEditEntriesForm() - this pins down both that the
     * exact given $names reach initEditEntriesForm() unchanged, and that the
     * resulting HTML is what gets set as the page content.
     */
    public function testEditSelectedObjectWithNonEmptyNamesRendersTheEditFormWithoutRedirecting(): void
    {
        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())->method('setContent')->with('<form>the edit form</form>');
        $tpl->expects($this->never())->method('setOnScreenMessage');

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->never())->method('redirect');

        $form = $this->createMock(ilPropertyFormGUI::class);
        $form->method('getHTML')->willReturn('<form>the edit form</form>');

        $gui = $this->createGuiForEditSelected(
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs(),
            $form
        );
        $gui->expects($this->once())
            ->method('initEditEntriesForm')
            ->with(['common#:#access', 'common#:#other'])
            ->willReturn($form);

        $this->invokeProtectedMethod(
            $gui,
            'editSelectedObject',
            [['common#:#access', 'common#:#other']]
        );
    }

    /**
     * Regression test for exceedsMaxInputVars(): a $names selection large
     * enough that initEditEntriesForm() would render more POST fields than
     * this environment's actual max_input_vars allows must abort BEFORE the
     * form is ever built - same failure message/redirect as the empty-
     * selection guard, but for a structurally different reason.
     *
     * ini_get('max_input_vars') is a PHP_INI_PERDIR directive - it cannot be
     * changed at runtime via ini_set() for this test, so the entry count is
     * derived from the ACTUAL value in this environment (falling back to the
     * same 1000 exceedsMaxInputVars() itself falls back to) rather than a
     * fixed literal: a hardcoded count like 400 would silently fail to
     * trigger the guard in an environment configured with a higher
     * max_input_vars than the one this test happened to be written against.
     */
    public function testEditSelectedObjectAbortsBeforeBuildingTheFormWhenSelectionExceedsMaxInputVars(): void
    {
        $configured_limit = ini_get('max_input_vars');
        $limit = ($configured_limit === false || $configured_limit === '') ? 1000 : (int) $configured_limit;
        // exceedsMaxInputVars() trips when ($entry_count * 3 + 1) > $limit -
        // comfortably overshoot rather than sit right at the boundary, so
        // this test is robust against off-by-one differences in how the
        // real max_input_vars value is reported across environments.
        $entry_count = $limit + 10;

        $names = [];
        for ($i = 0; $i < $entry_count; $i++) {
            $names[] = 'common#:#entry' . $i;
        }

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())
            ->method('setOnScreenMessage')
            ->with('failure', 'language_too_many_entries_selected', true);
        $tpl->expects($this->never())->method('setContent');

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect')->with(
            $this->isInstanceOf(ilObjLanguageExtGUI::class),
            'view'
        );

        // No $form given -> createGuiForEditSelected() wires
        // $gui->expects($this->never())->method('initEditEntriesForm') itself.
        $gui = $this->createGuiForEditSelected(
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $this->invokeProtectedMethod($gui, 'editSelectedObject', [$names]);
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
     * @return ilObjLanguageExtGUI&MockObject
     */
    private function createGuiForExecuteCommand(
        bool $has_action_token,
        ?string $action_value,
        array $row_ids,
        ilCtrl $ctrl
    ): ilObjLanguageExtGUI {
        $gui = $this->getMockBuilder(ilObjLanguageExtGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['editSelectedObject', 'viewObject'])
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
        $ctrl->method('getCmd')->with('view')->willReturn('view');

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
    // initEditEntriesForm()
    //
    // initEditEntriesForm() is `protected` and builds a REAL
    // ilPropertyFormGUI (not mocked) - both ilPropertyFormGUI's own
    // constructor and every ilFormPropertyGUI-derived item's constructor
    // (ilHiddenInputGUI/ilTextAreaInputGUI) unconditionally reach into
    // `global $DIC->ctrl()`/`$DIC->language()` (see class.ilPropertyFormGUI.php/
    // class.ilFormPropertyGUI.php), independently of the GUI instance's OWN
    // $ctrl/$lng properties used elsewhere in this test class - hence
    // stubDicForRealFormConstruction() below, on top of the usual
    // inject() wiring.
    // -----------------------------------------------------------------

    private function stubDicForRealFormConstruction(): void
    {
        $GLOBALS['DIC'] = new \ILIAS\DI\Container();
        $GLOBALS['DIC']['lng'] = $this->createMock(ilLanguage::class);
        $GLOBALS['DIC']['ilCtrl'] = $this->createMock(ilCtrl::class);
        // ilTextAreaInputGUI's own constructor (used for the visible
        // translation/comment fields) unconditionally reads $DIC->user()
        // too, on top of language()/ctrl().
        $GLOBALS['DIC']['ilUser'] = $this->createMock(ilObjUser::class);
    }

    /**
     * @return ilObjLanguageExtGUI&MockObject
     */
    private function createGuiForInitEditEntriesForm(
        ilObjLanguageExt $object,
        ilLanguage $lng,
        bool $langmode,
        ilCtrl $ctrl
    ): ilObjLanguageExtGUI {
        $gui = $this->getMockBuilder(ilObjLanguageExtGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->inject($gui, 'object', $object);
        $this->inject($gui, 'lng', $lng);
        $this->inject($gui, 'ctrl', $ctrl);
        $this->inject($gui, 'langmode', $langmode);
        $this->inject($gui, 'entries_table', $this->entriesTableFor($object, $lng));

        return $gui;
    }

    private function findItemByPostVar(ilPropertyFormGUI $form, string $post_var): ?ilFormPropertyGUI
    {
        foreach ($form->getItems() as $item) {
            if (method_exists($item, 'getPostVar') && $item->getPostVar() === $post_var) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Regression test for the data-loss blocker: outside langmode,
     * initEditEntriesForm() used to not build a "comment[$idx]" field at
     * all, which made saveEditedEntriesObject() overwrite the entry's
     * existing remark with '' on every save. It must now still carry the
     * existing remark - unchanged - as a hidden field.
     */
    public function testInitEditEntriesFormNonLangmodeCarriesTheExistingRemarkAsAHiddenFieldInsteadOfLosingIt(): void
    {
        $this->stubDicForRealFormConstruction();

        $object = $this->createFakeLanguageObject('de');
        $object->method('getAllValues')->willReturn(['common#:#access' => 'Access']);
        $object->method('getEffectiveRemarks')->willReturn(['common#:#access' => 'Existing remark']);

        $gui = $this->createGuiForInitEditEntriesForm(
            $object,
            $this->createLanguageMockReturningTopicAsIs(),
            false,
            $this->createMock(ilCtrl::class)
        );

        $form = $this->invokeProtectedMethod($gui, 'initEditEntriesForm', [['common#:#access']]);

        $comment_item = $this->findItemByPostVar($form, 'comment[0]');
        self::assertInstanceOf(ilHiddenInputGUI::class, $comment_item);
        self::assertSame('Existing remark', $comment_item->getValue());
    }

    /**
     * Control case: in langmode, the comment is still edited via a visible
     * field - not silently turned into a hidden field too. Regression test
     * for the reverted comment-field type: a single-line ilTextInputGUI
     * (matching ilObjLanguage::replaceLangEntry()'s own 250-char truncation,
     * see initEditEntriesForm()'s own docblock), NOT a multi-line
     * ilTextAreaInputGUI - a comment containing a line break would break
     * ilLanguageFile's strictly line-based format on export/merge. The
     * translation field itself is unaffected by this and remains a
     * multi-line ilTextAreaInputGUI.
     */
    public function testInitEditEntriesFormLangmodeUsesAVisibleSingleLineTextInputWithMaxLength250ForTheComment(): void
    {
        $this->stubDicForRealFormConstruction();

        $object = $this->createFakeLanguageObject('de');
        $object->method('getAllValues')->willReturn(['common#:#access' => 'Access']);
        $object->method('getEffectiveRemarks')->willReturn(['common#:#access' => 'Existing remark']);

        $gui = $this->createGuiForInitEditEntriesForm(
            $object,
            $this->createLanguageMockReturningTopicAsIs(),
            true,
            $this->createMock(ilCtrl::class)
        );

        $form = $this->invokeProtectedMethod($gui, 'initEditEntriesForm', [['common#:#access']]);

        $comment_item = $this->findItemByPostVar($form, 'comment[0]');
        self::assertInstanceOf(ilTextInputGUI::class, $comment_item);
        // Not an ilTextAreaInputGUI - ilTextInputGUI is not a subclass of it,
        // so this instanceof check is a genuinely distinct assertion, not a
        // tautology of the check above.
        self::assertNotInstanceOf(ilTextAreaInputGUI::class, $comment_item);
        self::assertSame(250, $comment_item->getMaxLength());
        self::assertSame('Existing remark', $comment_item->getValue());

        $translation_item = $this->findItemByPostVar($form, 'translation[0]');
        self::assertInstanceOf(ilTextAreaInputGUI::class, $translation_item);
        self::assertSame('Access', $translation_item->getValue());
    }

    /**
     * Regression test for the reflected-XSS blocker: a forged/nonexistent
     * name in $names (e.g. a query value that never went through
     * getFilteredEntryNames()) must be silently dropped BEFORE anything is
     * rendered - it must not produce a field/title of its own, and it must
     * not count towards "expected_entry_count" either (that count is read
     * back verbatim by saveEditedEntriesObject()'s own guard, so it must
     * reflect the FILTERED, not the raw, selection).
     */
    public function testInitEditEntriesFormSilentlyDropsNamesThatDoNotExistInTranslations(): void
    {
        $this->stubDicForRealFormConstruction();

        $object = $this->createFakeLanguageObject('de');
        $object->method('getAllValues')->willReturn([
            'common#:#access' => 'Access',
            'common#:#other' => 'Other',
        ]);
        $object->method('getEffectiveRemarks')->willReturn([]);

        $gui = $this->createGuiForInitEditEntriesForm(
            $object,
            $this->createLanguageMockReturningTopicAsIs(),
            false,
            $this->createMock(ilCtrl::class)
        );

        $form = $this->invokeProtectedMethod($gui, 'initEditEntriesForm', [[
            'common#:#access',
            'forged#:#nonexistent',
            'common#:#other',
        ]]);

        // expected_entry_count (1 item) + 3 items per surviving name (hidden
        // entry_name, translation textarea, comment hidden) x 2 surviving
        // names = 7 - the forged name must not add any item of its own.
        self::assertCount(1 + 3 * 2, $form->getItems());

        $entry_name_values = [];
        foreach ($form->getItems() as $item) {
            if (method_exists($item, 'getPostVar') && str_starts_with($item->getPostVar(), 'entry_name[')) {
                $entry_name_values[] = $item->getValue();
            }
        }
        self::assertSame(['common#:#access', 'common#:#other'], $entry_name_values);

        $expected_count_item = $this->findItemByPostVar($form, 'expected_entry_count');
        self::assertSame('2', $expected_count_item->getValue());
    }

    // -----------------------------------------------------------------
    // saveEditedEntriesObject()
    //
    // ilObjLanguageExt::_saveValues() (called only on the SUCCESS path,
    // after the guard below passes) is a hard-coded static call
    // (`ilObjLanguageExt::_saveValues(...)`, not `static::`) - it cannot be
    // intercepted via a subclass/partial mock, and it transitively reaches
    // into several further static collaborators (ilLanguageFile disk I/O,
    // ilCachedLanguage's global cache, ilObjLanguage::replaceLangEntry's own
    // DB calls) that would need a very heavy, unrelated fixture to exercise
    // safely. Both tests below therefore exercise only the guard itself
    // (the ACTUAL new behaviour being regression-tested here) - they rely on
    // $GLOBALS['DIC'] being deliberately left unset: if the guard ever
    // failed to abort and execution fell through to _saveValues(), that call
    // would fatal-error (undefined global $DIC) rather than silently pass,
    // which is exactly the safety net a positive/control test would
    // otherwise need real DB/file fixtures for. See this file's final test
    // report for the explicitly acknowledged coverage gap this leaves open.
    //
    // INVESTIGATED (Review Round 3): a genuine positive/control test - a
    // matching entry_name/translation/comment/expected_entry_count selection
    // actually reaching ilObjLanguageExt::_saveValues() with the correctly
    // built $save_array/$remarks_array (in particular: an UNCHANGED remark
    // from a hidden "comment[$idx]" field surviving the round-trip, the
    // actual regression this component's earlier review round was originally
    // about) - is NOT practicably writable against the current production
    // code, for two independent reasons:
    //
    // 1. Non-interceptability: `ilObjLanguageExt::_saveValues(...)` is a
    //    hard, fully-qualified static call, not `static::_saveValues(...)`.
    //    PHPUnit mocks work by subclassing; a subclass cannot override a
    //    call another method resolves via its own literal class name, so no
    //    partial mock of ilObjLanguageExtGUI or of ilObjLanguageExt can
    //    intercept or observe this particular call site at all.
    // 2. No cross-class checkpoint to fall back on either: $save_array and
    //    $remarks_array (the very values the task asks to verify) are local
    //    variables built entirely INSIDE saveEditedEntriesObject() and handed
    //    straight to _saveValues() in the same statement - unlike, say,
    //    initEditEntriesForm()'s $names filtering (which is observable via
    //    the returned ilPropertyFormGUI's items), there is no return value,
    //    property, or collaborator call in between to assert against. The
    //    only way to observe them is to let _saveValues() itself actually
    //    run - which pulls in the full chain below.
    //
    // Actually letting _saveValues() run (rather than intercepting it) was
    // also investigated and rejected as impractical for a unit test: reading
    // its body (class.ilObjLanguageExt.php) shows it (a) resolves and reads a
    // REAL global .lang file from disk via ilLanguageFile::_getGlobalLanguageFile(),
    // (b) issues two further real DB reads of its own (self::_getValues()/
    // _getRemarks()), (c) calls the static ilObjLanguage::replaceLangEntry()/
    // replaceLangModule() (each with their own further DB calls and exact SQL
    // this test would have to reproduce faithfully in a mock ilDBInterface to
    // avoid a silent no-op), and (d) flushes ilCachedLanguage's global,
    // process-wide cache. Faking all of this correctly would amount to
    // re-implementing large parts of the language persistence layer inside
    // the test itself - fragile, and testing far more than this GUI change.
    //
    // Suggested follow-up (out of this test-engineer's scope to implement
    // here): extract the "persist these values" step behind an injectable
    // collaborator - e.g. a small `SaveLanguageValues`-style
    // Activity/service, analogous to how AddLanguageEntry/
    // SetLanguageTranslationEnabled already replaced other direct static
    // calls in this same GUI class (see the constructor's own docblock) -
    // so ilObjLanguageExtGUI depends on an interface it actually receives in
    // its constructor rather than a hard-coded static class name. That alone
    // would make both the interception problem (1) and the round-trip
    // verification (2) straightforward: a mocked collaborator could then
    // assert directly on the $save_array/$remarks_array it was called with.
    // -----------------------------------------------------------------

    /**
     * @return ilObjLanguageExtGUI&MockObject
     */
    private function createGuiForSaveEditedEntries(
        \ILIAS\HTTP\GlobalHttpState $http,
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ctrl,
        ilLanguage $lng,
        ilObjLanguageExt $object
    ): ilObjLanguageExtGUI {
        $gui = $this->getMockBuilder(ilObjLanguageExtGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->inject($gui, 'http', $http);
        $this->inject($gui, 'ui_factory', $this->createMock(\ILIAS\UI\Factory::class));
        $this->inject($gui, 'tpl', $tpl);
        $this->inject($gui, 'ctrl', $ctrl);
        $this->inject($gui, 'lng', $lng);
        $this->inject($gui, 'object', $object);

        return $gui;
    }

    public function testSaveEditedEntriesObjectAbortsWithFailureMessageAndRedirectsWhenEntryCountDoesNotMatchExpectedCount(): void
    {
        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())->method('setOnScreenMessage')->with(
            'failure',
            'language_too_many_entries_selected',
            true
        );

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect')->with(
            $this->isInstanceOf(ilObjLanguageExtGUI::class),
            'view'
        );

        $http = $this->mockHttpWithParsedBody([
            'entry_name' => ['common#:#access', 'common#:#other'],
            'translation' => ['A', 'B'],
            'comment' => ['', ''],
            // 2 real entries, but the form claims to have rendered 3 -
            // exactly the max_input_vars truncation scenario this guard
            // protects against.
            'expected_entry_count' => '3',
        ]);

        $gui = $this->createGuiForSaveEditedEntries(
            $http,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs(),
            $this->createFakeLanguageObject('de')
        );

        $gui->saveEditedEntriesObject();
    }

    /**
     * A completely missing "expected_entry_count" (e.g. a hand-crafted POST,
     * or an older cached form) must default to -1, which can never equal a
     * real (>= 0) entry count - so this aborts exactly like an explicit
     * mismatch does, rather than silently proceeding to save.
     */
    public function testSaveEditedEntriesObjectAbortsWhenExpectedEntryCountIsMissingFromThePost(): void
    {
        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())->method('setOnScreenMessage')->with(
            'failure',
            'language_too_many_entries_selected',
            true
        );

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect')->with(
            $this->isInstanceOf(ilObjLanguageExtGUI::class),
            'view'
        );

        $http = $this->mockHttpWithParsedBody([
            'entry_name' => ['common#:#access'],
            'translation' => ['A'],
            'comment' => [''],
            // no 'expected_entry_count' key at all
        ]);

        $gui = $this->createGuiForSaveEditedEntries(
            $http,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs(),
            $this->createFakeLanguageObject('de')
        );

        $gui->saveEditedEntriesObject();
    }

    /**
     * Boundary/regression test: count($entry_names) must count actual array
     * ELEMENTS, not (highest key + 1) - a POST with gapped array indices
     * (e.g. produced by a partially-truncated max_input_vars submission)
     * has exactly 2 real entries at keys 0 and 5. Comparing against 6 (what
     * a max-index-based miscount would produce) must still be treated as a
     * mismatch, proving the guard is not fooled by index gaps in either
     * direction.
     */
    public function testSaveEditedEntriesObjectCountGuardCountsArrayElementsNotTheHighestIndexWhenKeysHaveGaps(): void
    {
        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())->method('setOnScreenMessage')->with(
            'failure',
            'language_too_many_entries_selected',
            true
        );

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect');

        $http = $this->mockHttpWithParsedBody([
            'entry_name' => [0 => 'common#:#access', 5 => 'common#:#other'],
            'translation' => [0 => 'A', 5 => 'B'],
            'comment' => [0 => '', 5 => ''],
            'expected_entry_count' => '6',
        ]);

        $gui = $this->createGuiForSaveEditedEntries(
            $http,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs(),
            $this->createFakeLanguageObject('de')
        );

        $gui->saveEditedEntriesObject();
    }

    /**
     * Regression test: the count guard checks entry_name/translation/comment
     * INDEPENDENTLY - not merely all three against each other, and not just
     * entry_name against expected_entry_count. A POST where exactly ONE of
     * the three arrays is short (here: "translation" has only 2 elements
     * while "entry_name"/"comment" both have the full 3, matching
     * expected_entry_count) must still abort - the max_input_vars truncation
     * scenario this guard protects against can just as well cut the POST off
     * in the middle of "translation[$idx]" while "entry_name[$idx]" and a
     * LATER "comment[$idx]" (per initEditEntriesForm()'s own field order:
     * entry_name, translation, comment - all three per entry, but truncation
     * can hit anywhere) still made it through.
     */
    public function testSaveEditedEntriesObjectAbortsWhenOnlyOneOfTheThreePostArraysIsShorterThanExpected(): void
    {
        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())->method('setOnScreenMessage')->with(
            'failure',
            'language_too_many_entries_selected',
            true
        );

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect')->with(
            $this->isInstanceOf(ilObjLanguageExtGUI::class),
            'view'
        );

        $http = $this->mockHttpWithParsedBody([
            'entry_name' => ['common#:#access', 'common#:#other', 'common#:#third'],
            // Only 2 elements - short by exactly one, while entry_name/
            // comment both match expected_entry_count (3).
            'translation' => ['A', 'B'],
            'comment' => ['', '', ''],
            'expected_entry_count' => '3',
        ]);

        // $GLOBALS['DIC'] is deliberately left unset (see this section's own
        // docblock above) - if the guard failed to catch this mismatch and
        // fell through to ilObjLanguageExt::_saveValues(), that call would
        // fatal-error (undefined global $DIC) rather than silently save a
        // truncated/misaligned selection.
        $gui = $this->createGuiForSaveEditedEntries(
            $http,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs(),
            $this->createFakeLanguageObject('de')
        );

        $gui->saveEditedEntriesObject();
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
