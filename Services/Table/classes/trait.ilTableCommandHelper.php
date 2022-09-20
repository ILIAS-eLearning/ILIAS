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
 * This trait can be used in GUI classes where an ilTable2GUI is used.
 * Up till now $_POST manipulations were made by ilCtrl which managed
 * the table GUI actions. Since direct $_POST manipulations will be
 * prohibited in the future, this trait delivers "fake" $_POST arrays
 * that should be used instead. Because this $_POST manipulations were
 * only necessary because of the poorly implemented table action
 * management, this trait is already marked as deprecated to enforce a
 * new implementation of it.
 * @author Fabian Schmid <fabian@sr.solutions>
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 * @deprecated
 */
trait ilTableCommandHelper
{
    /**
     * Returns a fake $_POST array that contains data used for multi commands,
     * when a table called addMultiItemSelectionButton().
     * Instead of accessing $_POST[<<$a_sel_var>>] this can be used:
     * $this->getMultiCommandPostArray()[<<$a_sel_var>>].
     * @return string[]
     * @see ilTable2GUI::addMultiItemSelectionButton()
     * @deprecated
     */
    protected function getMultiCommandPostArray(): array
    {
        global $DIC;

        $refinery = $DIC->refinery();
        $post_parameters = $DIC->http()->wrapper()->post();
        $fake_post_array = [];

        if ($post_parameters->has('table_top_cmd')) {
            // this var contains the pressed table-top or -bottom command.
            $multi_cmd = $post_parameters->retrieve(
                'table_top_cmd',
                $refinery->custom()->transformation(
                    static function ($post_array): ?string {
                        return is_array($post_array) ? key($post_array) : null;
                    }
                )
            );

            // this var contains the bottom or top value key according to
            // which command has been pressed.
            $multi_value_key = $post_parameters->retrieve(
                'cmd_sv',
                $refinery->custom()->transformation(
                    static function ($post_array) use ($multi_cmd): ?string {
                        return is_array($post_array) ? $post_array[$multi_cmd] : null;
                    }
                )
            );

            // this builds a fake post array similarly in ILIAS 7, where
            // bottom and top values are set to the submitted one.
            $fake_post_array[$multi_value_key] =
            $fake_post_array[$multi_value_key . "_2"] =
                $post_parameters->has($multi_value_key) ?
                    $post_parameters->retrieve($multi_value_key, $refinery->to()->string()) :
                    '';
        }

        return $fake_post_array;
    }

    /**
     * Returns a fake $_POST array that contains data accessed by
     * select_cmd_all or select_cmd_all2.
     * Instead of $_POST['select_cmd_all'] this can be used:
     * $this->getSelectAllPostArray()['select_cmd_all'].
     * @return bool[]
     * @deprecated
     */
    protected function getSelectAllPostArray(): array
    {
        global $DIC;

        $transformation = $DIC->refinery()->kindlyTo()->bool();
        $post_parameters = $DIC->http()->wrapper()->post();
        $fake_post_array = [
            'select_cmd_all' => $post_parameters->has('select_cmd_all') ?
                $post_parameters->retrieve('select_cmd_all', $transformation) :
                false,
            'select_cmd_all2' => $post_parameters->has('select_cmd_all2') ?
                $post_parameters->retrieve('select_cmd_all2', $transformation) :
                false,
        ];

        if ($post_parameters->has('select_cmd2')) {
            if ($post_parameters->has('select_cmd_all2')) {
                $fake_post_array["select_cmd_all"] = $fake_post_array["select_cmd_all2"];
            } else {
                $fake_post_array["select_cmd_all"] = $fake_post_array["select_cmd_all2"] = false;
            }
        }

        if ($post_parameters->has('select_cmd')) {
            if ($post_parameters->has('select_cmd_all')) {
                $fake_post_array["select_cmd_all2"] = $fake_post_array["select_cmd_all"];
            } else {
                $fake_post_array["select_cmd_all"] = $fake_post_array["select_cmd_all2"] = false;
            }
        }

        return $fake_post_array;
    }
}
