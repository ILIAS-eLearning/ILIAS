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

namespace ILIAS\StaticURL;

use ILIAS\Setup\Artifact;
use ILIAS\Setup;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ArtifactObjective extends Artifact\BuildArtifactObjective
{
    public const ARTIFACT = '../components/ILIAS/StaticURL/src/artifacts/static_url_handlers.php';

    public function getArtifactPath(): string
    {
        return self::ARTIFACT;
    }

    public function build(): Setup\Artifact
    {
        $implementation_of = new Setup\ImplementationOfInterfaceFinder();
        $implementations = iterator_to_array(
            $implementation_of->getMatchingClassNames(Handler\Handler::class)
        );

        return new Artifact\ArrayArtifact($implementations);
    }

}
