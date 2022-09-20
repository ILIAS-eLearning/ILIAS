<?php

namespace ILIAS\HTTP\Cookies;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Interface Cookie
 *
 * ILIAS cookie representation.
 * All implementations must be immutable.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @package ILIAS\HTTP\Cookies
 * @since   5.3
 * @version 1.0.0
 */
interface Cookie
{
    /**
     * Cookie name.
     */
    public function getName(): string;


    /**
     * Cookie value.
     */
    public function getValue(): ?string;


    /**
     * Expiration date as unix timestamp.
     */
    public function getExpires(): int;


    /**
     * Max age measured in seconds.
     * If the max age is zero no max age is set.
     */
    public function getMaxAge(): int;


    /**
     * Cookie path.
     */
    public function getPath(): ?string;


    /**
     * Cookie domain.
     */
    public function getDomain(): ?string;


    /**
     * True if it's secure cookie otherwise false.
     */
    public function getSecure(): bool;


    /**
     * True if the cookie is http only otherwise false.
     */
    public function getHttpOnly(): bool;


    /**
     * Sets the cookie value.
     *
     * @param null|string $value The cookie value.
     */
    public function withValue(string $value = null): Cookie;


    /**
     * Sets the expiration date of the cookie.
     * If the cookie should be expired please use the expire function.
     *
     * If the expires parameter equals null,
     * then the expires key will be removed from the cookie.
     *
     * @param null|\DateTimeInterface|int|string $expires The expiration time of the Cookie.
     */
    public function withExpires($expires = null): Cookie;


    /**
     * Sets the expiration date to +5 years.
     */
    public function rememberForLongTime(): Cookie;


    /**
     * Expire the cookie.
     * Useful if the cookie should be deleted at the client side.
     */
    public function expire(): Cookie;


    /**
     * Maximal life time of the cookie in seconds.
     * The most browser prefer max age over expiration date.
     *
     * @param null|int $maxAge Lifetime in seconds.
     */
    public function withMaxAge(int $maxAge = null): Cookie;


    /**
     * Sets the cookie path.
     *
     * @param null|string $path The cookie path.
     */
    public function withPath(string $path = null): Cookie;


    /**
     * Sets the domain name for the cookie.
     *
     * @param null|string $domain Cookie domain.
     */
    public function withDomain(string $domain = null): Cookie;


    /**
     * Sets if the cookie is a secure cookie or not.
     *
     * @param null|bool $secure Secure flag.
     */
    public function withSecure(bool $secure = null): Cookie;


    /**
     * Sets if the cookie is http only.
     *
     * @param null|bool $httpOnly http only flag.
     */
    public function withHttpOnly(bool $httpOnly = null): Cookie;


    /**
     * Returns the string representation of the object.
     *
     * @return string String representation.
     */
    public function __toString(): string;
}
