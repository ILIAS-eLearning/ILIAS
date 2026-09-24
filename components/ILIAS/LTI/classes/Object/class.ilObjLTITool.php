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
 * A repository object of type lti: one external tool made available at one place of the repository.
 * Its settings are stored in lti_consumer_settings, the tool itself in lti_ext_provider.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilObjLTITool extends ilObject2
{
    public const string TABLE_NAME = 'lti_consumer_settings';

    /**
     * The tool replaces the ILIAS page.
     */
    public const string LAUNCH_METHOD_OWN_WIN = 'ownWin';
    public const string LAUNCH_METHOD_NEW_WIN = 'newWin';
    public const string LAUNCH_METHOD_EMBEDDED = 'embedded';

    public const int HIGHSCORE_SHOW_ALL_TABLES = 1;
    public const int HIGHSCORE_SHOW_TOP_TABLE = 2;
    public const int HIGHSCORE_SHOW_OWN_TABLE = 3;

    private int $tool_id = 0;
    private ?ilLTITool $tool = null;
    private string $launch_method = self::LAUNCH_METHOD_NEW_WIN;
    private string $custom_params = '';
    private ?ilLTI1p1ConsumerObjectCredentials $lti_1p1_credentials = null;
    private float $mastery_score = 0.5;
    private bool $use_xapi = false;
    private string $custom_activity_id = '';
    private bool $statements_report_enabled = false;
    private bool $highscore_enabled = false;
    private bool $highscore_achieved_ts = true;
    private bool $highscore_percentage = true;
    private bool $highscore_wtime = true;
    private bool $highscore_own_table = true;
    private bool $highscore_top_table = true;
    private int $highscore_top_num = 10;

    protected function initType(): void
    {
        $this->type = 'lti';
    }

    public function getToolId(): int
    {
        return $this->tool_id;
    }

    public function setToolId(int $tool_id): void
    {
        $this->tool_id = $tool_id;
        $this->tool = null;
    }

    public function getTool(): ilLTITool
    {
        return $this->tool ??= new ilLTITool($this->getToolId());
    }

    public function getLaunchMethod(): string
    {
        return $this->launch_method;
    }

    public function setLaunchMethod(string $launch_method): void
    {
        $this->launch_method = $launch_method;
    }

    public function isLaunchMethodOwnWin(): bool
    {
        return $this->launch_method === self::LAUNCH_METHOD_OWN_WIN;
    }

    public function isLaunchMethodEmbedded(): bool
    {
        return $this->launch_method === self::LAUNCH_METHOD_EMBEDDED;
    }

    public function getCustomParams(): string
    {
        return $this->custom_params;
    }

    public function setCustomParams(string $custom_params): void
    {
        $this->custom_params = $custom_params;
    }

    /**
     * The share of the maximum result, between 0 and 1, a user has to reach to complete the object.
     */
    public function getMasteryScore(): float
    {
        return $this->mastery_score;
    }

    public function setMasteryScore(float $mastery_score): void
    {
        $this->mastery_score = $mastery_score;
    }

    /**
     * True when the object reports on the xAPI statements of its tool, which has to send them.
     */
    public function getUseXapi(): bool
    {
        return $this->use_xapi && $this->getTool()->getUseXapi();
    }

    public function setUseXapi(bool $use_xapi): void
    {
        $this->use_xapi = $use_xapi;
    }

    /**
     * The activity the object names itself, which only counts when its tool has none.
     */
    public function getCustomActivityId(): string
    {
        return $this->custom_activity_id;
    }

    public function setCustomActivityId(string $custom_activity_id): void
    {
        $this->custom_activity_id = $custom_activity_id;
    }

    /**
     * The xAPI activity the statements of the object belong to.
     */
    public function getActivityId(): string
    {
        $tool_activity_id = $this->getTool()->getXapiActivityId();

        return $tool_activity_id !== '' ? $tool_activity_id : $this->custom_activity_id;
    }

    /**
     * True when every user may see their own statements, not only the users who may read the outcomes.
     */
    public function isStatementsReportEnabled(): bool
    {
        return $this->statements_report_enabled;
    }

    public function setStatementsReportEnabled(bool $enabled): void
    {
        $this->statements_report_enabled = $enabled;
    }

    public function getHighscoreEnabled(): bool
    {
        return $this->highscore_enabled;
    }

    public function setHighscoreEnabled(bool $enabled): void
    {
        $this->highscore_enabled = $enabled;
    }

    public function getHighscoreAchievedTS(): bool
    {
        return $this->highscore_achieved_ts;
    }

    public function setHighscoreAchievedTS(bool $shown): void
    {
        $this->highscore_achieved_ts = $shown;
    }

    public function getHighscorePercentage(): bool
    {
        return $this->highscore_percentage;
    }

    public function setHighscorePercentage(bool $shown): void
    {
        $this->highscore_percentage = $shown;
    }

    public function getHighscoreWTime(): bool
    {
        return $this->highscore_wtime;
    }

    public function setHighscoreWTime(bool $shown): void
    {
        $this->highscore_wtime = $shown;
    }

    public function getHighscoreOwnTable(): bool
    {
        return $this->highscore_own_table;
    }

    public function getHighscoreTopTable(): bool
    {
        return $this->highscore_top_table;
    }

    /**
     * Which rankings are shown: the one around the user, the best users or both.
     */
    public function getHighscoreMode(): int
    {
        return match (true) {
            $this->highscore_own_table && $this->highscore_top_table => self::HIGHSCORE_SHOW_ALL_TABLES,
            $this->highscore_top_table => self::HIGHSCORE_SHOW_TOP_TABLE,
            default => self::HIGHSCORE_SHOW_OWN_TABLE,
        };
    }

    public function setHighscoreMode(int $mode): void
    {
        $this->highscore_own_table = $mode !== self::HIGHSCORE_SHOW_TOP_TABLE;
        $this->highscore_top_table = $mode !== self::HIGHSCORE_SHOW_OWN_TABLE;
    }

    public function getHighscoreTopNum(): int
    {
        return $this->highscore_top_num > 0 ? $this->highscore_top_num : 10;
    }

    public function setHighscoreTopNum(int $top_num): void
    {
        $this->highscore_top_num = $top_num;
    }

    /**
     * The reports of CmiXapi read the statements of an LTI object as generic xAPI of any kind.
     */
    public function isMixedContentType(): bool
    {
        return true;
    }

    public function getContentType(): string
    {
        return ilObjCmiXapi::CONT_TYPE_GENERIC;
    }

    /**
     * The name the reports of CmiXapi use for the tool.
     */
    public function getProvider(): ilLTITool
    {
        return $this->getTool();
    }

    public static function getInstance(int $id = 0, bool $reference = true): self
    {
        return new self($id, $reference);
    }

    public function getLti1p1Credentials(): ilLTI1p1ConsumerObjectCredentials
    {
        return $this->lti_1p1_credentials ??= new ilLTI1p1ConsumerObjectCredentials();
    }

    /**
     * OAuth1 key of this object, the one of the tool when it is not customizable.
     */
    public function getLaunchKey(): string
    {
        return ilLTI1p1ConsumerLaunchParameterBuilder::resolveLaunchKey(
            $this->getTool(),
            $this->getLti1p1Credentials()->getKey()
        );
    }

    public function getLaunchSecret(): string
    {
        return ilLTI1p1ConsumerLaunchParameterBuilder::resolveLaunchSecret(
            $this->getTool(),
            $this->getLti1p1Credentials()->getSecret()
        );
    }

    /**
     * @return array the custom parameters of the object, in the notation foo=bar;foo2=bar2
     */
    public function getCustomParamsArray(): array
    {
        return self::parseCustomParams($this->getCustomParams());
    }

    /**
     * @return array the custom parameters every object of the tool sends
     */
    public static function getToolCustomParamsArray(ilLTITool $tool): array
    {
        return self::parseCustomParams($tool->getCustomParams());
    }

    /**
     * @throws ilWACException
     */
    public function buildLaunchParameters(
        ilCmiXapiUser $cmix_user,
        string $token,
        string $context_type,
        string $context_id,
        string $context_title,
        ?string $return_url = ''
    ): array {
        return ilLTI1p1ConsumerLaunchParameterBuilder::build(
            $this->getTool(),
            $this->getRefId(),
            $this->getId(),
            $this->getTitle(),
            $this->getDescription(),
            $this->getLaunchMethod(),
            $this->getLaunchKey(),
            $this->getLaunchSecret(),
            $this->getCustomParamsArray(),
            $cmix_user,
            $token,
            $context_type,
            $context_id,
            $context_title,
            $return_url
        );
    }

    /**
     * The keywords of the metadata are the ones of the tool: those of the object are replaced by them.
     */
    public function syncKeywordsFromTool(): void
    {
        global $DIC;

        $lom = $DIC->learningObjectMetadata();
        $lom->manipulate($this->getId(), 0, $this->getType())
            ->prepareDelete($lom->paths()->keywords())
            ->execute();

        $keywords = $this->getTool()->getKeywords();
        if ($keywords !== []) {
            $lom->manipulate($this->getId(), 0, $this->getType())
                ->prepareCreateOrUpdate($lom->paths()->keywords(), ...$keywords)
                ->execute();
        }
    }

    /**
     * The base URL of the installation, which the tools use to address ILIAS back.
     */
    public static function getIliasHttpPath(): string
    {
        return rtrim(ILIAS_HTTP_PATH, '/');
    }

    protected function doRead(): void
    {
        global $DIC;

        $db = $DIC->database();
        $row = $db->fetchAssoc($db->queryF(
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE obj_id = %s',
            ['integer'],
            [$this->getId()]
        ));
        if ($row === null) {
            return;
        }

        $this->setToolId((int) $row['provider_id']);
        $this->setLaunchMethod((string) $row['launch_method']);
        $this->setCustomParams((string) $row['custom_params']);
        $this->mastery_score = (float) $row['mastery_score'];
        $this->use_xapi = (bool) $row['use_xapi'];
        $this->custom_activity_id = (string) $row['activity_id'];
        $this->statements_report_enabled = (bool) $row['show_statements'];
        $this->highscore_enabled = (bool) $row['highscore_enabled'];
        $this->highscore_achieved_ts = (bool) $row['highscore_achieved_ts'];
        $this->highscore_percentage = (bool) $row['highscore_percentage'];
        $this->highscore_wtime = (bool) $row['highscore_wtime'];
        $this->highscore_own_table = (bool) $row['highscore_own_table'];
        $this->highscore_top_table = (bool) $row['highscore_top_table'];
        $this->highscore_top_num = (int) $row['highscore_top_num'];
        $this->getLti1p1Credentials()->assignFromDbRow($row);
    }

    protected function doCreate(bool $clone_mode = false): void
    {
        global $DIC;

        $DIC->database()->insert(
            self::TABLE_NAME,
            ['obj_id' => ['integer', $this->getId()]] + $this->getDbFields()
        );
    }

    /**
     * Only the columns this class knows are written, so that the settings of an object coming from an
     * older installation survive until they are modelled here.
     */
    protected function doUpdate(): void
    {
        global $DIC;

        $DIC->database()->update(
            self::TABLE_NAME,
            $this->getDbFields(),
            ['obj_id' => ['integer', $this->getId()]]
        );
    }

    protected function doDelete(): void
    {
        global $DIC;

        $db = $DIC->database();
        $db->manipulateF(
            'DELETE FROM ' . self::TABLE_NAME . ' WHERE obj_id = %s',
            ['integer'],
            [$this->getId()]
        );
    }

    /**
     * @return array
     */
    private function getDbFields(): array
    {
        return $this->getLti1p1Credentials()->getDbFields() + [
            'provider_id' => ['integer', $this->getToolId()],
            'launch_method' => ['text', $this->getLaunchMethod()],
            'custom_params' => ['text', $this->getCustomParams()],
            'mastery_score' => ['float', $this->mastery_score],
            'use_xapi' => ['integer', (int) $this->use_xapi],
            'activity_id' => ['text', $this->custom_activity_id],
            'show_statements' => ['integer', (int) $this->statements_report_enabled],
            'highscore_enabled' => ['integer', (int) $this->highscore_enabled],
            'highscore_achieved_ts' => ['integer', (int) $this->highscore_achieved_ts],
            'highscore_percentage' => ['integer', (int) $this->highscore_percentage],
            'highscore_wtime' => ['integer', (int) $this->highscore_wtime],
            'highscore_own_table' => ['integer', (int) $this->highscore_own_table],
            'highscore_top_table' => ['integer', (int) $this->highscore_top_table],
            'highscore_top_num' => ['integer', $this->highscore_top_num],
        ];
    }

    /**
     * @return array the values of the notation foo=bar;foo2=bar2, indexed by their parameter name
     */
    private static function parseCustomParams(string $params): array
    {
        $parsed = [];
        foreach (preg_split('/; ?/', $params) as $param) {
            $param = explode('=', $param, 2);
            if ($param[0] !== '') {
                $parsed[$param[0]] = $param[1] ?? '';
            }
        }

        return $parsed;
    }
}
