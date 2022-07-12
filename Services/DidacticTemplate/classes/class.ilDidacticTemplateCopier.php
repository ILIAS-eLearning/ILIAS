<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Copy a didactic template and all subitems
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplate
 */
class ilDidacticTemplateCopier
{
    private int $tpl_id = 0;
    private int $new_tpl_id = 0;

    public function __construct(int $a_tpl_id)
    {
        $this->tpl_id = $a_tpl_id;
    }

    public static function appendCopyInfo(string $a_orig_title) : string
    {
        global $DIC;

        $db = $DIC->database();
        $lng = $DIC->language();

        $query = 'SELECT title FROM didactic_tpl_settings ' .
            'WHERE title = ' . $db->quote($a_orig_title, 'text');
        $res = $db->query($query);
        $num = 0;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            ++$num;
        }
        if (!$num) {
            return $a_orig_title;
        }
        if ($num === 1) {
            return $a_orig_title . ' ' . $lng->txt('copy_of_suffix');
        }
        return $a_orig_title . ' ' . sprintf($lng->txt('copy_n_of_suffix'), $num);
    }

    public function getTemplateId() : int
    {
        return $this->tpl_id;
    }

    public function getNewTemplateId() : int
    {
        return $this->new_tpl_id;
    }

    /**
     * Start copy process
     */
    public function start() : void
    {
        $orig = new ilDidacticTemplateSetting($this->getTemplateId());
        $copy = clone $orig;
        $copy->save();
        $this->new_tpl_id = $copy->getId();

        // copy icon
        $copy->getIconHandler()->copy($orig);
        foreach (ilDidacticTemplateActionFactory::getActionsByTemplateId($this->getTemplateId()) as $action) {
            $action->setTemplateId($this->getNewTemplateId());
            $new = clone $action;
            $new->save();
        }

        $trans = $orig->getTranslationObject();
        $copy_trans = $trans->copy($this->new_tpl_id);
        $copy_trans->addLanguage($trans->getDefaultLanguage(), $copy->getTitle(), $copy->getDescription(), true, true);
        $copy_trans->save();
    }
}
