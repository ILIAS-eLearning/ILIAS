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

namespace ILIAS\LDAP\Tests\Server;

use ILIAS\LDAP\Server\ServerUrlList;
use PHPUnit\Framework\TestCase;

class ServerUrlListTest extends TestCase
{
    public function testFromStringEmptyYieldsEmptyList(): void
    {
        $list = ServerUrlList::fromString('');
        $this->assertSame(0, $list->count());
        $this->assertSame('', $list->toString());
        $this->assertSame([], $list->getInvalidParts());
    }

    public function testFromStringOnlyWhitespaceAndCommasYieldsEmptyList(): void
    {
        $list = ServerUrlList::fromString('  ,  , ');
        $this->assertSame(0, $list->count());
        $this->assertSame('', $list->toString());
        $this->assertSame([], $list->getInvalidParts());
    }

    public function testFromStringSingleValidUri(): void
    {
        $list = ServerUrlList::fromString('ldap://host.example.com:389');
        $this->assertSame(1, $list->count());
        $this->assertSame('ldap://host.example.com:389', $list->getConnectionStringAtIndex(0));
        $this->assertSame('', $list->getConnectionStringAtIndex(1));
        $this->assertSame([], $list->getInvalidParts());
        $this->assertSame('ldap://host.example.com:389', $list->toString());
    }

    public function testFromStringHostWithoutSchemeStoredAsInvalid(): void
    {
        $input = 'host.example.com:389';
        $list = ServerUrlList::fromString($input);
        $this->assertSame(1, $list->count());
        $this->assertSame($input, $list->getConnectionStringAtIndex(0));
        $this->assertSame([$input], $list->getInvalidParts());
        $this->assertSame($input, $list->toString());
    }

    public function testFromStringLdapsPreserved(): void
    {
        $list = ServerUrlList::fromString('ldaps://secure.example.com:636');
        $this->assertSame(1, $list->count());
        $this->assertSame('ldaps://secure.example.com:636', $list->getConnectionStringAtIndex(0));
    }

    public function testFromStringMultipleValidUris(): void
    {
        $stored = 'ldap://a.example:389,ldaps://b.example:636';
        $list = ServerUrlList::fromString($stored);
        $this->assertSame(2, $list->count());
        $this->assertSame('ldap://a.example:389', $list->getConnectionStringAtIndex(0));
        $this->assertSame('ldaps://b.example:636', $list->getConnectionStringAtIndex(1));
        $this->assertSame([], $list->getInvalidParts());
        $this->assertSame($stored, $list->toString());
    }

    public function testFromStringInvalidPartStoredAsRawAndReportedInGetInvalidParts(): void
    {
        $invalid = 'ldap://';
        $list = ServerUrlList::fromString($invalid);
        $this->assertSame(1, $list->count());
        $this->assertSame($invalid, $list->getConnectionStringAtIndex(0));
        $this->assertSame([$invalid], $list->getInvalidParts());
        $this->assertSame($invalid, $list->toString());
    }

    public function testFromStringMixedValidAndInvalid(): void
    {
        $invalid = 'ldap://';
        $stored = 'ldap://ok:389,' . $invalid . ',ldaps://also:636';
        $list = ServerUrlList::fromString($stored);
        $this->assertSame(3, $list->count());
        $this->assertSame('ldap://ok:389', $list->getConnectionStringAtIndex(0));
        $this->assertSame($invalid, $list->getConnectionStringAtIndex(1));
        $this->assertSame('ldaps://also:636', $list->getConnectionStringAtIndex(2));
        $this->assertSame([$invalid], $list->getInvalidParts());
        $this->assertSame($stored, $list->toString(), 'toString() must preserve order and use comma delimiter');
    }

    public function testGetConnectionStringAtIndexNegativeIndexReturnsEmptyString(): void
    {
        $list = ServerUrlList::fromString('ldap://host:389');
        $this->assertSame('', $list->getConnectionStringAtIndex(-1));
    }

    public function testGetConnectionStringAtIndexOutOfRangePositiveReturnsEmptyString(): void
    {
        $list = ServerUrlList::fromString('ldap://host:389');
        $this->assertSame('', $list->getConnectionStringAtIndex(1));
        $this->assertSame('', $list->getConnectionStringAtIndex(99));
    }

