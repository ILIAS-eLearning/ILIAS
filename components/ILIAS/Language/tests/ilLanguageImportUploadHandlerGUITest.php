<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Provider\Configuration\LocalConfig;
use ILIAS\Filesystem\Provider\FlySystem\FlySystemLocalFilesystemFactory;
use ILIAS\FileUpload\Collection\ImmutableStringMap;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\FileUpload;
use ILIAS\FileUpload\Handler\HandlerResult;
use PHPUnit\Framework\TestCase;

/**
 * The temp filesystem is a real one below a scratch directory, the session is the real, static ilSession.
 */
class ilLanguageImportUploadHandlerGUITest extends TestCase
{
    private const string SESSION_KEY = 'lang_ext_import_uploads';

    private string $root;
    private Filesystem $temp;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/lng_upload_test_' . bin2hex(random_bytes(6));
        mkdir($this->root);
        $this->temp = (new FlySystemLocalFilesystemFactory())->getInstance(new LocalConfig($this->root));
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        unset($GLOBALS['DIC']);
        $this->removeDir($this->root);
    }

    private function removeDir(string $dir): void
    {
        foreach (glob($dir . '/*') ?: [] as $entry) {
            is_dir($entry) ? $this->removeDir($entry) : unlink($entry);
        }
        rmdir($dir);
    }

    private function createHandler(?FileUpload $upload = null, ?Filesystem $temp = null): ilLanguageImportUploadHandlerGUI
    {
        $handler = (new ReflectionClass(ilLanguageImportUploadHandlerGUI::class))->newInstanceWithoutConstructor();
        (new ReflectionProperty(ilLanguageImportUploadHandlerGUI::class, 'temp_system'))->setValue($handler, $temp ?? $this->temp);
        if ($upload !== null) {
            (new ReflectionProperty(\ILIAS\FileUpload\Handler\AbstractCtrlAwareUploadHandler::class, 'upload'))
                ->setValue($handler, $upload);
        }

        return $handler;
    }

    /** A file in the temp filesystem, issued to this session only if $own. */
    private function storeUpload(string $identifier, string $name, bool $own): void
    {
        $this->temp->createDir($identifier);
        $this->temp->put($identifier . '/' . $name, 'content');
        if ($own) {
            $_SESSION[self::SESSION_KEY] = [...($_SESSION[self::SESSION_KEY] ?? []), $identifier];
        }
    }

    public function testForeignAndUnknownIdentifiersAreNeitherReadNorDeleted(): void
    {
        $this->storeUpload('own', 'own.lang', true);
        $this->storeUpload('foreign', 'foreign.lang', false);
        $handler = $this->createHandler();

        self::assertSame('own.lang', $handler->getFileName('own'));
        foreach (['foreign', 'unknown', '', '../foreign'] as $identifier) {
            self::assertNull($handler->getFileName($identifier), $identifier);
            self::assertNull($handler->getInfoResult($identifier), $identifier);
            self::assertNull($handler->copyToLocalFile($identifier), $identifier);
        }
        self::assertSame([], $handler->getInfoForExistingFiles(['foreign', 'unknown']));

        $handler->discard('foreign');
        $handler->discard('unknown');

        self::assertTrue($this->temp->hasDir('foreign'));
        self::assertSame(['own'], $_SESSION[self::SESSION_KEY]);
    }

    public function testDiscardRemovesTheDirectoryAndForgetsTheIdentifier(): void
    {
        $this->storeUpload('a', 'a.lang', true);
        $this->storeUpload('b', 'b.lang', true);
        $handler = $this->createHandler();

        $handler->discard('a');

        self::assertFalse($this->temp->hasDir('a'));
        self::assertTrue($this->temp->hasDir('b'));
        self::assertSame(['b'], $_SESSION[self::SESSION_KEY]);
        self::assertNull($handler->getFileName('a'));
    }

    public function testDiscardAllKeepsTheExceptionsAndLeavesForeignUploadsAlone(): void
    {
        $this->storeUpload('keep', 'keep.lang', true);
        $this->storeUpload('drop1', 'drop1.lang', true);
        $this->storeUpload('drop2', 'drop2.lang', true);
        $this->storeUpload('foreign', 'foreign.lang', false);
        $handler = $this->createHandler();

        $handler->discardAll(['keep']);

        self::assertTrue($this->temp->hasDir('keep'));
        self::assertFalse($this->temp->hasDir('drop1'));
        self::assertFalse($this->temp->hasDir('drop2'));
        self::assertTrue($this->temp->hasDir('foreign'));
        self::assertSame(['keep'], array_values($_SESSION[self::SESSION_KEY]));

        $handler->discardAll();

        self::assertFalse($this->temp->hasDir('keep'));
        self::assertTrue($this->temp->hasDir('foreign'));
        self::assertSame([], $_SESSION[self::SESSION_KEY]);
    }

    public function testAnIssuedIdentifierWithoutFileIsNotResolved(): void
    {
        $_SESSION[self::SESSION_KEY] = ['gone'];
        $this->temp->createDir('empty');
        $_SESSION[self::SESSION_KEY][] = 'empty';
        $handler = $this->createHandler();

        self::assertNull($handler->getFileName('gone'));
        self::assertNull($handler->getFileName('empty'));
    }

    public function testASuccessfulUploadRemembersItsIdentifierInTheSession(): void
    {
        $source = $this->root . '/source.lang';
        file_put_contents($source, 'payload');
        $GLOBALS['DIC'] = new \ILIAS\DI\Container();
        $sanitizer = new class () {
            public function sanitize(string $name): string
            {
                return $name;
            }
        };
        $GLOBALS['DIC']['filesystem.security.sanitizing.filename'] = $sanitizer;

        $upload = $this->createStub(FileUpload::class);
        $upload->method('hasBeenProcessed')->willReturn(true);
        $upload->method('getResults')->willReturn([new UploadResult(
            'ilias_de.lang',
            7,
            'text/plain',
            $this->createStub(ImmutableStringMap::class),
            new ProcessingStatus(ProcessingStatus::OK, 'ok'),
            $source
        )]);
        $handler = $this->createHandler($upload);
        $method = new ReflectionMethod($handler, 'getUploadResult');

        $this->temp->createDir('previous');
        $_SESSION[self::SESSION_KEY] = ['previous'];
        $result = $method->invoke($handler);

        self::assertSame(HandlerResult::STATUS_OK, $result->getStatus());
        $identifier = $result->getFileIdentifier();
        self::assertNotSame('', $identifier);
        self::assertSame(['previous', $identifier], $_SESSION[self::SESSION_KEY]);
        self::assertSame('ilias_de.lang', $handler->getFileName($identifier));
    }

    public function testARejectedUploadIsReportedAndRemembersNothing(): void
    {
        $upload = $this->createStub(FileUpload::class);
        $upload->method('hasBeenProcessed')->willReturn(true);
        $upload->method('getResults')->willReturn([new UploadResult(
            'x.lang',
            1,
            'text/plain',
            $this->createStub(ImmutableStringMap::class),
            new ProcessingStatus(ProcessingStatus::REJECTED, 'too big'),
            $this->root . '/none'
        )]);
        $handler = $this->createHandler($upload);

        $result = (new ReflectionMethod($handler, 'getUploadResult'))->invoke($handler);

        self::assertSame(HandlerResult::STATUS_FAILED, $result->getStatus());
        self::assertSame('too big', $result->getMessage());
        self::assertArrayNotHasKey(self::SESSION_KEY, $_SESSION);
    }

    /** @param \Closure(string): string $sanitize the filename sanitizer of the DIC */
    private function runUpload(string $name, int $size, \Closure $sanitize): HandlerResult
    {
        $source = $this->root . '/source.lang';
        file_put_contents($source, 'payload');
        $GLOBALS['DIC'] = new \ILIAS\DI\Container();
        $GLOBALS['DIC']['filesystem.security.sanitizing.filename'] = new class ($sanitize) {
            public function __construct(private readonly \Closure $sanitize)
            {
            }

            public function sanitize(string $name): string
            {
                return ($this->sanitize)($name);
            }
        };
        $upload = $this->createStub(FileUpload::class);
        $upload->method('hasBeenProcessed')->willReturn(true);
        $upload->method('getResults')->willReturn([new UploadResult(
            $name,
            $size,
            'text/plain',
            $this->createStub(ImmutableStringMap::class),
            new ProcessingStatus(ProcessingStatus::OK, 'ok'),
            $source
        )]);
        $handler = $this->createHandler($upload);

        return (new ReflectionMethod($handler, 'getUploadResult'))->invoke($handler);
    }

    public function testAnUploadOverTheServerSideSizeLimitIsRefusedAndStoresNothing(): void
    {
        // the limit is checked in the browser only, the declared size of the result is what counts
        $result = $this->runUpload('big.lang', ilLanguageImportUploadHandlerGUI::MAX_FILE_SIZE + 1, static fn(string $n): string => $n);

        self::assertSame(HandlerResult::STATUS_FAILED, $result->getStatus());
        self::assertSame('', $result->getFileIdentifier());
        self::assertArrayNotHasKey(self::SESSION_KEY, $_SESSION);
        self::assertSame(['source.lang'], array_map(static fn($m): string => $m->getPath(), $this->temp->listContents('', true)));
    }

    public function testAnUploadExactlyAtTheSizeLimitIsAccepted(): void
    {
        $result = $this->runUpload('edge.lang', ilLanguageImportUploadHandlerGUI::MAX_FILE_SIZE, static fn(string $n): string => $n);

        self::assertSame(HandlerResult::STATUS_OK, $result->getStatus());
        self::assertSame([$result->getFileIdentifier()], $_SESSION[self::SESSION_KEY]);
    }

    public function testAFileNameThatSanitizesToNothingGetsTheFallbackName(): void
    {
        $result = $this->runUpload('../', 7, static fn(string $n): string => '');

        self::assertSame(HandlerResult::STATUS_OK, $result->getStatus());
        $handler = $this->createHandler();
        self::assertSame('language.lang', $handler->getFileName($result->getFileIdentifier()));
    }

    /** @return array<string, array{string, string}> name given by the browser => display name */
    public static function clientFileNames(): array
    {
        return [
            'own export' => ['ilias_de_x.lang.local', 'ilias_de_x.lang.local'],
            'ampersand and markup' => ['ilias_de_x&y <b>.lang.local', 'ilias_de_x&y <b>.lang.local'],
            'windows path' => ['C:\\Users\\me\\ilias_de.lang.global', 'ilias_de.lang.global'],
            'control characters' => ["ilias\x00_de\n.lang", 'ilias_de.lang'],
            'too long' => [str_repeat('a', 250) . '.lang', str_repeat('a', 200)],
            'only dots' => ['..', 'language.lang'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('clientFileNames')]
    public function testTheFileIsStoredUnderAFixedNameAndTheBrowserNameIsKeptForDisplay(string $client_name, string $display_name): void
    {
        // getValidFilename() must play no part anymore: a sanitizer that destroys every name
        $result = $this->runUpload($client_name, 7, static fn(string $n): string => 'mangled.sec');
        $identifier = $result->getFileIdentifier();
        $handler = $this->createHandler();

        self::assertSame(HandlerResult::STATUS_OK, $result->getStatus());
        self::assertSame([$identifier . '/language.lang'], array_map(
            static fn($m): string => $m->getPath(),
            $this->temp->listContents($identifier)
        ));
        self::assertSame($display_name, $handler->getFileName($identifier));
        self::assertSame(htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8'), $handler->getInfoResult($identifier)->getName());

        $handler->discard($identifier);
        self::assertSame([], $_SESSION['lang_ext_import_upload_names']);
    }

    public function testTheInfoResultEscapesTheFileNameButTheStoredNameStaysRaw(): void
    {
        $this->storeUpload('own', '<img src=x onerror=alert(1)>.lang', true);
        $handler = $this->createHandler();

        $info = $handler->getInfoResult('own');

        self::assertSame('&lt;img src=x onerror=alert(1)&gt;.lang', $info->getName());
        self::assertSame(7, $info->getSize());
        self::assertSame('<img src=x onerror=alert(1)>.lang', $handler->getFileName('own'));
    }

    /** The detector gives up on the unknown extension .lang; any other detected type is passed on. */
    public function testTheMimeTypeFallsBackToPlainTextOnlyWhenTheDetectionFails(): void
    {
        $this->storeUpload('own', 'own.lang', true);
        $real = $this->temp;
        foreach ([new IOException('unknown'), 'application/x-detected'] as $detected) {
            $fs = $this->createStub(Filesystem::class);
            $fs->method('hasDir')->willReturnCallback(static fn(string $d): bool => $real->hasDir($d));
            $fs->method('listContents')->willReturnCallback(static fn(string $d): array => $real->listContents($d));
            $fs->method('getSize')->willReturnCallback(static fn(string $p, $unit) => $real->getSize($p, $unit));
            $detected instanceof IOException
                ? $fs->method('getMimeType')->willThrowException($detected)
                : $fs->method('getMimeType')->willReturn($detected);
            $handler = $this->createHandler(null, $fs);

            $expected = $detected instanceof IOException ? 'text/plain' : $detected;
            self::assertSame($expected, $handler->getInfoResult('own')->getMimeType());
        }
    }
}
