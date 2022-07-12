<?php declare(strict_types = 1);

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

namespace ILIAS\Survey\Mode;

/**
 * Interface for modes
 * @author Alexander Killing <killing@leifos.de>
 */
interface FeatureConfig
{
    public function supportsCompetences() : bool;

    public function supportsConstraints() : bool;

    public function supportsAccessCodes() : bool;

    public function supportsTutorNotification() : bool;

    public function supportsMemberReminder() : bool;

    public function supportsSumScore() : bool;

    /**
     * If raters rate single persons (appraisees) this mode is activated.
     * Otherwise the participants screen will be shown
     * @return bool
     */
    public function usesAppraisees() : bool;
}
