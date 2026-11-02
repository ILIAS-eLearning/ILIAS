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

namespace ILIAS\Data\Privacy\Source;

/**
 * Marks a catalogue of the personal data columns of one component. Only
 * catalogues may name tables and columns as literals (checked by the
 * PreferKnownSourcesRule); everybody else uses the catalogue's getters.
 *
 * A component keeps its catalogue next to its code, e.g.
 * `components/ILIAS/Certificate/src/Privacy/CertificateSources.php`.
 *
 * A catalogue MUST be constructible without arguments and its getters MUST
 * only return sources built from constants: the documentation generator
 * calls them to name tables and columns.
 */
interface SourceCatalogue
{
}
