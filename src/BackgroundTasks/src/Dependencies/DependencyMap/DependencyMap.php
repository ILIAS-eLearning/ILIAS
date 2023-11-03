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

namespace ILIAS\BackgroundTasks\Dependencies\DependencyMap;

use ILIAS\DI\Container;

interface DependencyMap
{
    /**
     * @param Container $DIC                      The DIC to take the dependencies from.
     * @param string    $fullyQualifiedDomainName What domain name is requested?
     * @param string    $for                      What class is the dependency for? Also fully
     *                                            qualified domain name.
     * @return mixed
     */
    public function getDependency(Container $DIC, string $fullyQualifiedDomainName, string $for);

    /**
     * @param Container $DIC                      The DIC to take the dependencies from.
     * @param string    $fullyQualifiedDomainName What domain name is requested?
     * @param callable  $map                      (DIC $DIC, string $fullyQualifiedDomainName,
     *                                            string $for) => mixed|null
     * @param string    $for                      What class is the dependency for? Also fully
     *                                            qualified domain name.
     * @return mixed
     */
    public function getDependencyWith(Container $DIC, string $fullyQualifiedDomainName, string $for, callable $map);
}
