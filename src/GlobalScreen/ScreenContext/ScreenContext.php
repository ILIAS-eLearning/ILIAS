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
namespace ILIAS\GlobalScreen\ScreenContext;

use ILIAS\Data\ReferenceId;
use ILIAS\GlobalScreen\ScreenContext\AdditionalData\Collection;

/**
 * Interface ScreenContext
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ScreenContext
{
    /**
     * @return string
     */
    public function getUniqueContextIdentifier() : string;

    /**
     * @return bool
     */
    public function hasReferenceId() : bool;

    /**
     * @return ReferenceId
     */
    public function getReferenceId() : ReferenceId;

    /**
     * @param ReferenceId $reference_id
     * @return ScreenContext
     */
    public function withReferenceId(ReferenceId $reference_id) : ScreenContext;

    /**
     * @param Collection $collection
     * @return ScreenContext
     */
    public function withAdditionalData(Collection $collection) : ScreenContext;

    /**
     * @param string $key
     * @param        $value
     * @return ScreenContext
     */
    public function addAdditionalData(string $key, $value) : ScreenContext;

    /**
     * @return Collection
     */
    public function getAdditionalData() : Collection;
}
