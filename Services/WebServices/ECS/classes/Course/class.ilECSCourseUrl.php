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
 * Represents a ecs course url
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilECSCourseUrl
{
    const COURSE_URL_PREFIX = 'campusconnect/course/';

    private ilLogger $logger;
    
    // json fields
    public string $cms_lecture_id = '';
    public string $ecs_course_url = '';
    public ?array $lms_course_urls = null;
    
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        
        $this->logger = $DIC->logger()->wsrv();
    }
    
    /**
     * Set lecture id
     * @param type $a_id
     */
    public function setCmsLectureId($a_id)
    {
        $this->cms_lecture_id = $a_id;
    }
    
    /**
     * Set ecs course id
     * @param int $a_id
     */
    public function setECSId($a_id)
    {
        $this->ecs_course_url = self::COURSE_URL_PREFIX . $a_id;
    }
    
    /**
     * Add lms url
     * @param ilECSCourseLmsUrl $lms_url
     */
    public function addLmsCourseUrls(ilECSCourseLmsUrl $lms_url = null)
    {
        $this->lms_course_urls[] = $lms_url;
    }
    
    /**
     * Send urls to ecs
     */
    public function send(ilECSSetting $setting, $ecs_receiver_mid)
    {
        try {
            $con = new ilECSCourseUrlConnector($setting);
            $url_id = $con->addUrl($this, $ecs_receiver_mid);
            
            $this->logger->info('Received new url id ' . $url_id);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
