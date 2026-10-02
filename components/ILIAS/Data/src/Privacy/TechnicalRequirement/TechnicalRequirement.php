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

namespace ILIAS\Data\Privacy\TechnicalRequirement;

/**
 * The technical reason why the code needs the raw value of a personal data
 * value, stated at the point of access (store it, display it, hand it to
 * another component, process it).
 *
 * This is deliberately not the "purpose" of processing in the sense of the
 * GDPR: it describes what the code does, not why an institution may do it.
 *
 * Technical requirements are structured objects, not strings — this allows
 * typed arguments (StoreInTable needs a table) and the generated
 * documentation.
 */
interface TechnicalRequirement
{
    public function describe(): string;
}
