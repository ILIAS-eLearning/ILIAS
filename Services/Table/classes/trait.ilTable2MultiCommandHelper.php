<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Trait ilTable2MultiCommandHelper
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait ilTable2MultiCommandHelper
{
    protected function isSelectCmdAllSelected() : bool
    {
        return $this->getSelectCmdAll()['select_cmd_all'];
    }

    protected function getMultiCommandArray() : array
    {
        global $DIC;
        $post = $DIC->http()->wrapper()->post();
        $fake_post = [];
        $refinery = $DIC->refinery();
        if ($post->has('table_top_cmd')) {
            $cmd = $post->retrieve('table_top_cmd',
                $refinery->custom()->transformation(function ($item) : ?string {
                    return is_array($item) ? key($item) : null;
                }));
            $cmd_sv = $post->retrieve(
                'cmd_sv',
                $refinery->custom()->transformation(function ($item) use ($cmd) : ?string {
                    return is_array($item) ? $item[$cmd] : null;
                })
            );

            $fake_post[$cmd_sv] = $fake_post[$cmd_sv . "_2"] = $post->has($cmd_sv)
                ? $post->retrieve($cmd_sv, $refinery->to()->string())
                : '';
        }

        return $fake_post;
    }

    protected function getSelectCmdAll() : array
    {
        global $DIC;
        $post = $DIC->http()->wrapper()->post();
        $trans = $DIC->refinery()->kindlyTo()->bool();

        $fake_post = [
            'select_cmd_all' => $post->has('select_cmd_all')
                ? $post->retrieve('select_cmd_all', $trans)
                : false,
            'select_cmd_all2' => $post->has('select_cmd_all2')
                ? $post->retrieve('select_cmd_all2', $trans)
                : false,
        ];
        if ($post->has('select_cmd2')) {
            if ($post->has('select_cmd_all2')) {
                $fake_post["select_cmd_all"] = $fake_post["select_cmd_all2"];
            } else {
                $fake_post["select_cmd_all"] = $fake_post["select_cmd_all2"] = false;
            }
        }
        if ($post->has('select_cmd')) {
            if ($post->has('select_cmd_all')) {
                $fake_post["select_cmd_all2"] = $fake_post["select_cmd_all"];
            } else {
                $fake_post["select_cmd_all"] = $fake_post["select_cmd_all2"] = false;
            }
        }

        return $fake_post;
    }
}
