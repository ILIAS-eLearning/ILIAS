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

/**
 * Provider availability and mastery score of an LTI consumer object, used by ilLTIConsumerGradeServiceScores.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTIConsumerResultProperties
{
    private function __construct(
        private int $availability,
        private float $mastery_score
    ) {
    }

    /**
     * Reads the properties of an object. Without settings the object counts as unavailable with mastery score 1.
     */
    public static function forObject(int $a_obj_id): self
    {
        global $DIC;

        $query = "
			SELECT lti_ext_provider.availability, lti_consumer_settings.mastery_score
			FROM lti_ext_provider, lti_consumer_settings
			WHERE lti_ext_provider.id = lti_consumer_settings.provider_id
			AND lti_consumer_settings.obj_id = %s
		";

        $res = $DIC->database()->queryF($query, array('integer'), array($a_obj_id));

        if ($row = $DIC->database()->fetchAssoc($res)) {
            return new self((int) $row['availability'], (float) $row['mastery_score']);
        }

        return new self(0, 1);
    }

    public function getAvailability(): int
    {
        return $this->availability;
    }

    /**
     * False when the provider availability is ilLTIConsumeProvider::AVAILABILITY_NONE.
     */
    public function isAvailable(): bool
    {
        return $this->availability != 0;
    }

    public function getMasteryScore(): float
    {
        return $this->mastery_score;
    }
}