    public function testRotateWithTwoEntriesMovesFirstToEnd(): void
    {
        $list = ServerUrlList::fromString('ldap://first:389,ldap://second:389');
        $rotated = $list->rotate();
        $this->assertNotSame($list, $rotated, 'rotate() must return new instance when count >= 2');
        $this->assertSame(2, $rotated->count());
        $this->assertSame('ldap://second:389', $rotated->getConnectionStringAtIndex(0));
        $this->assertSame('ldap://first:389', $rotated->getConnectionStringAtIndex(1));
        $this->assertSame('ldap://second:389,ldap://first:389', $rotated->toString());
    }

    public function testRotateWithOneEntryReturnsSameInstance(): void
    {
        $list = ServerUrlList::fromString('ldap://only:389');
        $rotated = $list->rotate();
        $this->assertSame($list, $rotated, 'rotate() must return this when count < 2');
        $this->assertSame(1, $rotated->count());
        $this->assertSame('ldap://only:389', $rotated->getConnectionStringAtIndex(0));
    }

    public function testRotateEmptyListReturnsSameInstance(): void
    {
        $list = ServerUrlList::fromString('');
        $rotated = $list->rotate();
        $this->assertSame($list, $rotated, 'rotate() must return this when count < 2');
        $this->assertSame(0, $rotated->count());
        $this->assertSame('', $rotated->toString());
    }

    public function testWithPrimaryAtZeroReturnsSameOrder(): void
    {
        $list = ServerUrlList::fromString('ldap://a:389,ldap://b:389');
        $reordered = $list->withPrimaryAt(0);
        $this->assertSame('ldap://a:389', $reordered->getConnectionStringAtIndex(0));
        $this->assertSame('ldap://b:389', $reordered->getConnectionStringAtIndex(1));
        $this->assertSame('ldap://a:389,ldap://b:389', $reordered->toString());
    }

    public function testWithPrimaryAtOneMovesSecondToFirst(): void
    {
        $list = ServerUrlList::fromString('ldap://a:389,ldap://b:389');
        $reordered = $list->withPrimaryAt(1);
        $this->assertNotSame($list, $reordered, 'withPrimaryAt() must return new instance when index valid');
        $this->assertSame(2, $reordered->count());
        $this->assertSame('ldap://b:389', $reordered->getConnectionStringAtIndex(0));
        $this->assertSame('ldap://a:389', $reordered->getConnectionStringAtIndex(1));
        $this->assertSame('ldap://b:389,ldap://a:389', $reordered->toString());
    }

    public function testWithPrimaryAtOutOfRangeReturnsSameInstance(): void
    {
        $list = ServerUrlList::fromString('ldap://a:389');
        $reordered = $list->withPrimaryAt(5);
        $this->assertSame($list, $reordered, 'withPrimaryAt() must return this when index out of range');
        $this->assertSame('ldap://a:389', $reordered->getConnectionStringAtIndex(0));
    }

    public function testWithPrimaryAtNegativeIndexReturnsSameInstance(): void
    {
        $list = ServerUrlList::fromString('ldap://a:389,ldap://b:389');
        $reordered = $list->withPrimaryAt(-1);
        $this->assertSame($list, $reordered, 'withPrimaryAt() must return this when index negative');
        $this->assertSame('ldap://a:389', $reordered->getConnectionStringAtIndex(0));
        $this->assertSame('ldap://b:389', $reordered->getConnectionStringAtIndex(1));
    }

    public function testValidUrlsYieldsOnlyUriInstances(): void
    {
        $list = ServerUrlList::fromString('ldap://a:389,ldap://b:389');
        $collected = [];
        foreach ($list->validUrls() as $index => $uri) {
            $collected[$index] = $uri;
        }
        $this->assertCount(2, $collected);
        $this->assertInstanceOf(\ILIAS\Data\URI::class, $collected[0]);
        $this->assertInstanceOf(\ILIAS\Data\URI::class, $collected[1]);
        $this->assertSame('ldap://a:389', (string) $collected[0]);
        $this->assertSame('ldap://b:389', (string) $collected[1]);
    }

    public function testValidUrlsSkipsInvalidParts(): void
    {
        $list = ServerUrlList::fromString('ldap://ok:389,ldap://');
        $collected = [];
        foreach ($list->validUrls() as $index => $uri) {
            $collected[$index] = $uri;
        }
        $this->assertCount(1, $collected);
        $this->assertArrayHasKey(0, $collected);
        $this->assertInstanceOf(\ILIAS\Data\URI::class, $collected[0]);
        $this->assertSame('ldap://ok:389', (string) $collected[0]);
    }

