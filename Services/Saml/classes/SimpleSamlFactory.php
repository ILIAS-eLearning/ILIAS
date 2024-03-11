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

namespace ILIAS\Saml;

use SimpleSAML\Utils\Crypto;
use SimpleSAML\Store\StoreInterface;
use SimpleSAML\Auth\Source;
use SimpleSAML\Configuration;
use SimpleSAML\Metadata\SAMLBuilder;

interface SimpleSamlFactory
{
    public function sourceById(string $id): Source;
    public function sign(string $metadata, array $entity, string $type): string;
    public function store(): StoreInterface;
    public function configFromArray(array $config): Configuration;
    public function contact(?array $contact): array;
    public function crypt(): Crypto;
    public function builder(string $id): SAMLBuilder;
}
