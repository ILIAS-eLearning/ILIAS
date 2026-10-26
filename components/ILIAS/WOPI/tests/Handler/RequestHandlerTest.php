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

namespace ILIAS\WOPI\Handler;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\Stub;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\FileDelivery\Token\DataSigner;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKey;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKeyRotation;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Manager\Manager;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Information\Information;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\WOPI\Lock\Lock;
use ILIAS\WOPI\Lock\LockRepository;
use ILIAS\WOPI\Lock\LockService;
use ILIAS\WOPI\Discovery\ProofKey;
use ILIAS\WOPI\Discovery\ProofKeys;
use ILIAS\HTTP\Message\MessageFactoryImpl;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RequestHandlerTest extends TestCase
{
    private const RID = 'f4d61153-dfd6-4252-80d2-a4d6b7ff6f39';
    private const USER = 6;

    private DataSigner $signer;
    private LockRepository $lock_repository;
    private Manager&Stub $manager;
    /** @var string[] the IRSS calls which change the resource, in order */
    private array $irss_calls = [];
    private int $current_size = 1234;
    private ?int $published_size = null;
    private int $published_created = 0;
    private ?ProofKeys $proof_keys = null;
    private int $max_upload_size = 0;
    private ?\Closure $refresh_proof_keys = null;
    /** @var ResourceStakeholder[] */
    private array $resource_stakeholders = [];
    private ?ResourceStakeholder $appended_stakeholder = null;
    private ?\Throwable $resource_failure = null;

    protected function setUp(): void
    {
        if (!defined('ILIAS_HTTP_PATH')) {
            define('ILIAS_HTTP_PATH', 'https://ilias.example.org');
        }
        // GetFileInfoResponse looks up the name of the user
        $GLOBALS['DIC'] ??= new \ILIAS\DI\Container();
        if (!isset($GLOBALS['DIC']['ilDB'])) {
            $db = $this->createStub(\ilDBInterface::class);
            $db->method('queryF')->willReturn($this->createStub(\ilDBStatement::class));
            $db->method('fetchAssoc')->willReturn(
                ['firstname' => 'Ada', 'lastname' => 'Lovelace', 'title' => '', 'login' => 'ada']
            );
            $GLOBALS['DIC']['ilDB'] = static fn(): \ilDBInterface => $db;
        }

        $this->signer = new DataSigner(new SecretKeyRotation(new SecretKey('a-secret-for-testing-only')));
        $this->lock_repository = new class () implements LockRepository {
            /** @var array<string, Lock> */
            public array $locks = [];

            public function find(string $resource_id): ?Lock
            {
                return $this->locks[$resource_id] ?? null;
            }

            public function store(string $resource_id, Lock $lock): void
            {
                $this->locks[$resource_id] = $lock;
            }

            public function delete(string $resource_id): void
            {
                unset($this->locks[$resource_id]);
            }
        };
        $this->published_created = time() - 3600;
        $this->manager = $this->buildManager();
    }

    private function revision(int $size, int $created): Revision
    {
        $information = $this->createStub(Information::class);
        $information->method('getSize')->willReturn($size);
        $information->method('getSuffix')->willReturn('docx');
        $information->method('getCreationDate')->willReturn((new \DateTimeImmutable())->setTimestamp($created));

        $revision = $this->createStub(Revision::class);
        $revision->method('getInformation')->willReturn($information);
        $revision->method('getTitle')->willReturn('minutes.docx');
        $revision->method('getVersionNumber')->willReturn(2);
        $revision->method('getOwnerId')->willReturn(self::USER);

        return $revision;
    }

    private function buildManager(): Manager&Stub
    {
        $manager = $this->createStub(Manager::class);
        $manager->method('find')->willReturnCallback(
            static fn(string $id): ?ResourceIdentification => $id === self::RID ? new ResourceIdentification($id) : null
        );
        $manager->method('getResource')->willReturnCallback(function (): StorableResource {
            if ($this->resource_failure !== null) {
                throw $this->resource_failure;
            }
            $resource = $this->createStub(StorableResource::class);
            $resource->method('getCurrentRevisionIncludingDraft')->willReturn(
                $this->revision($this->current_size, time())
            );
            $resource->method('getCurrentRevision')->willReturn(
                $this->revision($this->published_size ?? $this->current_size, $this->published_created)
            );
            $resource->method('getStakeholders')->willReturn($this->resource_stakeholders);
            return $resource;
        });
        $manager->method('appendNewRevisionFromStream')->willReturnCallback(
            function ($rid, $stream, $stakeholder, $title, bool $draft): Revision {
                $stream->rewind();
                $this->appended_stakeholder = $stakeholder;
                $this->irss_calls[] = 'append:' . ($draft ? 'draft' : 'published') . ':' . $stream->getContents();
                return $this->revision(3, time());
            }
        );
        $manager->method('publish')->willReturnCallback(function (): void {
            $this->irss_calls[] = 'publish';
        });

        return $manager;
    }

    private function token(bool $editable, ?bool $user_can_write = null, string $rid = self::RID): string
    {
        return $this->signer->sign([
            'resource_id' => $rid,
            'user_id' => self::USER,
            'stakeholder' => 'ilObjFileStakeholder',
            'editable' => $editable,
            'user_can_write' => $user_can_write ?? $editable,
        ], 'wopi', new \DateTimeImmutable('now + 1 hour'));
    }

    /**
     * @param array<string, string> $headers
     */
    private function process(
        string $method,
        string $path,
        ?string $token,
        array $headers = [],
        string $body = '',
        int $saving_interval = 0,
        bool $activated = true
    ): ResponseInterface {
        $url = 'https://ilias.example.org/wopi/index.php/' . $path;
        if ($token !== null) {
            $url .= '?access_token=' . rawurlencode($token);
        }
        $messages = new MessageFactoryImpl();
        $message = $messages->request($method, $url, $headers, $body);
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn($message->getMethod());
        $request->method('getUri')->willReturn($message->getUri());
        $request->method('getBody')->willReturn($message->getBody());
        $request->method('getHeader')->willReturnCallback(
            static fn(string $name): array => $message->getHeader($name)
        );
        $request->method('getQueryParams')->willReturn(
            $token === null ? [] : ['access_token' => $token]
        );

        $http = $this->createStub(GlobalHttpState::class);
        $http->method('request')->willReturn($request);
        $http->method('response')->willReturn($messages->response());

        $irss = $this->createStub(\ILIAS\ResourceStorage\Services::class);
        $irss->method('manage')->willReturn($this->manager);

        return (new RequestHandler(
            $http,
            $irss,
            $this->signer,
            new LockService($this->lock_repository),
            new ProofValidator($this->proof_keys),
            'https://ilias.example.org',
            $activated,
            $saving_interval,
            $this->max_upload_size,
            $this->refresh_proof_keys
        ))->process();
    }

    private function files(string $action = ''): string
    {
        return 'files/' . self::RID . ($action === '' ? '' : '/' . $action);
    }

    public function testRequestWithoutTokenIsUnauthorized(): void
    {
        $this->assertSame(401, $this->process('GET', $this->files(), null)->getStatusCode());
    }

    public function testRequestWithForgedTokenIsUnauthorized(): void
    {
        // signed with a key ILIAS does not know
        $forged = (new DataSigner(new SecretKeyRotation(new SecretKey('another-secret'))))->sign([
            'resource_id' => self::RID,
            'user_id' => self::USER,
            'editable' => true,
            'user_can_write' => true,
        ], 'wopi', new \DateTimeImmutable('now + 1 hour'));

        $this->assertSame(401, $this->process('GET', $this->files(), $forged)->getStatusCode());
    }

    public function testTokenIsOnlyValidForItsResource(): void
    {
        $token = $this->token(true, true, 'another-resource');

        $this->assertSame(401, $this->process('GET', $this->files(), $token)->getStatusCode());
    }

    public function testUnknownResourceIsNotFound(): void
    {
        $token = $this->token(true, true, 'unknown');

        $this->assertSame(404, $this->process('GET', 'files/unknown', $token)->getStatusCode());
    }

    public function testRequestsAreRefusedWhileWOPIIsNotActivated(): void
    {
        $response = $this->process('GET', $this->files(), $this->token(true), activated: false);

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testUnknownRouteIsNotFound(): void
    {
        $response = $this->process('GET', 'folders/' . self::RID, $this->token(true));

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testErrorsDoNotDiscloseTheirDetails(): void
    {
        $this->resource_failure = new \RuntimeException('/var/www/html/secret/path');

        $response = $this->process('GET', $this->files(), $this->token(true));

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('Internal server error', $response->getHeaderLine('X-WOPI-ServerError'));
        $this->assertStringNotContainsString('secret', (string) $response->getBody());
    }

    public function testCheckFileInfoAnnouncesLockSupport(): void
    {
        $response = $this->process('GET', $this->files(), $this->token(true));
        $info = json_decode((string) $response->getBody(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($info['SupportsLocks']);
        $this->assertTrue($info['SupportsGetLock']);
    }

    public function testViewerSeesThePublishedRevision(): void
    {
        $this->published_size = 1000;

        $response = $this->process('GET', $this->files(), $this->token(false));

        $this->assertSame(1000, json_decode((string) $response->getBody(), true)['Size']);
    }

    /**
     * A WOPI client tries to initialise an empty file, which a viewer may not.
     */
    public function testViewerSeesTheDraftOfANewEmptyDocument(): void
    {
        $this->published_size = 0;

        $response = $this->process('GET', $this->files(), $this->token(false));

        $this->assertSame(1234, json_decode((string) $response->getBody(), true)['Size']);
    }

    public function testUnknownOperationIsNotImplemented(): void
    {
        $response = $this->process('POST', $this->files(), $this->token(true), ['X-WOPI-Override' => 'RENAME_FILE']);

        $this->assertSame(501, $response->getStatusCode());
    }

    public static function writingOperations(): array
    {
        return [
            'PutFile' => ['contents', 'PUT'],
            'Lock' => ['', 'LOCK'],
            'RefreshLock' => ['', 'REFRESH_LOCK'],
            'Unlock' => ['', 'UNLOCK'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('writingOperations')]
    public function testViewerTokenMustNotWrite(string $action, string $override): void
    {
        $this->current_size = 0;
        foreach ([$this->token(false, true), $this->token(true, false)] as $token) {
            $response = $this->process(
                'POST',
                $this->files($action),
                $token,
                ['X-WOPI-Override' => $override, 'X-WOPI-Lock' => 'A'],
                'payload'
            );

            $this->assertSame(401, $response->getStatusCode());
        }
        $this->assertSame([], $this->irss_calls);
        $this->assertSame([], $this->lock_repository->locks);
    }

    public function testViewerTokenMayReadTheLock(): void
    {
        $this->lock_repository->store(self::RID, new Lock('A', time() + 600, self::USER));

        $response = $this->process('POST', $this->files(), $this->token(false), ['X-WOPI-Override' => 'GET_LOCK']);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('A', $response->getHeaderLine('X-WOPI-Lock'));
    }

    public function testLockConflictTellsTheCurrentLock(): void
    {
        $token = $this->token(true);
        $lock = fn(string $value): ResponseInterface => $this->process(
            'POST',
            $this->files(),
            $token,
            ['X-WOPI-Override' => 'LOCK', 'X-WOPI-Lock' => $value]
        );

        $this->assertSame(200, $lock('A')->getStatusCode());
        $conflict = $lock('B');
        $this->assertSame(409, $conflict->getStatusCode());
        $this->assertSame('A', $conflict->getHeaderLine('X-WOPI-Lock'));
        $this->assertTrue($conflict->hasHeader('X-WOPI-LockFailureReason'));
    }

    public function testUnlockAndRelock(): void
    {
        $this->lock_repository->store(self::RID, new Lock('A', time() + 600, self::USER));

        $response = $this->process(
            'POST',
            $this->files(),
            $this->token(true),
            ['X-WOPI-Override' => 'LOCK', 'X-WOPI-Lock' => 'B', 'X-WOPI-OldLock' => 'A']
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('B', $this->lock_repository->find(self::RID)->value);
    }

    public function testPutFileOnUnlockedFileWithContentConflicts(): void
    {
        $response = $this->process('POST', $this->files('contents'), $this->token(true), ['X-WOPI-Override' => 'PUT'], 'payload');

        $this->assertSame(409, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('X-WOPI-Lock'));
        $this->assertSame('', $response->getHeaderLine('X-WOPI-Lock'));
        $this->assertSame([], $this->irss_calls);
    }

    public function testPutFileWithOtherLockConflicts(): void
    {
        $this->lock_repository->store(self::RID, new Lock('A', time() + 600, self::USER));

        $response = $this->process(
            'POST',
            $this->files('contents'),
            $this->token(true),
            ['X-WOPI-Override' => 'PUT', 'X-WOPI-Lock' => 'B'],
            'payload'
        );

        $this->assertSame(409, $response->getStatusCode());
        $this->assertSame('A', $response->getHeaderLine('X-WOPI-Lock'));
        $this->assertSame([], $this->irss_calls);
    }

    public function testPutFileOnEmptyFileNeedsNoLock(): void
    {
        $this->current_size = 0;

        $response = $this->process('POST', $this->files('contents'), $this->token(true), ['X-WOPI-Override' => 'PUT'], 'payload');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['append:draft:payload'], $this->irss_calls);
    }

    public function testPutFileWithLockStoresDraft(): void
    {
        $this->lock_repository->store(self::RID, new Lock('A', time() + 600, self::USER));

        $response = $this->process(
            'POST',
            $this->files('contents'),
            $this->token(true),
            ['X-WOPI-Override' => 'PUT', 'X-WOPI-Lock' => 'A'],
            'payload'
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['append:draft:payload'], $this->irss_calls);
    }

    private function stakeholder(string $id, string $class_name): ResourceStakeholder
    {
        $stakeholder = $this->createStub(ResourceStakeholder::class);
        $stakeholder->method('getId')->willReturn($id);
        $stakeholder->method('getFullyQualifiedClassName')->willReturn($class_name);
        $stakeholder->method('canBeAccessedByCurrentUser')->willReturn(false);
        return $stakeholder;
    }

    private function putFile(): ResponseInterface
    {
        $this->lock_repository->store(self::RID, new Lock('A', time() + 600, self::USER));

        return $this->process(
            'POST',
            $this->files('contents'),
            $this->token(true),
            ['X-WOPI-Override' => 'PUT', 'X-WOPI-Lock' => 'A'],
            'payload'
        );
    }

    public function testPutFileStoresOnBehalfOfTheStakeholderOfTheToken(): void
    {
        $this->resource_stakeholders = [
            $this->stakeholder('other', 'OtherStakeholder'),
            $this->stakeholder('file_obj', 'ilObjFileStakeholder'),
        ];

        $this->assertSame(200, $this->putFile()->getStatusCode());
        $this->assertSame('file_obj', $this->appended_stakeholder?->getId());
        $this->assertSame('ilObjFileStakeholder', $this->appended_stakeholder?->getFullyQualifiedClassName());
        $this->assertSame(self::USER, $this->appended_stakeholder?->getOwnerOfNewResources());
        $this->assertFalse(
            $this->appended_stakeholder?->canBeAccessedByCurrentUser(new ResourceIdentification(self::RID))
        );
    }

    public function testPutFileDoesNotAddAStakeholderToTheResource(): void
    {
        $this->resource_stakeholders = [$this->stakeholder('other', 'OtherStakeholder')];

        $this->assertSame(200, $this->putFile()->getStatusCode());
        $this->assertSame('other', $this->appended_stakeholder?->getId());
    }

    public function testStakeholderForResourceWithoutStakeholdersGrantsNoAccess(): void
    {
        $this->assertSame(200, $this->putFile()->getStatusCode());
        $this->assertInstanceOf(WOPIUnknownStakeholder::class, $this->appended_stakeholder);
        $this->assertFalse(
            $this->appended_stakeholder->canBeAccessedByCurrentUser(new ResourceIdentification(self::RID))
        );
    }

    public function testPutFilePublishesTheReceivedContentOnceTheIntervalHasPassed(): void
    {
        $this->lock_repository->store(self::RID, new Lock('A', time() + 600, self::USER));
        $this->published_created = time() - 10;

        $this->process(
            'POST',
            $this->files('contents'),
            $this->token(true),
            ['X-WOPI-Override' => 'PUT', 'X-WOPI-Lock' => 'A'],
            'payload',
            5
        );

        // appending first and publishing afterwards publishes the content just received
        $this->assertSame(['append:draft:payload', 'publish'], $this->irss_calls);
    }

    public function testPutFileKeepsDraftWithinTheInterval(): void
    {
        $this->lock_repository->store(self::RID, new Lock('A', time() + 600, self::USER));
        $this->published_created = time() - 2;

        $this->process(
            'POST',
            $this->files('contents'),
            $this->token(true),
            ['X-WOPI-Override' => 'PUT', 'X-WOPI-Lock' => 'A'],
            'payload',
            5
        );

        $this->assertSame(['append:draft:payload'], $this->irss_calls);
    }

    public function testCheckFileInfoIsJson(): void
    {
        $response = $this->process('GET', $this->files(), $this->token(true));

        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testPutFileAnswersWithTheNewVersionInsteadOfFileInfo(): void
    {
        $this->current_size = 0;

        $response = $this->process('POST', $this->files('contents'), $this->token(true), ['X-WOPI-Override' => 'PUT'], 'payload');
        $body = json_decode((string) $response->getBody(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotSame('', $response->getHeaderLine('X-WOPI-ItemVersion'));
        $this->assertSame(['LastModifiedTime'], array_keys($body));
    }

    public function testGetFileLargerThanExpectedIsRejected(): void
    {
        $response = $this->process('GET', $this->files('contents'), $this->token(true), ['X-WOPI-MaxExpectedSize' => '100']);

        $this->assertSame(412, $response->getStatusCode());
    }

    public function testPutFileLargerThanAllowedIsRejected(): void
    {
        $this->current_size = 0;
        $this->max_upload_size = 5;

        $response = $this->process('POST', $this->files('contents'), $this->token(true), ['X-WOPI-Override' => 'PUT'], 'payload');

        $this->assertSame(413, $response->getStatusCode());
        $this->assertSame([], $this->irss_calls);
    }

    public function testRequestWithoutProofIsRejectedOnceTheClientPublishesProofKeys(): void
    {
        [, $public] = $this->proofKeyPair();
        $this->proof_keys = new ProofKeys($public);

        $response = $this->process('GET', $this->files(), $this->token(true));

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('Proof validation failed', $response->getHeaderLine('X-WOPI-ServerError'));
    }

    public function testRequestWithValidProofIsAccepted(): void
    {
        [$private, $public] = $this->proofKeyPair();
        $this->proof_keys = new ProofKeys($public);
        $token = $this->token(true);
        $ticks = time() * 10_000_000 + 621_355_968_000_000_000;
        $url = 'https://ilias.example.org/wopi/index.php/' . $this->files() . '?access_token=' . $token;
        openssl_sign(ProofValidator::expected($token, $url, $ticks), $signature, $private, OPENSSL_ALGO_SHA256);

        $response = $this->process('GET', $this->files(), $token, [
            'X-WOPI-TimeStamp' => (string) $ticks,
            'X-WOPI-Proof' => base64_encode($signature),
        ]);

        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @return array<string, string>
     */
    private function signedHeaders(\OpenSSLAsymmetricKey $private, string $token): array
    {
        $ticks = time() * 10_000_000 + 621_355_968_000_000_000;
        $url = 'https://ilias.example.org/wopi/index.php/' . $this->files() . '?access_token=' . $token;
        openssl_sign(ProofValidator::expected($token, $url, $ticks), $signature, $private, OPENSSL_ALGO_SHA256);

        return ['X-WOPI-TimeStamp' => (string) $ticks, 'X-WOPI-Proof' => base64_encode($signature)];
    }

    /**
     * The client has rotated its keys twice since the discovery has been read.
     */
    public function testKeysAreFetchedAgainWhenTheProofCannotBeVerified(): void
    {
        [, $outdated] = $this->proofKeyPair();
        [$private, $rotated] = $this->proofKeyPair();
        $this->proof_keys = new ProofKeys($outdated);
        $fetches = 0;
        $this->refresh_proof_keys = static function () use ($rotated, &$fetches): ProofKeys {
            $fetches++;
            return new ProofKeys($rotated);
        };
        $token = $this->token(true);

        $response = $this->process('GET', $this->files(), $token, $this->signedHeaders($private, $token));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(1, $fetches);
    }

    public function testRequestStaysRejectedWhenTheFetchedKeysDoNotVerifyItEither(): void
    {
        [, $outdated] = $this->proofKeyPair();
        [$private] = $this->proofKeyPair();
        $this->proof_keys = new ProofKeys($outdated);
        $this->refresh_proof_keys = static fn(): ProofKeys => new ProofKeys($outdated);
        $token = $this->token(true);

        $response = $this->process('GET', $this->files(), $token, $this->signedHeaders($private, $token));

        $this->assertSame(500, $response->getStatusCode());
    }

    public function testRequestStaysRejectedWhenNothingIsFetched(): void
    {
        [, $outdated] = $this->proofKeyPair();
        [$private] = $this->proofKeyPair();
        $this->proof_keys = new ProofKeys($outdated);
        $this->refresh_proof_keys = static fn(): ?ProofKeys => null;
        $token = $this->token(true);

        $response = $this->process('GET', $this->files(), $token, $this->signedHeaders($private, $token));

        $this->assertSame(500, $response->getStatusCode());
    }

    public function testValidProofNeedsNoFetch(): void
    {
        [$private, $public] = $this->proofKeyPair();
        $this->proof_keys = new ProofKeys($public);
        $this->refresh_proof_keys = function (): never {
            $this->fail('the keys must not be fetched');
        };
        $token = $this->token(true);

        $response = $this->process('GET', $this->files(), $token, $this->signedHeaders($private, $token));

        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @return array{0: \OpenSSLAsymmetricKey, 1: ProofKey}
     */
    private function proofKeyPair(): array
    {
        $private = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        $rsa = openssl_pkey_get_details($private)['rsa'];

        return [$private, new ProofKey(base64_encode($rsa['n']), base64_encode($rsa['e']))];
    }

    public function testPutRelativeFileIsNotImplemented(): void
    {
        $response = $this->process('POST', $this->files(), $this->token(true), ['X-WOPI-Override' => 'PUT_RELATIVE']);

        $this->assertSame(501, $response->getStatusCode());
        $this->assertSame([], $this->irss_calls);
    }
}
