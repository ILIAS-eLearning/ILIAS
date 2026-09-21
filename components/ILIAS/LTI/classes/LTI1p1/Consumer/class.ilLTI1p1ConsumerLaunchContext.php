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

/**
 * LTI 1.1 copy of the former ilLTIConsumerLaunch, so that LTI1p1 does not depend on other LTI code.
 *
 * @author      Saúl Díaz <sdiaz@surlabs.com>
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 */
class ilLTI1p1ConsumerLaunchContext
{
    private ?array $context = null;
    protected int $ref_id;

    public function __construct(int $a_ref_id)
    {
        $this->ref_id = $a_ref_id;
    }

    /**
     * Returns the context in which the object is launched: the outermost matching course or group,
     * or the innermost category or the root node when there is none.
     *
     * @param array|null $a_valid_types  list of valid types
     * @return array|null  context array ("ref_id", "title", "type")
     */
    public function getContext(?array $a_valid_types = array('crs', 'grp', 'cat', 'root')): ?array
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $tree = $DIC->repositoryTree();

        if (!isset($this->context)) {
            $this->context = array();

            // check fromm inner to outer
            $path = array_reverse($tree->getPathFull($this->ref_id));
            foreach ($path as $row) {
                if (in_array($row['type'], $a_valid_types)) {
                    // take an existing inner context outside a course
                    if (in_array($row['type'], array('cat', 'root')) && !empty($this->context)) {
                        break;
                    }

                    $this->context['id'] = $row['child'];
                    $this->context['title'] = $row['title'];
                    $this->context['type'] = $row['type'];

                    // don't break to get the most outer course or group
                }
            }
        }
        return $this->context;
    }




    public static function getLTIContextType(string $a_type): string
    {
        return match ($a_type) {
            "grp" => "https://purl.imsglobal.org/vocab/lis/v2/course#Group",
            default => "https://purl.imsglobal.org/vocab/lis/v2/course#CourseOffering",
        };
    }
}
