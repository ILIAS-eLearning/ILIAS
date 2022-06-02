<?php declare(strict_types=1);

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
 
namespace ILIAS\UI\Component\Card;

use ILIAS\UI\Component\Chart\ProgressMeter\ProgressMeter;
use ILIAS\UI\Component\Dropdown\Dropdown;
use ILIAS\UI\Component\Symbol\Icon\Icon;

/**
 * Interface Custom
 * @package ILIAS\UI\Component\Card
 */
interface RepositoryObject extends Card
{
    /**
     * Get a RepositoryObject card like this, but with an additional UI Icon representing the repository object type.
     */
    public function withObjectIcon(Icon $icon) : RepositoryObject;

    /**
     * Returns an UI Icon which represents the repository object type
     */
    public function getObjectIcon() : ?Icon;

    /**
     * Get a RepositoryObject card like this, but with an additional UI ProgressMeter object
     */
    public function withProgress(ProgressMeter $progress_meter) : RepositoryObject;

    /**
     * Get the ProgressMeter of the card
     */
    public function getProgress() : ?ProgressMeter;

    /**
     * Get a RepositoryObject card like this, but with an additional certificate outlined icon
     */
    public function withCertificateIcon(bool $certificate_icon) : RepositoryObject;

    /**
     * Get the certificate icon
     */
    public function getCertificateIcon() : ?bool;

    /**
     * Get a RepositoryObject card like this, but with an additional UI Dropdown object
     */
    public function withActions(Dropdown $dropdown) : RepositoryObject;

    /**
     * get the dropdown actions
     */
    public function getActions() : ?Dropdown;
}
