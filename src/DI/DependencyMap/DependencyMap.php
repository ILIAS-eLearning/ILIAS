<?php

namespace ILIAS\DI\DependencyMap;

use ILIAS\DI\Container;

interface DependencyMap {

	/**
	 * @param Container $DIC                        The DIC to take the dependencies from.
	 * @param string    $fullyQualifiedDomainName   What domain name is requested?
	 * @param string    $for                        What class is the dependency for? Also fully qualified domain name.
	 *
	 * @return mixed
	 */
	public function getDependency(Container $DIC, string $fullyQualifiedDomainName, string $for);


	/**
	 * @param Container $DIC                        The DIC to take the dependencies from.
	 * @param string    $fullyQualifiedDomainName   What domain name is requested?
	 * @param callable  $map                        (DIC $DIC, string $fullyQualifiedDomainName, string $for) => mixed|null
	 * @param string    $for                        What class is the dependency for? Also fully qualified domain name.
	 *
	 * @return mixed
	 */
	public function getDependencyWith(Container $DIC, string $fullyQualifiedDomainName, string $for, callable $map);
}