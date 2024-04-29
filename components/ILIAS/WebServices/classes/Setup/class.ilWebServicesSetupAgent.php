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

use ILIAS\Setup;
use ILIAS\Refinery;
use ILIAS\UI;

class ilWebServicesSetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    protected Refinery\Factory $refinery;

    public function __construct(Refinery\Factory $refinery)
    {
        $this->refinery = $refinery;
    }

    /**
     * @inheritdoc
     */
    public function hasConfig(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getConfigInput(Setup\Config $config = null): UI\Component\Input\Container\Form\FormInput
    {
        throw new \LogicException("Not yet implemented.");
    }

    /**
     * @inheritdoc
     */
    public function getArrayToConfigTransformation(): Refinery\Transformation
    {
        return $this->refinery->custom()->transformation(function ($data) {
            return new \ilWebServicesSetupConfig(
                (bool) ($data["soap_user_administration"] ?? false),
                $data["soap_wsdl_path"] ?? "",
                (int) ($data["soap_connect_timeout"] ?? ilSoapClient::DEFAULT_CONNECT_TIMEOUT),
                (int) ($data["soap_response_timeout"] ?? ilSoapClient::DEFAULT_RESPONSE_TIMEOUT),
                $data["rpc_server_host"] ?? "",
                (int) ($data["rpc_server_port"] ?? 0),
            );
        });
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(Setup\Config $config = null): Setup\Objective
    {
        return new ilWebServicesConfigStoredObjective($config);
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Setup\Config $config = null): Setup\Objective
    {
        $wsrv_objective = new Setup\Objective\NullObjective();
        if (!is_null($config)) {
            $wsrv_objective = new ilWebServicesConfigStoredObjective($config);
        }
        return new ILIAS\Setup\ObjectiveCollection(
            'Updates of Services/WebServices',
            false,
            $wsrv_objective,
            new ilDatabaseUpdateStepsExecutedObjective(
                new ilECSUpdateSteps8()
            ),
            new ilDatabaseUpdateStepsExecutedObjective(
                new ilSoapWsdlPathUpdateStep()
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function getBuildObjective(): Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritdoc
     */
    public function getStatusObjective(Setup\Metrics\Storage $storage): Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritDoc
     */
    public function getMigrations(): array
    {
        return [];
    }
}
