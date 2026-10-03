<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, see <https://www.gnu.org/licenses/>.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

class ilWikiPageCollector implements ilCOPageCollectorInterface
{
    public function getAllPageIds(int $obj_id): array
    {
        $pages = [];

        foreach (ilWikiPage::getAllWikiPages($obj_id) as $page) {
            $pages[] = [
                'parent_type' => 'wpg',
                'id' => $page['id'],
                'lang' => $page['lang'],
            ];
        }

        return $pages;
    }
}
