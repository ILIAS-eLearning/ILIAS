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

namespace ILIAS\Data;

/**
 * An Email Address is a common personal address for people online.
 * This class ensures the address follows common format requirements.
 * It also splits the address into some of the parts outlined in RFC 2822 that can then be retrieved separately.
 * Non-ASCII characters are technically valid in the local (RFC 6531) and the domain (RFC 3490) parts, but...
 *      - in the local part they are only supported by a small amount of mailservers
 *      - in the domain part they are converted to ASCII punycode
 * Because of this limited support, PHP's own FILTER_VALIDATE_EMAIL does not allow Unicode characters. However, we don't
 * want to be as strict. ILIAS is used internationally where Latin-1 Supplement characters like ö, ä, ü, é, ø become
 * more common in email addresses. Some Service Providers need to allow Japanese, Chinese and Cyrillic/Ukrainian
 * scripts. Therefore, we allow Unicode characters covered by the Email Address Internationalization (EAI) framework.
 * Be aware that not all email servers can process these by default and that additional steps might be necessary to
 * enable ILIAS to send those emails.
 * You can use isAscii() to check if an address is following the old standards and is likely supported by all servers.
 *
 * @author Ferdinand Engländer <ferdinand.englaender@concepts-and-training.de>
 */
class EmailAddress
{
    protected string $addressFull;
    protected string $domainPart;
    protected string $localPart;
    protected bool $isAscii;
    protected bool $isDomainPartAscii;
    protected bool $isLocalPartAscii;


    public function __construct(string $address_Full)
    {
        $this->addressFull = $this->digestFullAddress($address_Full);
        $this->domainPart = $this->digestDomainPart($address_Full);
        $this->localPart = $this->digestLocalPart($address_Full);
        if ($this->isDomainPartAscii && $this->isLocalPartAscii) {
            $this->isAscii = true;
        } else {
            $this->isAscii = false;
        }
    }

    public function getAddressFull(): string
    {
        return $this->addressFull;
    }

    public function getDomainPart(): string
    {
        return $this->domainPart;
    }

    public function getLocalPart(): string
    {
        return $this->localPart;
    }

    public function getIsAscii(): bool
    {
        return $this->isAscii;
    }

    public function getIsDomainPartAscii(): bool
    {
        return $this->isDomainPartAscii;
    }

    public function getIsLocalPartAscii(): bool
    {
        return $this->isLocalPartAscii;
    }

    public function __toString(): string
    {
        return $this->getAddressFull();
    }

    protected function checkAscii(string $str): bool
    {
        return mb_check_encoding($str, 'ASCII');
    }

    protected function digestFullAddress(string $address): string
    {
        $address = trim($address);

        if (substr_count($address, '@') !== 1) {
            throw new \InvalidArgumentException("Email must contain exactly one '@' character.");
        }

        [$local, $domain] = explode('@', $address, 2);

        if ($local === '' || $domain === '') {
            throw new \InvalidArgumentException("Email must have non-empty local and domain parts.");
        }

        $this->addressFull = $address;
        return $address;
    }

    protected function digestDomainPart(string $address): string
    {
        [, $domain] = explode('@', $address, 2);

        $this->isDomainPartAscii = $this->checkAscii($domain);

        if ($domain === 'localhost') {
            return $domain;
        }

        if (preg_match('/[\p{C}\p{Z}]/u', $domain)) {
            throw new \InvalidArgumentException("Domain part contains invalid characters (e.g., whitespace or control).");
        }

        if (str_contains($domain, '..')) {
            throw new \InvalidArgumentException("Domain part contains consecutive dots.");
        }

        // not flagging double hyphens as punycode uses those

        if (substr_count($domain, '.') < 1) {
            throw new \InvalidArgumentException("Domain must contain at least one dot except for 'localhost'.");
        }

        if (strlen($domain) > 254) {
            throw new \InvalidArgumentException("Domain part exceeds 254 character limit.");
        }

        $labels = explode('.', $domain);
        foreach ($labels as $label) {
            if (strlen($label) > 63) {
                throw new \InvalidArgumentException("Domain label exceeds 63 character limit.");
            }

            if ($label === '') {
                throw new \InvalidArgumentException("Domain contains an empty label.");
            }

            if ($label[0] === '-' || $label[strlen($label) - 1] === '-') {
                throw new \InvalidArgumentException("Domain labels must not start or end with a hyphen.");
            }
        }

        return $domain;
    }

    protected function digestLocalPart(string $address): string
    {
        [$local,] = explode('@', $address, 2);

        if (!mb_check_encoding($local, 'UTF-8')) {
            throw new \InvalidArgumentException("Local part is not valid UTF-8.");
        }

        if ($local[0] === '.' || str_ends_with($local, '.')) {
            throw new \InvalidArgumentException("Local part cannot start or end with a dot.");
        }

        if (str_contains($local, '..')) {
            throw new \InvalidArgumentException("Local part cannot contain consecutive dots.");
        }

        // double quotes are allowed only as first and last character
        if (str_starts_with($local, '"') && str_ends_with($local, '"')) {
            $local_strip_quotes = substr($local, 1, -1);
        } else {
            $local_strip_quotes = $local;
        }

        if (preg_match('/[\x00-\x1F\x7F]/', $local_strip_quotes)) {
            throw new \InvalidArgumentException("Local part contains unsupported control characters or invalid escape sequences.");
        }

        // check if safe Ascii
        if (preg_match('/^[a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~\.-]+$/', $local_strip_quotes)) {
            $this->isLocalPartAscii = true;
        } else {
            $this->isLocalPartAscii = false;
        }
        // check if save Unicode
        if (!self::isAllowedIntlUnicode($local_strip_quotes)) {
            throw new \InvalidArgumentException("Local part is not a valid Unicode string.");
        }

        return $local;
    }

    protected static function isAllowedIntlUnicode(string $string): bool
    {
        // Check allowed Unicode characters this includes e.g.
        //      * a-z, A-Z, 0-9
        //      * Latin accented: é, ñ, ö, č
        //      * Greek: Α, β, Ω
        //      * Cyrillic: Б, и, я
        //      * Arabic letters: ا, ب, خ
        //      * Hebrew: א, ב, ג
        //      * East Asian ideographs (CJK): 日, 本, 語, 汉, 字
        //      * Devanagari (Hindi, Marathi): अ, आ, क
        //      * Hangul (Korean): 한, 글
        //      * and more
        // \p{L} includes all characters Unicode defines as letters
        // \p{N} includes all characters Unicode defines as numbers
        // this excludes emojis and control characters which are not allowed in the local part
        if (!preg_match('/^[\p{L}\p{N}!#$%&\'*+\/=?^_`{|}~\.-]+$/u', $string)) {
            return false;
        } else {
            return true;
        }
    }
}
