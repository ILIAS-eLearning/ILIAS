<?php

declare(strict_types = 1);

/**
 * Get members of a certain source. While, e.g., groups and courses share the
 * common participant-mechanism, roles and orgus have differnt concepts.
 * This is to provide a facade for StudyProgrammes.
 *
 */
interface ilStudyProgrammeMembershipSourceReader
{
	/**
	 * @return int[]
	 */
	public function getMemberIds(): array;
}
