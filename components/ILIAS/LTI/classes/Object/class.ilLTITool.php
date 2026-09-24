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
 * An external tool ILIAS can launch, stored in lti_ext_provider. A row holds the settings of both LTI
 * versions, so lti_version decides which of them are in use.
 *
 * This class owns every statement against lti_ext_provider: the instance reads the settings a launch
 * needs, the static methods serve the administration screens.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTITool
{
    public const string TABLE_NAME = 'lti_ext_provider';

    public const string VERSION_1P1 = 'LTI-1p0';
    public const string VERSION_ADVANTAGE = '1.3.0';

    /**
     * The tool is no longer available, neither for new nor for existing objects. The value 1 in
     * between means that only the objects already using it may launch it.
     */
    public const int AVAILABILITY_NONE = 0;
    public const int AVAILABILITY_CREATE = 2;

    public const int PRIVACY_IDENT_IL_UUID_USER_ID = 0;

    /**
     * Tools released for everybody.
     */
    public const int SCOPE_GLOBAL = 0;
    /**
     * Tools a single user defined for their own objects.
     */
    public const int SCOPE_USER = 1;
    /**
     * What the given user may pick when creating an LTI object.
     */
    public const int SCOPE_SELECTABLE = 2;

    public const int PRIVACY_NAME_NONE = 0;
    public const int PRIVACY_NAME_FIRSTNAME = 1;
    public const int PRIVACY_NAME_LASTNAME = 2;
    public const int PRIVACY_NAME_FULLNAME = 3;

    private int $id = 0;
    private string $title = '';
    private string $description = '';
    private int $availability = self::AVAILABILITY_NONE;
    private string $url = '';
    private int $privacy_ident = self::PRIVACY_IDENT_IL_UUID_USER_ID;
    private int $privacy_name = self::PRIVACY_NAME_NONE;
    private string $lti_version = self::VERSION_1P1;
    private int $creator = 0;
    private bool $external = false;
    private string $keywords = '';
    private bool $global = false;
    private bool $include_user_picture = false;
    private bool $always_learner = false;
    private bool $use_tool_id = false;
    private string $custom_params = '';
    private bool $has_outcome = false;
    private float $mastery_score = 0.8;
    private bool $use_xapi = false;
    private string $xapi_launch_url = '';
    private string $xapi_launch_key = '';
    private string $xapi_launch_secret = '';
    private string $xapi_activity_id = '';
    private bool $grade_synchronization = false;
    private bool $content_item = false;
    private ?ilLTI1p1ConsumerProviderCredentials $lti_1p1_credentials = null;

    public function __construct(int $id = 0)
    {
        if ($id > 0) {
            $this->assignFromDbRow(self::read($id));
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * The user who defined the tool, 0 when it came with the installation.
     */
    public function getCreator(): int
    {
        return $this->creator;
    }

    /**
     * True when the operator of the installation has no say over the tool, which users are warned about.
     */
    public function isExternal(): bool
    {
        return $this->external;
    }

    /**
     * @return array the keywords to find the tool by, which a new object also takes as its own
     */
    public function getKeywords(): array
    {
        return array_values(array_filter(
            array_map('trim', explode(';', $this->keywords)),
            static fn(string $keyword): bool => $keyword !== ''
        ));
    }

    /**
     * True when the tool is released for everybody instead of belonging to its creator.
     */
    public function isGlobal(): bool
    {
        return $this->global;
    }

    public function getAvailability(): int
    {
        return $this->availability;
    }

    /**
     * True when the given user may create an object for the tool, the same rule as SCOPE_SELECTABLE.
     */
    public function isSelectableBy(int $user_id): bool
    {
        return $this->id > 0
            && $this->availability === self::AVAILABILITY_CREATE
            && ($this->global || $this->creator === $user_id);
    }

    /**
     * Which of the two LTI versions the tool speaks, and with it how it authenticates.
     */
    public function getLtiVersion(): string
    {
        return $this->lti_version;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getPrivacyIdent(): int
    {
        return $this->privacy_ident;
    }

    public function getPrivacyName(): int
    {
        return $this->privacy_name;
    }

    public function getIncludeUserPicture(): bool
    {
        return $this->include_user_picture;
    }

    public function getAlwaysLearner(): bool
    {
        return $this->always_learner;
    }

    /**
     * True when all objects of this tool launch with the same resource link.
     */
    public function getUseToolId(): bool
    {
        return $this->use_tool_id;
    }

    public function getCustomParams(): string
    {
        return $this->custom_params;
    }

    /**
     * True when the tool reports results, which is what the learning progress of its objects builds on.
     */
    public function hasOutcome(): bool
    {
        return $this->has_outcome;
    }

    /**
     * The share of the maximum result, between 0 and 1, a new object requires by default.
     */
    public function getMasteryScore(): float
    {
        return $this->mastery_score;
    }

    /**
     * True when the tool sends xAPI statements to the LRS below, which its objects can report on.
     */
    public function getUseXapi(): bool
    {
        return $this->use_xapi;
    }

    public function getXapiLaunchUrl(): string
    {
        return $this->xapi_launch_url;
    }

    /**
     * Where the reports on the statements of the tool are queried. The tool only gives the statements
     * endpoint of its LRS, from which the aggregation endpoint of the LRS is derived.
     */
    public function getXapiAggregateEndpoint(): string
    {
        return str_replace('data/xAPI', 'api/statements/aggregate', $this->xapi_launch_url);
    }

    public function getXapiBasicAuth(): string
    {
        return ilCmiXapiLrsType::buildBasicAuth($this->xapi_launch_key, $this->xapi_launch_secret);
    }

    public function getXapiLaunchKey(): string
    {
        return $this->xapi_launch_key;
    }

    public function getXapiLaunchSecret(): string
    {
        return $this->xapi_launch_secret;
    }

    /**
     * The xAPI activity of the tool, empty when each object has to name its own.
     */
    public function getXapiActivityId(): string
    {
        return $this->xapi_activity_id;
    }

    /**
     * True when the tool sends grades through LTI Advantage Assignment and Grade Services.
     */
    public function isGradeSynchronization(): bool
    {
        return $this->grade_synchronization;
    }

    /**
     * True when the tool lets the user pick its content through LTI Advantage Deep Linking.
     */
    public function isContentItem(): bool
    {
        return $this->content_item;
    }

    public function getLti1p1Credentials(): ilLTI1p1ConsumerProviderCredentials
    {
        return $this->lti_1p1_credentials ??= new ilLTI1p1ConsumerProviderCredentials();
    }

    public function getKey(): string
    {
        return $this->getLti1p1Credentials()->getKey();
    }

    public function getSecret(): string
    {
        return $this->getLti1p1Credentials()->getSecret();
    }

    /**
     * True when each object sets its own OAuth1 key and secret instead of using the ones of the tool.
     */
    public function isKeyCustomizable(): bool
    {
        return $this->getLti1p1Credentials()->isKeyCustomizable();
    }

    private function assignFromDbRow(array $row): void
    {
        $this->id = (int) ($row['id'] ?? 0);
        $this->title = (string) ($row['title'] ?? '');
        $this->description = (string) ($row['description'] ?? '');
        $this->availability = (int) ($row['availability'] ?? self::AVAILABILITY_NONE);
        $this->lti_version = (string) ($row['lti_version'] ?? self::VERSION_1P1);
        $this->creator = (int) ($row['creator'] ?? 0);
        $this->external = (bool) ($row['external_provider'] ?? false);
        $this->keywords = (string) ($row['keywords'] ?? '');
        $this->global = (bool) ($row['global'] ?? false);
        $this->url = (string) ($row['provider_url'] ?? '');
        $this->privacy_ident = (int) ($row['privacy_ident'] ?? self::PRIVACY_IDENT_IL_UUID_USER_ID);
        $this->privacy_name = (int) ($row['privacy_name'] ?? self::PRIVACY_NAME_NONE);
        $this->include_user_picture = (bool) ($row['inc_usr_pic'] ?? false);
        $this->always_learner = (bool) ($row['always_learner'] ?? false);
        $this->use_tool_id = (bool) ($row['use_provider_id'] ?? false);
        $this->custom_params = (string) ($row['custom_params'] ?? '');
        $this->has_outcome = (bool) ($row['has_outcome'] ?? false);
        $this->mastery_score = (float) ($row['mastery_score'] ?? 0.8);
        $this->use_xapi = (bool) ($row['use_xapi'] ?? false);
        $this->xapi_launch_url = (string) ($row['xapi_launch_url'] ?? '');
        $this->xapi_launch_key = (string) ($row['xapi_launch_key'] ?? '');
        $this->xapi_launch_secret = (string) ($row['xapi_launch_secret'] ?? '');
        $this->xapi_activity_id = (string) ($row['xapi_activity_id'] ?? '');
        $this->grade_synchronization = (bool) ($row['grade_synchronization'] ?? false);
        $this->content_item = (bool) ($row['content_item'] ?? false);
        $this->getLti1p1Credentials()->assignFromDbRow($row);
    }

    /**
     * @return array the whole row, empty when the tool does not exist
     */
    public static function read(int $id): array
    {
        $db = self::db();

        return $db->fetchAssoc($db->query(
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = ' . $db->quote($id, 'integer')
        )) ?? [];
    }

    public static function lookupVersion(int $id): string
    {
        return (string) (self::read($id)['lti_version'] ?? '') === self::VERSION_ADVANTAGE
            ? self::VERSION_ADVANTAGE
            : self::VERSION_1P1;
    }

    /**
     * @return array title indexed by tool id
     */
    public static function lookupTitles(array $ids): array
    {
        $db = self::db();
        $result = $db->query(
            'SELECT id, title FROM ' . self::TABLE_NAME . ' WHERE ' . $db->in('id', $ids, false, 'integer')
        );

        $titles = [];
        while ($row = $db->fetchAssoc($result)) {
            $titles[(int) $row['id']] = (string) $row['title'];
        }

        return $titles;
    }

    /**
     * @param array $fields the columns to store, in the format of ilDBInterface::insert()
     * @param bool $global false for a tool only its creator may use
     * @return int the id of the new tool
     */
    public static function create(array $fields, int $creator_id, bool $global = true): int
    {
        $db = self::db();
        $id = $db->nextId(self::TABLE_NAME);
        $db->insert(self::TABLE_NAME, $fields + [
            'id' => ['integer', $id],
            'creator' => ['integer', $creator_id],
            'global' => ['integer', (int) $global],
            'privacy_comment_default' => ['text', ''],
            'launch_method' => ['text', 'newWin'],
        ]);

        return $id;
    }

    /**
     * @param array $fields the columns to store, in the format of ilDBInterface::update()
     */
    public static function update(int $id, array $fields): void
    {
        self::db()->update(self::TABLE_NAME, $fields, ['id' => ['integer', $id]]);
    }

    public static function delete(array $ids): void
    {
        $db = self::db();
        $db->manipulate('DELETE FROM ' . self::TABLE_NAME . ' WHERE ' . $db->in('id', $ids, false, 'integer'));
    }

    /**
     * Number of LTI objects using one of the given tools. A tool in use must not be deleted.
     */
    public static function countUsages(array $ids): int
    {
        $db = self::db();
        $result = $db->query(
            'SELECT COUNT(*) cnt FROM ' . ilObjLTIConsumer::TABLE_NAME
            . ' WHERE ' . $db->in('provider_id', $ids, false, 'integer')
        );

        return (int) $db->fetchAssoc($result)['cnt'];
    }

    /**
     * Number of the given tools whose scope cannot be changed, either because they have no creator
     * or because they already have the wanted scope.
     */
    public static function countWithFixedScope(array $ids, bool $global): int
    {
        $db = self::db();
        $result = $db->query(
            'SELECT COUNT(*) cnt FROM ' . self::TABLE_NAME . ' WHERE ' . $db->in('id', $ids, false, 'integer')
            . ' AND (creator IS NULL OR creator = 0 OR global = ' . $db->quote((int) $global, 'integer') . ')'
        );

        return (int) $db->fetchAssoc($result)['cnt'];
    }

    /**
     * Releases the given tools for everybody or resets them to the scope of their creator.
     */
    public static function updateScope(array $ids, bool $global, int $accepted_by): void
    {
        $db = self::db();
        $db->manipulate(
            'UPDATE ' . self::TABLE_NAME . ' SET global = ' . $db->quote((int) $global, 'integer')
            . ', accepted_by = ' . $db->quote($global ? $accepted_by : 0, 'integer')
            . ' WHERE ' . $db->in('id', $ids, false, 'integer')
        );
    }

    /**
     * @param array $filter see buildWhere()
     * @return array one row per tool, with its creator and the number of objects using it
     */
    public static function getRows(
        int $scope,
        array $filter,
        int $user_id,
        string $order_by,
        int $limit,
        int $offset
    ): array {
        $db = self::db();
        $db->setLimit($limit, $offset);
        $result = $db->query(
            'SELECT p.id, p.title, p.description, p.category, p.keywords, p.has_outcome, p.external_provider,'
            . ' p.provider_key_customizable, p.availability, p.creator, p.lti_version,'
            . ' p.creator = ' . $db->quote($user_id, 'integer') . ' own_provider,'
            . ' u.usr_id creator_exists, TRIM(CONCAT_WS(\' \', u.title, u.firstname, u.lastname)) creator_name,'
            . ' (' . self::getUsagesQuery(false) . ') usages_untrashed,'
            . ' (' . self::getUsagesQuery(true) . ') usages_trashed'
            . ' FROM ' . self::TABLE_NAME . ' p LEFT JOIN usr_data u ON u.usr_id = p.creator'
            . ' WHERE ' . self::buildWhere($scope, $filter, $user_id)
            . ' ORDER BY ' . $order_by
        );

        $rows = [];
        while ($row = $db->fetchAssoc($result)) {
            $rows[] = $row;
        }

        return $rows;
    }

    public static function countRows(int $scope, array $filter, int $user_id): int
    {
        $db = self::db();
        $result = $db->query(
            'SELECT COUNT(*) cnt FROM ' . self::TABLE_NAME . ' p WHERE ' . self::buildWhere($scope, $filter, $user_id)
        );

        return (int) $db->fetchAssoc($result)['cnt'];
    }

    /**
     * @param array $filter see buildUsageFrom()
     * @return array one row per object using a released tool
     */
    public static function getUsageRows(array $filter, string $order_by, int $limit, int $offset): array
    {
        $db = self::db();
        $db->setLimit($limit, $offset);
        $result = $db->query(
            'SELECT p.title, p.lti_version, r.ref_id, r.deleted IS NOT NULL trashed, od.type, od.title used_by'
            . self::buildUsageFrom($filter)
            . ' ORDER BY ' . $order_by
        );

        $rows = [];
        while ($row = $db->fetchAssoc($result)) {
            $rows[] = $row;
        }

        return $rows;
    }

    public static function countUsageRows(array $filter): int
    {
        $db = self::db();

        return (int) $db->fetchAssoc($db->query('SELECT COUNT(*) cnt' . self::buildUsageFrom($filter)))['cnt'];
    }

    /**
     * @param array $filter the columns title and keywords as a substring, category and lti_version as a
     *                      value, and has_outcome, external_provider and provider_key_customizable as a flag
     */
    private static function buildWhere(int $scope, array $filter, int $user_id): string
    {
        $db = self::db();
        $conditions = [match ($scope) {
            self::SCOPE_USER => 'p.global = 0',
            self::SCOPE_SELECTABLE => 'p.availability = ' . $db->quote(self::AVAILABILITY_CREATE, 'integer')
                . ' AND (p.global = 1 OR p.creator = ' . $db->quote($user_id, 'integer') . ')',
            default => 'p.global = 1',
        }];

        foreach (['title', 'keywords'] as $column) {
            if ((string) ($filter[$column] ?? '') !== '') {
                $conditions[] = $db->like('p.' . $column, 'text', '%' . $filter[$column] . '%');
            }
        }
        foreach (['has_outcome', 'external_provider', 'provider_key_customizable'] as $column) {
            if (isset($filter[$column])) {
                $conditions[] = 'p.' . $column . ' = ' . $db->quote((int) $filter[$column], 'integer');
            }
        }
        foreach (['category', 'lti_version'] as $column) {
            if ((string) ($filter[$column] ?? '') !== '') {
                $conditions[] = 'p.' . $column . ' = ' . $db->quote($filter[$column], 'text');
            }
        }

        return implode(' AND ', $conditions);
    }

    /**
     * @param array $filter the tool title and the object title as a substring, lti_version as a value
     *                      and trashed as a flag
     */
    private static function buildUsageFrom(array $filter): string
    {
        $db = self::db();
        $conditions = ['p.global = 1'];

        if ((string) ($filter['title'] ?? '') !== '') {
            $conditions[] = $db->like('p.title', 'text', '%' . $filter['title'] . '%');
        }
        if ((string) ($filter['used_by'] ?? '') !== '') {
            $conditions[] = $db->like('od.title', 'text', '%' . $filter['used_by'] . '%');
        }
        if (isset($filter['trashed'])) {
            $conditions[] = 'r.deleted IS ' . ($filter['trashed'] ? 'NOT NULL' : 'NULL');
        }
        if ((string) ($filter['lti_version'] ?? '') !== '') {
            $conditions[] = 'p.lti_version = ' . $db->quote($filter['lti_version'], 'text');
        }

        return ' FROM ' . self::TABLE_NAME . ' p'
            . ' JOIN ' . ilObjLTIConsumer::TABLE_NAME . ' s ON s.provider_id = p.id'
            . ' JOIN object_reference r ON r.obj_id = s.obj_id'
            . ' JOIN object_data od ON od.obj_id = s.obj_id'
            . ' WHERE ' . implode(' AND ', $conditions);
    }

    private static function getUsagesQuery(bool $trashed): string
    {
        return 'SELECT COUNT(s.obj_id) FROM ' . ilObjLTIConsumer::TABLE_NAME . ' s'
            . ' JOIN object_reference r ON r.obj_id = s.obj_id AND r.deleted IS ' . ($trashed ? 'NOT NULL' : 'NULL')
            . ' WHERE s.provider_id = p.id';
    }

    private static function db(): ilDBInterface
    {
        global $DIC;

        return $DIC->database();
    }
}
