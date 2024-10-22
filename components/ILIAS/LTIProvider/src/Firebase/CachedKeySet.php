<?php

namespace Firebase\JWT;

use ArrayAccess;
use InvalidArgumentException;
use LogicException;
use OutOfBoundsException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use RuntimeException;
use UnexpectedValueException;

/**
 * @implements ArrayAccess<string, Key>
 */
class CachedKeySet implements ArrayAccess
{
    /**
     * @var string
     */
    private string $jwksUri;
    /**
     * @var ClientInterface
     */
    private ClientInterface $httpClient;
    /**
     * @var RequestFactoryInterface
     */
    private RequestFactoryInterface $httpFactory;
    /**
     * @var CacheItemPoolInterface
     */
    private CacheItemPoolInterface $cache;
    /**
     * @var ?int
     */
    private ?int $expiresAfter;
    /**
     * @var ?CacheItemInterface
     */
    private ?CacheItemInterface $cacheItem;
    /**
     * @var array<string, array<mixed>>
     */
    private array $keySet;
    /**
     * @var string
     */
    private string $cacheKey;
    /**
     * @var string
     */
    private string $cacheKeyPrefix = 'jwks';
    /**
     * @var int
     */
    private int $maxKeyLength = 64;
    /**
     * @var bool
     */
    private bool $rateLimit;
    /**
     * @var string
     */
    private string $rateLimitCacheKey;
    /**
     * @var int
     */
    private int $maxCallsPerMinute = 10;
    /**
     * @var string|null
     */
    private ?string $defaultAlg;

    public function __construct(
        string $jwksUri,
        ClientInterface $httpClient,
        RequestFactoryInterface $httpFactory,
        CacheItemPoolInterface $cache,
        int $expiresAfter = null,
        bool $rateLimit = false,
        string $defaultAlg = null
    ) {
        $this->jwksUri = $jwksUri;
        $this->httpClient = $httpClient;
        $this->httpFactory = $httpFactory;
        $this->cache = $cache;
        $this->expiresAfter = $expiresAfter;
        $this->rateLimit = $rateLimit;
        $this->defaultAlg = $defaultAlg;
        $this->setCacheKeys();
    }

    /**
     * @param string $keyId
     * @return Key
     */
    public function offsetGet($keyId): Key
    {
        if (!$this->keyIdExists($keyId)) {
            throw new OutOfBoundsException('Key ID not found');
        }
        return JWK::parseKey($this->keySet[$keyId], $this->defaultAlg);
    }

    /**
     * @param string $keyId
     * @return bool
     */
    public function offsetExists($keyId): bool
    {
        return $this->keyIdExists($keyId);
    }

    /**
     * @param string $offset
     * @param Key $value
     */
    public function offsetSet($offset, $value): void
    {
        throw new LogicException('Method not implemented');
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset): void
    {
        throw new LogicException('Method not implemented');
    }

    /**
     * @return array<mixed>
     */
    private function formatJwksForCache(string $jwks): array
    {
        $jwks = json_decode($jwks, true);

        if (!isset($jwks['keys'])) {
            throw new UnexpectedValueException('"keys" member must exist in the JWK Set');
        }

        if (empty($jwks['keys'])) {
            throw new InvalidArgumentException('JWK Set did not contain any keys');
        }

        $keys = [];
        foreach ($jwks['keys'] as $k => $v) {
            $kid = isset($v['kid']) ? $v['kid'] : $k;
            $keys[(string) $kid] = $v;
        }

        return $keys;
    }

    private function keyIdExists(string $keyId): bool
    {
        if (null === $this->keySet) {
            $item = $this->getCacheItem();
            // Try to load keys from cache
            if ($item->isHit()) {
                // item found! retrieve it
                $this->keySet = $item->get();
                // If the cached item is a string, the JWKS response was cached (previous behavior).
                // Parse this into expected format array<kid, jwk> instead.
                if (\is_string($this->keySet)) {
                    $this->keySet = $this->formatJwksForCache($this->keySet);
                }
            }
        }

        if (!isset($this->keySet[$keyId])) {
            if ($this->rateLimitExceeded()) {
                return false;
            }
            $request = $this->httpFactory->createRequest('GET', $this->jwksUri);
            $jwksResponse = $this->httpClient->sendRequest($request);
            if ($jwksResponse->getStatusCode() !== 200) {
                throw new UnexpectedValueException(
                    sprintf(
                        'HTTP Error: %d %s for URI "%s"',
                        $jwksResponse->getStatusCode(),
                        $jwksResponse->getReasonPhrase(),
                        $this->jwksUri,
                    ),
                    $jwksResponse->getStatusCode()
                );
            }
            $this->keySet = $this->formatJwksForCache((string) $jwksResponse->getBody());

            if (!isset($this->keySet[$keyId])) {
                return false;
            }

            $item = $this->getCacheItem();
            $item->set($this->keySet);
            if ($this->expiresAfter) {
                $item->expiresAfter($this->expiresAfter);
            }
            $this->cache->save($item);
        }

        return true;
    }

    private function rateLimitExceeded(): bool
    {
        if (!$this->rateLimit) {
            return false;
        }

        $cacheItem = $this->cache->getItem($this->rateLimitCacheKey);
        if (!$cacheItem->isHit()) {
            $cacheItem->expiresAfter(1); // # of calls are cached each minute
        }

        $callsPerMinute = (int) $cacheItem->get();
        if (++$callsPerMinute > $this->maxCallsPerMinute) {
            return true;
        }
        $cacheItem->set($callsPerMinute);
        $this->cache->save($cacheItem);
        return false;
    }

    private function getCacheItem(): CacheItemInterface
    {
        if (\is_null($this->cacheItem)) {
            $this->cacheItem = $this->cache->getItem($this->cacheKey);
        }

        return $this->cacheItem;
    }

    private function setCacheKeys(): void
    {
        if (empty($this->jwksUri)) {
            throw new RuntimeException('JWKS URI is empty');
        }

        // ensure we do not have illegal characters
        $key = preg_replace('|[^a-zA-Z0-9_\.!]|', '', $this->jwksUri);

        // add prefix
        $key = $this->cacheKeyPrefix . $key;

        // Hash keys if they exceed $maxKeyLength of 64
        if (\strlen($key) > $this->maxKeyLength) {
            $key = substr(hash('sha256', $key), 0, $this->maxKeyLength);
        }

        $this->cacheKey = $key;

        if ($this->rateLimit) {
            // add prefix
            $rateLimitKey = $this->cacheKeyPrefix . 'ratelimit' . $key;

            // Hash keys if they exceed $maxKeyLength of 64
            if (\strlen($rateLimitKey) > $this->maxKeyLength) {
                $rateLimitKey = substr(hash('sha256', $rateLimitKey), 0, $this->maxKeyLength);
            }

            $this->rateLimitCacheKey = $rateLimitKey;
        }
    }
}