    public function testValidUrlsYieldsNothingWhenAllInvalid(): void
    {
        $list = ServerUrlList::fromString('ldap://,ldap://');
        $collected = [];
        foreach ($list->validUrls() as $index => $uri) {
            $collected[$index] = $uri;
        }
        $this->assertCount(0, $collected);
    }

    public function testValidUrlsYieldsOriginalIndicesWithPrimaryAtCorrectWithMixedValidAndInvalid(): void
    {
        $invalid = 'ldap://';
        $list = ServerUrlList::fromString('ldap://first:389,' . $invalid . ',ldap://third:389');
        $this->assertSame(3, $list->count());

        $indices = [];
        foreach ($list->validUrls() as $index => $uri) {
            $indices[] = $index;
        }
        $this->assertSame([0, 2], $indices);

        $reordered = $list->withPrimaryAt(2);
        $this->assertSame(3, $reordered->count());
        $this->assertSame('ldap://third:389', $reordered->getConnectionStringAtIndex(0));
        $this->assertSame('ldap://first:389', $reordered->getConnectionStringAtIndex(1));
        $this->assertSame($invalid, $reordered->getConnectionStringAtIndex(2));
        $this->assertSame('ldap://third:389,ldap://first:389,' . $invalid, $reordered->toString());

        $reorderedInvalid = $list->withPrimaryAt(1);
        $this->assertSame($invalid, $reorderedInvalid->getConnectionStringAtIndex(0));
        $this->assertSame('ldap://first:389', $reorderedInvalid->getConnectionStringAtIndex(1));
        $this->assertSame('ldap://third:389', $reorderedInvalid->getConnectionStringAtIndex(2));
    }

    public function testToStringDelegatesToMagicToString(): void
    {
        $list = ServerUrlList::fromString('ldap://host:389');
        $this->assertSame($list->toString(), (string) $list);
    }

    public function testConstructorWithEmptyArray(): void
    {
        $list = new ServerUrlList([]);
        $this->assertSame(0, $list->count());
        $this->assertSame('', $list->toString());
        $this->assertSame([], $list->getInvalidParts());
    }

    public function testConstructorPreservesOrderWithArrayValues(): void
    {
        $list = new ServerUrlList([
            new \ILIAS\Data\URI('ldap://first:389'),
            new \ILIAS\Data\URI('ldap://second:389')
        ]);
        $this->assertSame(2, $list->count());
        $this->assertSame('ldap://first:389', $list->getConnectionStringAtIndex(0));
        $this->assertSame('ldap://second:389', $list->getConnectionStringAtIndex(1));
    }

    /**
     * Constructor must reindex with array_values() so indices are 0-based (mutant that removes array_values would break this).
     */
    public function testConstructorWithNonSequentialKeysRebasesToZeroBasedIndices(): void
    {
        $list = new ServerUrlList([
            1 => new \ILIAS\Data\URI('ldap://first:389'),
            2 => new \ILIAS\Data\URI('ldap://second:389')
        ]);
        $this->assertSame(2, $list->count());
        $this->assertSame('ldap://first:389', $list->getConnectionStringAtIndex(0));
        $this->assertSame('ldap://second:389', $list->getConnectionStringAtIndex(1));
        $this->assertSame('', $list->getConnectionStringAtIndex(2));
    }

    /**
     * fromString must skip empty parts after trim (mutant that removes "continue" would add empty entries).
     */
    public function testFromStringSkipsEmptyPartsAfterTrim(): void
    {
        $list = ServerUrlList::fromString('ldap://only:389  ,  ,  ');
        $this->assertSame(1, $list->count());
        $this->assertSame('ldap://only:389', $list->getConnectionStringAtIndex(0));
        $this->assertSame('ldap://only:389', $list->toString());
    }

    /**
     * getConnectionStringAtIndex must return string for raw (non-URI) entry.
     */
    public function testGetConnectionStringAtIndexReturnsRawStringForInvalidEntry(): void
    {
        $raw = 'not-a-valid-uri';
        $list = ServerUrlList::fromString($raw);
        $this->assertSame(1, $list->count());
        $this->assertSame($raw, $list->getConnectionStringAtIndex(0));
        $this->assertSame([$raw], $list->getInvalidParts());
    }
}
