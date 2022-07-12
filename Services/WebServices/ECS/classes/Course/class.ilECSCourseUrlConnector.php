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
 * Connector for writing ecs course urls
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSCourseUrlConnector extends ilECSConnector
{
    /**
     * Send url of newly created courses to ecs
     * @throws ilECSConnectorException
     */
    public function addUrl(ilECSCourseUrl $url, $a_target_mid) : void
    {
        $this->logger->info(__METHOD__ . ': Add new course url ...');

        $this->path_postfix = '/campusconnect/course_urls';
        
        try {
            $this->prepareConnection();

            $this->addHeader('Content-Type', 'application/json');
            $this->addHeader('Accept', 'application/json');
            $this->addHeader(ilECSConnector::HEADER_MEMBERSHIPS, $a_target_mid);

            $this->curl->setOpt(CURLOPT_HTTPHEADER, $this->getHeader());
            $this->curl->setOpt(CURLOPT_POST, true);
            $this->curl->setOpt(CURLOPT_POSTFIELDS, json_encode($url, JSON_THROW_ON_ERROR));
            
            $this->logger->debug('Sending url ' . print_r(json_encode($url, JSON_THROW_ON_ERROR), true));
            
            $this->call();

            $info = $this->curl->getInfo(CURLINFO_HTTP_CODE);
    
            $this->logger->debug('Checking HTTP status...');
            if ($info !== self::HTTP_CODE_CREATED) {
                $this->logger->debug('Cannot create course url ressource, did not receive HTTP 201. ');
                $this->logger->debug('POST was: ' . json_encode($url, JSON_THROW_ON_ERROR));
                $this->logger->debug('HTTP code: ' . $info);
                throw new ilECSConnectorException('Received HTTP status code: ' . $info);
            }
            $this->logger->debug('... got HTTP 201 (created)');
            //TODO add returning of the new created courseurl id
        } catch (ilCurlConnectionException $exc) {
            throw new ilECSConnectorException('Error calling ECS service: ' . $exc->getMessage());
        }
    }
}
