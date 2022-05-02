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
/*
    PHP port of ADL SeqRollupRule.java
    @author Hendrik Holtmann <holtmann@mac.com>

    This .php file is GPL licensed (see above) but based on
    SeqRollupRule.java by ADL Co-Lab, which is licensed as:

    Advanced Distributed Learning Co-Laboratory (ADL Co-Lab) Hub grants you
    ("Licensee") a non-exclusive, royalty free, license to use, modify and
    redistribute this software in source and binary code form, provided that
    i) this copyright notice and license appear on all copies of the software;
    and ii) Licensee does not utilize the software in a manner which is
    disparaging to ADL Co-Lab Hub.

    This software is provided "AS IS," without a warranty of any kind.  ALL
    EXPRESS OR IMPLIED CONDITIONS, REPRESENTATIONS AND WARRANTIES, INCLUDING
    ANY IMPLIED WARRANTY OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE
    OR NON-INFRINGEMENT, ARE HEREBY EXCLUDED.  ADL Co-Lab Hub AND ITS LICENSORS
    SHALL NOT BE LIABLE FOR ANY DAMAGES SUFFERED BY LICENSEE AS A RESULT OF
    USING, MODIFYING OR DISTRIBUTING THE SOFTWARE OR ITS DERIVATIVES.  IN NO
    EVENT WILL ADL Co-Lab Hub OR ITS LICENSORS BE LIABLE FOR ANY LOST REVENUE,
    PROFIT OR DATA, OR FOR DIRECT, INDIRECT, SPECIAL, CONSEQUENTIAL,
    INCIDENTAL OR PUNITIVE DAMAGES, HOWEVER CAUSED AND REGARDLESS OF THE
    THEORY OF LIABILITY, ARISING OUT OF THE USE OF OR INABILITY TO USE
    SOFTWARE, EVEN IF ADL Co-Lab Hub HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH
    DAMAGES.
*/


    define("ROLLUP_ACTION_NOCHANGE", 0);
    define("ROLLUP_ACTION_SATISFIED", 1);
    define("ROLLUP_ACTION_NOTSATISFIED", 2);
    define("ROLLUP_ACTION_COMPLETED", 3);
    define("ROLLUP_ACTION_INCOMPLETE", 4);
    
    define("ROLLUP_CONSIDER_ALWAYS", "always");
    define("ROLLUP_CONSIDER_ATTEMPTED", "ifAttempted");
    define("ROLLUP_CONSIDER_NOTSKIPPED", "ifNotSkipped");
    define("ROLLUP_CONSIDER_NOTSUSPENDED", "ifNotSuspended");

    define("ROLLUP_SET_ALL", "all");
    define("ROLLUP_SET_ANY", "any");
    define("ROLLUP_SET_NONE", "none");
    define("ROLLUP_SET_ATLEASTCOUNT", "atLeastCount");
    define("ROLLUP_SET_ATLEASTPERCENT", "atLeastPercent");
    
    class SeqRollupRule
    {
        public int $mAction = ROLLUP_ACTION_SATISFIED;
        
        public string $mChildActivitySet = ROLLUP_SET_ALL;
   
        public int $mMinCount = 0;
            
        public float $mMinPercent = 0.0;
        
        public ?array $mConditions = null;
        
        public function __construct()
        {
            //$this->mRules=$iRules;
        }
        
        public function setRollupAction(string $iAction) : void
        {
            if ($iAction === "satisfied") {
                $this->mAction = ROLLUP_ACTION_SATISFIED;
            } elseif ($iAction === "notSatisfied") {
                $this->mAction = ROLLUP_ACTION_NOTSATISFIED;
            } elseif ($iAction === "completed") {
                $this->mAction = ROLLUP_ACTION_COMPLETED;
            } elseif ($iAction === "incomplete") {
                $this->mAction = ROLLUP_ACTION_INCOMPLETE;
            }
        }
    }
