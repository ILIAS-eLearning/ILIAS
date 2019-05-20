<?php
/*
 * This file is part of the prooph/php-ddd-cargo-sample.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 06.12.15 - 17:25
 */

namespace ILIAS\Infrastructure\Persistence\Doctrine\Type;

final class IlObjectId extends DoctrineDomainIdType
{
    const NAME = 'obj_id';

	/**
	 * @inheritdoc
	 */
	public function getName(): string
	{
		return self::NAME;
	}


}
