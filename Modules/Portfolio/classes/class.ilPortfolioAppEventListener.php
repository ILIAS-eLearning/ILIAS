<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilLearningSequenceAppEventListener
 * @author killing@leifos.de
 * @ingroup ModulesPortfolio
 */
class ilPortfolioAppEventListener
{
    public static function handleEvent($component, $event, $parameter)
    {
        switch ($component) {
            case "Services/Object":
                switch ($event) {
                    case "beforeDeletion":
                        self::beforeDeletion($parameter);
                        break;
                }
                break;
        }
    }

    /**
     * @param array $parameter
     */
    protected static function beforeDeletion($parameter)
    {
        if (is_object($parameter["object"])) {
            /** @var ilObject $obj */
            $obj = $parameter["object"];
            if (get_class($obj) == "ilObjBlog") {
                $blog_id = $obj->getId();
                $action = new ilPortfolioPageAction();
                $action->deletePagesOfBlog($blog_id);
            }
        }
    }
}
