<?php
namespace ILIAS\Modules\Course\Infrastructure\Repository\Doctrine;
use Doctrine\ORM\Mapping\ClassMetaData,
	Doctrine\ORM\Query\Filter\SQLFilter;

class CourseMemberBaseFilter extends SQLFilter
{
	public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
	{
		// Check if the entity implements the LocalAware interface
		/*if (!$targetEntity->reflClass->implementsInterface('LocaleAware')) {
			return "";
		}*/

		return 'o0_.member = 1';
	}
}
