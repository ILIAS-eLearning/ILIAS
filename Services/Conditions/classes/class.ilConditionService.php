<?php declare(strict_types=1);

/******************************************************************************
 *
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
 *     https://www.ilias.de
 *     https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Condition service
 * @author @leifos.de
 * @ingroup
 */
class ilConditionService
{
    protected ilConditionObjectAdapterInterface $cond_obj_adapter;

    protected function __construct(?ilConditionObjectAdapterInterface $cond_obj_adapter = null)
    {
        if (is_null($cond_obj_adapter)) {
            $this->cond_obj_adapter = new ilConditionObjectAdapter();
        }
    }

    public static function getInstance(ilConditionObjectAdapterInterface $cond_obj_adapter = null) : ilConditionService
    {
        return new self($cond_obj_adapter);
    }

    public function factory() : ilConditionFactory
    {
        return new ilConditionFactory($this->cond_obj_adapter ?? new ilConditionObjectAdapter());
    }

    public function util() : ilConditionUtil
    {
        return new ilConditionUtil();
    }
}
