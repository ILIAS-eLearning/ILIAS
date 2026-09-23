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
class ilObjLTIConsumer extends ilObject2
{
    public const string TABLE_NAME = 'lti_consumer_settings';

    /**
     * The tool replaces the ILIAS page.
     */
    public const string LAUNCH_METHOD_OWN_WIN = 'ownWin';
    public const string LAUNCH_METHOD_NEW_WIN = 'newWin';
    public const string LAUNCH_METHOD_EMBEDDED = 'embedded';

    private int $tool_id = 0;
    private ?ilLTITool $tool = null;
    private string $launch_method = self::LAUNCH_METHOD_NEW_WIN;
    private string $custom_params = '';
    private ?ilLTI1p1ConsumerObjectCredentials $lti_1p1_credentials = null;

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
        $this->getLti1p1Credentials()->assignFromDbRow($row);
    }

    protected function doCreate(bool $clone_mode = false): void
    {
        $this->doUpdate();
    }

    protected function doUpdate(): void
    {
        global $DIC;

        $DIC->database()->replace(
            self::TABLE_NAME,
            ['obj_id' => ['integer', $this->getId()]],
            $this->getLti1p1Credentials()->getDbFields() + [
                'provider_id' => ['integer', $this->getToolId()],
                'launch_method' => ['text', $this->getLaunchMethod()],
                'custom_params' => ['text', $this->getCustomParams()],
            ]
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
