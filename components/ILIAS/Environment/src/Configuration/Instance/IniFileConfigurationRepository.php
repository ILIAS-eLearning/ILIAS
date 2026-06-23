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

namespace ILIAS\Environment\Configuration\Instance;

/**
 * Combined read-write INI repository.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 * @deprecated The read and write responsibilities have been split. Depend on
 *             {@see IniFileReadRepository} for read-only access, or
 *             {@see IniFileWriteRepository} when mutation/persistence is
 *             required. This combined class is kept only for backward
 *             compatibility and will be removed.
 */
class IniFileConfigurationRepository extends IniFileWriteRepository
{
}
