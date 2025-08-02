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

namespace ILIAS\User\Profile\ChangeListeners;

use ILIAS\User\Profile\Fields\FieldDefinition;
use ILIAS\User\Profile\ChangeListeners\UserFieldAttributesChangeListener;
use ILIAS\Setup\Artifact;
use ILIAS\Setup\Artifact\ArrayArtifact;
use ILIAS\Setup\Artifact\BuildArtifactObjective;

class CollectListenersObjective extends BuildArtifactObjective
{
    /**
     * @param array<UserFieldAttributesChangeListener> $contributions
     */
    public function __construct(
        private readonly array $contributions
    ) {
    }

    public function getArtifactName(): string
    {
        return 'profile_attribute_change_listeners';
    }

    public function build(): Artifact
    {
        return new ArrayArtifact(
            array_map(
                static function (UserFieldAttributesChangeListener $v): string {
                    $field_class = $v->isInterestedInField();
                    if (!(new $field_class() instanceof FieldDefinition)) {
                        throw new \Exception('The Field ' . $v::class
                            . ' is interested in, does not exist.');
                    }
                    return $v::class;
                },
                $this->contributions
            )
        );
    }
}
