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

namespace ILIAS\Data\Privacy\PHPStan\Rules\Fixtures;

use ILIAS\Data\Privacy\TechnicalRequirement\DisplayToUser;
use ILIAS\Data\Privacy\TechnicalRequirement\Export;
use ILIAS\Data\Privacy\TechnicalRequirement\TechnicalRequirement;
use ILIAS\Data\Privacy\TechnicalRequirement\TechnicalRequirements;
use ILIAS\Data\Privacy\TechnicalRequirement\StoreInTable;
use ILIAS\Data\Privacy\Source\DbTableColumn;
use ILIAS\Data\Privacy\Source\Sources;
use ILIAS\Data\Privacy\Types\PersonName;
use ILIAS\Data\Privacy\Types\PostalAddress;

class NotAPrivacyType
{
    public function resolve(TechnicalRequirement $requirement): string
    {
        return 'irrelevant';
    }
}

/**
 * @param class-string<DisplayToUser> $dynamic_requirement_class
 */
function resolveCalls(
    PostalAddress $address,
    TechnicalRequirements $requirements,
    Sources $sources,
    TechnicalRequirement $requirement,
    mixed $untyped,
    NotAPrivacyType $other,
    string $dynamic_requirement_class,
    string $context_variable
): void {
    $address->resolve(new DisplayToUser('on the page', 'the reader', 'read it'));
    $address->resolve($requirements->displayToUser(to: 'the reader', where: 'in the list', in_order_to: 'find it'));
    $address->resolve($untyped->passToComponent('Mail', 'send it'));
    $address->resolve($requirements->storeInTable($sources->user()->postalAddress(), 'keep it'));
    $address->resolve(new StoreInTable(new DbTableColumn('tmp_table', 'tmp_column'), 'keep it', 'it is enabled'));
    $address->resolve($requirement);
    $other->resolve($requirement);
    $address->getSource();
    $address->{'resolve'}($requirement);
    $address->resolve(new $dynamic_requirement_class('dynamic_class_context'));
    $address->resolve(new DisplayToUser($context_variable, 'the reader', 'read it'));
    $address->resolve(new DisplayToUser($requirements->technicalProcessing('x')->describe(), 'the reader', 'read it'));
    $address->resolve(new Export('as CSV', 'the reader', 'keep a copy'));
    $address->resolve($requirements->export('as vCard', 'the reader', 'keep a copy'));
    $held = $requirements->export('as PDF', 'the reader', 'keep a copy');
    $address->resolve($held);
}

class ResolvingClass
{
    public function method(PostalAddress $address): void
    {
        $address->resolve(new DisplayToUser('in the method', 'the reader', 'read it'));
    }
}

class HoldsAnAddress
{
    public function address(): PostalAddress
    {
        throw new \LogicException('only analysed');
    }
}

function nullableResolveCalls(?PostalAddress $maybe, ?HoldsAnAddress $holder): void
{
    $maybe?->resolve(new DisplayToUser('nullsafe', 'the reader', 'read it'));
    $holder?->address()->resolve(new DisplayToUser('nullsafe chain', 'the reader', 'read it'));
}

class Receivers
{
    public const string MAIL = 'Mail';
}

function ambiguousResolveCalls(
    PostalAddress $address,
    PostalAddress|PersonName $either,
    TechnicalRequirements $requirements,
    bool $flag
): void {
    $address->resolve($flag ? $requirements->displayToUser('a', 'b', 'c') : $requirements->export('a', 'b', 'c'));
    $address->resolve($requirements->passToComponent(Receivers::MAIL, 'send it'));
    $either->resolve($requirements->displayToUser('in the union', 'the reader', 'read it'));
}
