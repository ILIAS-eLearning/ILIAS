<?php

declare(strict_types=1);

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

/**
 * Class ilLTIConsumerGradeService
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/LTIConsumer
 */

class ilLTIConsumerGradeService extends ilLTIConsumerServiceBase
{
    /** Read-only access to Gradebook services */
    public const GRADESERVICE_READ = 1;

    /** Full access to Gradebook services */
    public const GRADESERVICE_FULL = 2;

    /** Scope for full access to Lineitem service */
    public const SCOPE_GRADESERVICE_LINEITEM = 'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem';

    /** Scope for full access to Lineitem service */
    public const SCOPE_GRADESERVICE_LINEITEM_READ = 'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem.readonly';

    /** Scope for access to Result service */
    public const SCOPE_GRADESERVICE_RESULT_READ = 'https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly';

    /** Scope for access to Score service */
    public const SCOPE_GRADESERVICE_SCORE = 'https://purl.imsglobal.org/spec/lti-ags/scope/score';


    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->id = 'gradebookservice';
        $this->name = 'ilLTIConsumerGradeService';
    }

    /**
     * Get the resources for this service.
     */
    public function getResources(): array
    {
        // The containers should be ordered in the array after their elements.
        // Lineitems should be after lineitem.
        if (empty($this->resources)) {
            $this->resources = array();
            $this->resources[] = new ilLTIConsumerGradeServiceLineItem($this);
            $this->resources[] = new ilLTIConsumerGradeServiceLineItems($this);
            $this->resources[] = new ilLTIConsumerGradeServiceResults($this);
            $this->resources[] = new ilLTIConsumerGradeServiceScores($this);
        }
        return $this->resources;
    }

    /**
     * Get the scope(s) permitted for this service.
     * ToDo: make configurable
     */
    public function getPermittedScopes(): array
    {
        $scopes = array();
        $scopes[] = self::SCOPE_GRADESERVICE_LINEITEM;
        $scopes[] = self::SCOPE_GRADESERVICE_LINEITEM_READ;
        $scopes[] = self::SCOPE_GRADESERVICE_RESULT_READ;
        $scopes[] = self::SCOPE_GRADESERVICE_SCORE;
        return $scopes;
    }

    /**
     * Get the scopes defined by this service.
     */
    public function getScopes(): array
    {
        return [
            self::SCOPE_GRADESERVICE_LINEITEM_READ,
            self::SCOPE_GRADESERVICE_RESULT_READ,
            self::SCOPE_GRADESERVICE_SCORE,
            self::SCOPE_GRADESERVICE_LINEITEM
        ];
    }
}
