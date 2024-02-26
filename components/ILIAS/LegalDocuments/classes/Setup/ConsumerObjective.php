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

namespace ILIAS\LegalDocuments\Setup;

use ILIAS\LegalDocuments\Consumer;
use ILIAS\LegalDocuments\Internal;
use ILIAS\Setup\Artifact;
use ILIAS\Setup\Artifact\ArrayArtifact;
use ILIAS\Setup\Artifact\BuildArtifactObjective;
use ILIAS\Setup\ImplementationOfInterfaceFinder;

class ConsumerObjective extends BuildArtifactObjective
{

    public function getArtifactName(): string
    {
        return "legal_documents_consumer";
    }


    public function build(): Artifact
    {
        $finder = new ImplementationOfInterfaceFinder();
        $classes = iterator_to_array($finder->getMatchingClassNames(Consumer::class));
        $ids = array_map(fn($class) => (new $class())->id(), $classes);

        return new ArrayArtifact(array_combine($ids, $classes));
    }
}
