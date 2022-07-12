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
 */
class ilECSCourseUrl
{
    public const COURSE_URL_PREFIX = 'campusconnect/course/';

    private ilLogger $logger;
    
    // json fields
    public string $cms_lecture_id = '';
    public string $ecs_course_url = '';
    public ?array $lms_course_urls = null;
    
    public function __construct()
    {
        global $DIC;
        
        $this->logger = $DIC->logger()->wsrv();
    }
    
    /**
     * Set lecture id
     */
    public function setCmsLectureId(string $a_id) : void
    {
        $this->cms_lecture_id = $a_id;
    }
    
    /**
     * Set ecs course id
     */
    public function setECSId(int $a_id) : void
    {
        $this->ecs_course_url = self::COURSE_URL_PREFIX . $a_id;
    }
    
    /**
     * Add lms url
     */
    public function addLmsCourseUrls(ilECSCourseLmsUrl $lms_url = null) : void
    {
        $this->lms_course_urls[] = $lms_url;
    }
    
    /**
     * Send urls to ecs
     */
    public function send(ilECSSetting $setting, $ecs_receiver_mid) : void
    {
        try {
            $con = new ilECSCourseUrlConnector($setting);
            $con->addUrl($this, $ecs_receiver_mid);
            
            //$this->logger->info('Received new url id ' . $url_id);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
