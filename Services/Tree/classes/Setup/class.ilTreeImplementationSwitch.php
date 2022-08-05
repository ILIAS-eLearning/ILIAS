<?php declare(strict_types=1);

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

use ILIAS\Setup\Environment;
use ILIAS\Setup\UnachievableException;
use ILIAS\Setup\NullConfig;

class ilTreeImplementationSwitch extends ilSetupObjective
{
    public const NESTED_SET_TO_MATERIALIZED_PATH = 'nested_set_to_materialized_path';
    public const MATERIALIZED_PATH_TO_NESTED_SET = 'materialized_path_to_nested_set';

    private string $mode;

    public function __construct(string $mode)
    {
        parent::__construct(new NullConfig());
        $this->mode = $mode;
    }

    public function getHash() : string
    {
        return hash('sha256', self::class);
    }

    public function getLabel() : string
    {
        if ($this->mode === self::MATERIALIZED_PATH_TO_NESTED_SET) {
            return 'The tree implementation is switched to nested set';
        }

        return 'The tree implementation is switched to materialized path';
    }

    public function isNotable() : bool
    {
        return true;
    }

    public function getPreconditions(Environment $environment) : array
    {
        return [
            new ilSettingsFactoryExistsObjective(),
            new ilDatabaseInitializedObjective(),
        ];
    }

    public function achieve(Environment $environment) : Environment
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $settings_factory = $environment->getResource(Environment::RESOURCE_SETTINGS_FACTORY);

        $is_nested_set = $settings_factory->settingsFor('common')->get('main_tree_impl', 'ns') === 'ns';

        if ($this->mode === self::NESTED_SET_TO_MATERIALIZED_PATH && $is_nested_set) {
            ilMaterializedPathTree::createFromParentRelation($db);

            $db->dropIndexByFields('tree', ['lft']);
            $db->dropIndexByFields('tree', ['path']);
            $db->addIndex('tree', ['path'], 'i4');

            $settings_factory->settingsFor('common')->set('main_tree_impl', 'mp');
        } elseif ($this->mode === self::MATERIALIZED_PATH_TO_NESTED_SET && !$is_nested_set) {
            $renumber_callable = function (ilDBInterface $db) {
                $this->renumberNestedSet($db, 1, 1);
            };
            $ilAtomQuery = $db->buildAtomQuery();
            $ilAtomQuery->addTableLock('tree');

            $ilAtomQuery->addQueryCallable($renumber_callable);
            $ilAtomQuery->run();

            $db->dropIndexByFields('tree', ['lft']);
            $db->dropIndexByFields('tree', ['path']);
            $db->addIndex('tree', ['lft'], 'i4');

            $settings_factory->settingsFor('common')->set('main_tree_impl', 'ns');
        } else {
            throw new UnachievableException('The tree implementation switch does already equal the requested mode');
        }

        return $environment;
    }
    
    private function renumberNestedSet(ilDBInterface $db, int $node_id, int $i) : int
    {
        $db->manipulateF(
            'UPDATE tree SET lft = %s WHERE child = %s',
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
            [$node_id, $i]
        );

        $query = 'SELECT child FROM tree WHERE parent = ' . $db->quote($node_id, ilDBConstants::T_INTEGER) . ' ORDER BY lft';
        $res = $db->query($query);

        $children = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $children[] = (int) $row->child;
        }

        foreach ($children as $child) {
            $i = $this->renumberNestedSet($db, $child, $i + 1);
        }
        $i++;

        if (count($children) > 0) {
            $i += 100;
        }

        $query = 'UPDATE tree SET rgt = %s WHERE child = %s';
        $db->manipulateF(
            $query,
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
            [$i, $node_id]
        );

        return $i;
    }

    public function isApplicable(Environment $environment) : bool
    {
        $settings_factory = $environment->getResource(Environment::RESOURCE_SETTINGS_FACTORY);

        $is_nested_set = $settings_factory->settingsFor('common')->get('main_tree_impl', 'ns') === 'ns';

        if ($this->mode === self::MATERIALIZED_PATH_TO_NESTED_SET) {
            return !$is_nested_set;
        }

        return $is_nested_set;
    }
}
