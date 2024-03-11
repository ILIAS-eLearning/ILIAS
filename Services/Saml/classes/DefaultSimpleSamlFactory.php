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
use SimpleSAML\Utils\Config\Metadata;
use SimpleSAML\Store\StoreInterface;
use SimpleSAML\Store\SQLStore;
use SimpleSAML\Metadata\Signer;
use SimpleSAML\Configuration;
use SimpleSAML\Auth\Source;
use SimpleSAML\Metadata\SAMLBuilder;

class DefaultSimpleSamlFactory implements SimpleSamlFactory
{
    public function sourceById(string $id): Source
    {
        return Source::getById($id);
    }

    public function sign(string $metadata, array $entity, string $type): string
    {
        return Signer::sign($metadata, $entity, $type);
    }

    public function store(): StoreInterface
    {
        return new SQLStore();
    }

    public function configFromArray(array $config): Configuration
    {
        return Configuration::loadFromArray($config);
    }

    public function contact(?array $contact): array
    {
        return Metadata::getContact($contact);
    }

    public function crypt(): Crypto
    {
        return new Crypto();
    }

    public function builder(string $id): SAMLBuilder
    {
        return new SAMLBuilder($id);
    }
}
