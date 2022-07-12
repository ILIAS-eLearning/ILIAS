<?php

namespace Firebase\JWT;

use ArrayAccess;
use LogicException;
use OutOfBoundsException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use RuntimeException;

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
     * @var array<string, Key>
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

    public function __construct(
        string $jwksUri,
        ClientInterface $httpClient,
        RequestFactoryInterface $httpFactory,
        CacheItemPoolInterface $cache,
        int $expiresAfter = null,
        bool $rateLimit = false
    ) {
        $this->jwksUri = $jwksUri;
        $this->httpClient = $httpClient;
        $this->httpFactory = $httpFactory;
        $this->cache = $cache;
        $this->expiresAfter = $expiresAfter;
        $this->rateLimit = $rateLimit;
        $this->setCacheKeys();
    }

    /**
     * @param string $keyId
     * @return Key
     */
    public function offsetGet($keyId) : Key
    {
        if (!$this->keyIdExists($keyId)) {
            throw new OutOfBoundsException('Key ID not found');
        }
        return $this->keySet[$keyId];
    }

    /**
     * @param string $keyId
     * @return bool
     */
    public function offsetExists($keyId) : bool
    {
        return $this->keyIdExists($keyId);
    }

    /**
     * @param string $offset
     * @param Key $value
     */
    public function offsetSet($offset, $value) : void
    {
        throw new LogicException('Method not implemented');
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset) : void
    {
        throw new LogicException('Method not implemented');
    }

    private function keyIdExists(string $keyId) : bool
    {
        $keySetToCache = null;
        if (null === $this->keySet) {
            $item = $this->getCacheItem();
            // Try to load keys from cache
            if ($item->isHit()) {
                // item found! Return it
                $this->keySet = $item->get();
            }
        }

        if (!isset($this->keySet[$keyId])) {
            if ($this->rateLimitExceeded()) {
                return false;
            }
            $request = $this->httpFactory->createRequest('get', $this->jwksUri);
            $jwksResponse = $this->httpClient->sendRequest($request);
            $jwks = json_decode((string) $jwksResponse->getBody(), true);
            $this->keySet = $keySetToCache = JWK::parseKeySet($jwks);

            if (!isset($this->keySet[$keyId])) {
                return false;
            }
        }

        if ($keySetToCache) {
            $item = $this->getCacheItem();
            $item->set($keySetToCache);
            if ($this->expiresAfter) {
                $item->expiresAfter($this->expiresAfter);
            }
            $this->cache->save($item);
        }

        return true;
    }

    private function rateLimitExceeded() : bool
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

    private function getCacheItem() : CacheItemInterface
    {
        if (\is_null($this->cacheItem)) {
            $this->cacheItem = $this->cache->getItem($this->cacheKey);
        }

        return $this->cacheItem;
    }

    private function setCacheKeys() : void
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
