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

namespace ILIAS\Data\Privacy\PHPStan\Collector\Fixtures;

use ILIAS\Data\Privacy\Purpose\DisplayToUser;
use ILIAS\Data\Privacy\Purpose\Purpose;
use ILIAS\Data\Privacy\Purpose\Purposes;
use ILIAS\Data\Privacy\Purpose\StoreInTable;
use ILIAS\Data\Privacy\Source\DbTableColumn;
use ILIAS\Data\Privacy\Source\Sources;
use ILIAS\Data\Privacy\Types\PostalAddress;

class NotAPrivacyType
{
    public function resolve(Purpose $purpose): string
    {
        return 'irrelevant';
    }
}

/**
 * @param class-string<DisplayToUser> $dynamic_purpose_class
 */
function resolveCalls(
    PostalAddress $address,
    Purposes $purposes,
    Sources $sources,
    Purpose $purpose,
    mixed $untyped,
    NotAPrivacyType $other,
    string $dynamic_purpose_class,
    string $context_variable
): void {
    $address->resolve(new DisplayToUser('fixture_context'));
    $address->resolve($purposes->displayToUser('factory_context'));
    $address->resolve($untyped->passToComponent('Mail', 'fixture_reason'));
    $address->resolve($purposes->storeInTable($sources->user()->postalAddress()));
    $address->resolve(new StoreInTable(new DbTableColumn('tmp_table', 'tmp_column')));
    $address->resolve($purpose);
    $other->resolve($purpose);
    $address->getSource();
    $address->{'resolve'}($purpose);
    $address->resolve(new $dynamic_purpose_class('dynamic_class_context'));
    $address->resolve(new DisplayToUser($context_variable));
    $address->resolve(new DisplayToUser($purposes->technicalProcessing('x')->describe()));
}
