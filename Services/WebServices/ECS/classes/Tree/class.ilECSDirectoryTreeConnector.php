<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 *
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilECSDirectoryTreeConnector extends ilECSConnector
{

    /**
     * Constructor
     * @param ilECSSetting $settings
     */
    public function __construct(ilECSSetting $settings = null)
    {
        parent::__construct($settings);
    }

    /**
     * Get directory tree
     * @return ilECSResult
     * @throws ilECSConnectorException
     */
    public function getDirectoryTrees($a_mid = 0)
    {
        $this->path_postfix = '/campusconnect/directory_trees';

        try {
            $this->prepareConnection();
            $this->setHeader(array());
            $this->addHeader('Accept', 'text/uri-list');
            $this->addHeader('X-EcsQueryStrings', 'all=true');
            if ($a_mid) {
                $this->addHeader('X-EcsReceiverMemberships', $a_mid);
            }

            $this->curl->setOpt(CURLOPT_HTTPHEADER, $this->getHeader());
            $res = $this->call();

            $ecsResult = new ilECSResult($res, ilECSResult::RESULT_TYPE_URL_LIST);
            return $ecsResult->getResult();
        } catch (ilCurlConnectionException $exc) {
            throw new ilECSConnectorException('Error calling ECS service: ' . $exc->getMessage());
        }
    }

    /**
     * Get single directory tree
     * @return array an array of ecs cms directory tree entries
     */
    public function getDirectoryTree($tree_id)
    {
        $this->path_postfix = '/campusconnect/directory_trees/' . (int) $tree_id;

        try {
            $this->prepareConnection();
            $this->setHeader(array());
            $this->addHeader('Accept', 'text/uri-list');
            $this->curl->setOpt(CURLOPT_HTTPHEADER, $this->getHeader());
            $res = $this->call();
            
            if (substr($res, 0, 4) == 'http') {
                $json = file_get_contents($res);
                $ecs_result = new ilECSResult($json);
            } else {
                $ecs_result = new ilECSResult($res);
            }
            return $ecs_result;
        } catch (ilCurlConnectionException $e) {
            throw new ilECSConnectorException('Error calling ECS service: ' . $e->getMessage());
        }
    }
}
