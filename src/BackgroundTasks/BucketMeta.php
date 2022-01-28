<?php

namespace ILIAS\BackgroundTasks;

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
 * Interface BucketMeta
 * @package ILIAS\BackgroundTasks
 * A meta bucket contains infos about a bucket like its percentage, name etc.
 */
interface BucketMeta
{
    public function getUserId() : int;
    
    public function setUserId(int $user_id) : void;
    
    public function getOverallPercentage() : int;
    
    public function setOverallPercentage(int $percentage) : void;
    
    public function setState(int $state) : void;
    
    public function getState() : int;
    
    public function getDescription() : string;
    
    public function getTitle() : string;
}
