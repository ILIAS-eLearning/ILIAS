<?php

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

declare(strict_types=1);

namespace ILIAS\Blog\Navigation;

use ILIAS\Blog\InternalDomainService;
use ILIAS\Blog\InternalGUIService;
use ILIAS\Blog\Posting\Posting;
use ILIAS\Blog\Navigation\Link\LinkBuilder;

class AuthorBlockGUI
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected LinkBuilder $link_builder
    ) {
    }

    public function render(
        bool $show_inactive = false
    ): string {
        $obj_id = \ilObject::_lookupObjId($this->gui->standardRequest()->getRefId());
        $posting_list = $this->domain->postingList(
            $obj_id,
            $show_inactive
        );
        $authors = $posting_list->getAuthors();

        // filter out deleted users
        $authors = array_filter($authors, function ($id) {
            return \ilObject::_lookupType($id) == "usr";
        });

        if (count($authors) > 1) {
            $list = array();
            foreach ($authors as $user_id) {
                if ($user_id) {
                    $url = $this->link_builder->forAuthor($user_id);

                    $base_name = \ilUserUtil::getNamePresentation($user_id);
                    if (str_starts_with($base_name, "[")) {
                        $name = \ilUserUtil::getNamePresentation($user_id, true);
                        $sort = $name;
                    } else {
                        $name = \ilUserUtil::getNamePresentation(
                            $user_id,
                            true,
                            false,
                            "",
                            false,
                            true,
                            false
                        );
                        $name_arr = \ilObjUser::_lookupName($user_id);
                        $sort = $name_arr["lastname"] . " " . $name_arr["firstname"];
                    }

                    $idx = trim(strip_tags((string) $sort)) . "///" . $user_id;
                    $list[$idx] = array($name, $url);
                }
            }
            ksort($list);

            $wtpl = new \ilTemplate("tpl.blog_list_navigation_authors.html", true, true, "components/ILIAS/Blog");

            $wtpl->setCurrentBlock("author");
            foreach ($list as $author) {
                $wtpl->setVariable("TXT_AUTHOR", $author[0]);
                $wtpl->setVariable("URL_AUTHOR", $author[1]);
                $wtpl->parseCurrentBlock();
            }

            return $wtpl->get();
        }
        return "";
    }
}
