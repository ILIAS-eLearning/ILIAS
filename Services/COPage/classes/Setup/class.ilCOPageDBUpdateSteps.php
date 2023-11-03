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

namespace ILIAS\COPage\Setup;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilCOPageDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $field = array(
            'type' => 'integer',
            'length' => 2,
            'notnull' => true,
            'default' => 0
        );

        $this->db->modifyTableColumn("copg_pc_def", "order_nr", $field);
    }

    public function step_2(): void
    {
        $field = array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        );

        $this->db->modifyTableColumn("copg_pc_def", "order_nr", $field);
    }

    public function step_3(): void
    {
        $this->db->update(
            "page_layout",
            [
            "title" => ["text", "Text page with accompanying media"]
        ],
            [    // where
                "title" => ["text", "1A Simple text page with accompanying media"]
            ]
        );
        $this->db->update(
            "page_layout",
            [
            "title" => ["text", "Text page with accompanying media and test"]
        ],
            [    // where
                "title" => ["text", "1C Text page with accompanying media and test"]
            ]
        );
        $this->db->update(
            "page_layout",
            [
            "title" => ["text", "Text page with accompanying media followed by test and text"]
        ],
            [    // where
                "title" => ["text", "1E Text page with accompanying media followed by test and text"]
            ]
        );
        $this->db->update(
            "page_layout",
            [
            "title" => ["text", "Media page with accompanying text and test"]
        ],
            [    // where
                "title" => ["text", "2C Simple media page with accompanying text and test"]
            ]
        );
        $this->db->update(
            "page_layout",
            [
            "title" => ["text", "Vertical component navigation page with media and text	"]
        ],
            [    // where
                "title" => ["text", "7C Vertical component navigation page with media and text"]
            ]
        );
    }

    public function step_4(): void
    {
        if (!$this->db->tableColumnExists('page_object', 'est_reading_time')) {
            $this->db->addTableColumn('page_object', 'est_reading_time', array(
                'type' => 'integer',
                'notnull' => true,
                'length' => 4,
                'default' => 0
            ));
        }
    }

    public function step_5(): void
    {
        $set = $this->db->queryF(
            "SELECT content FROM page_object " .
            " WHERE page_id = %s AND parent_type = %s AND lang = %s",
            ["integer", "text", "text"],
            [5, "stys", "-"]
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            $content = $rec["content"];

            $replacements = [
                ["a4e417c08feebeafb1487e60a2e245a4", "a4e417c08feebeafb1487e60a2e245a5"],
                ["a4e417c08feebeafb1487e60a2e245a4", "a4e417c08feebeafb1487e60a2e245a6"],
                ["a4e417c08feebeafb1487e60a2e245a5", "a4e417c08feebeafb1487e60a2e245a7"],
                ["a4e417c08feebeafb1487e60a2e245a5", "a4e417c08feebeafb1487e60a2e245a8"]
            ];

            foreach ($replacements as $r) {
                $content = preg_replace('/' . $r[0] . '/', $r[1], $content, 1);
            }

            $this->db->update(
                "page_object",
                [
                "content" => ["clob", $content]
            ],
                [    // where
                    "page_id" => ["integer", 5],
                    "parent_type" => ["text", "stys"],
                    "lang" => ["text", '-'],
                ]
            );
        }
    }
    public function step_6(): void
    {
    }
    public function step_7(): void
    {
        $content = <<<EOT
<PageObject><PageContent PCID="0568f23f59f828dd532cd77c66dcea97"><Grid><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="7" WIDTH_L="8" WIDTH_XL="8" PCID="2fe139a171c9276193832c2d64f5822b"><PageContent PCID="1f77eb1d8a478497d69b99d938fda8f"><PlaceHolder ContentClass="Text" Height="500px"/></PageContent></GridCell><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="5" WIDTH_L="4" WIDTH_XL="4" PCID="010960eb5296c409d0b5070e186f033e"><PageContent PCID="2e77eb1d8a478497d69b99d938fda8e"><PlaceHolder ContentClass="Media" Height="500px"/></PageContent></GridCell></Grid></PageContent></PageObject>
EOT;
        $this->db->update(
            "page_object",
            [
                "content" => ["clob", $content]
            ],
            [    // where
                 "page_id" => ["integer", 1],
                 "parent_type" => ["text", "stys"],
                 "lang" => ["text", '-'],
            ]
        );
    }

    public function step_8(): void
    {
        $content = <<<EOT
<PageObject><PageContent PCID="336db62153bc33b955c8eab6b4ba1331"><Grid><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="7" WIDTH_L="8" WIDTH_XL="8" PCID="18772b9425070318d27bb2fdaf6b6bdf"><PageContent PCID="1f77eb1d8a478497d69b99d938fda8f"><PlaceHolder ContentClass="Text" Height="300px"/></PageContent></GridCell><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="5" WIDTH_L="4" WIDTH_XL="4" PCID="4309720c697857f55946691119fd7f10"><PageContent PCID="2e77eb1d8a478497d69b99d938fda8e"><PlaceHolder ContentClass="Media" Height="300px"/></PageContent></GridCell></Grid></PageContent><PageContent PCID="3f77eb1d8a478493d69b99d438fda8f"><PlaceHolder ContentClass="Question" Height="200px"/></PageContent></PageObject>
EOT;
        $this->db->update(
            "page_object",
            [
                "content" => ["clob", $content]
            ],
            [    // where
                 "page_id" => ["integer", 2],
                 "parent_type" => ["text", "stys"],
                 "lang" => ["text", '-'],
            ]
        );
    }

    public function step_9(): void
    {
        $content = <<<EOT
<PageObject><PageContent PCID="6239da5ab9497f14774a2cceb5525c3d"><Grid><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="5" WIDTH_L="4" WIDTH_XL="4" PCID="2f9835a738e7c83dbef27915816b0f5a"><PageContent PCID="2e77eb1d8a478497d69b99d938fda8e"><PlaceHolder ContentClass="Media" Height="500px"/></PageContent></GridCell><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="7" WIDTH_L="8" WIDTH_XL="8" PCID="1d74da9bf94b63e8c85a451399b624d9"><PageContent PCID="1f77eb1d8a478497d69b99d938fda8f"><PlaceHolder ContentClass="Text" Height="500px"/></PageContent></GridCell></Grid></PageContent></PageObject>
EOT;
        $this->db->update(
            "page_object",
            [
                "content" => ["clob", $content]
            ],
            [    // where
                 "page_id" => ["integer", 3],
                 "parent_type" => ["text", "stys"],
                 "lang" => ["text", '-'],
            ]
        );
    }

    public function step_10(): void
    {
        $content = <<<EOT
<PageObject><PageContent PCID="906a03fd9999c5c83a7166e9f9744fec"><Grid><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="5" WIDTH_L="4" WIDTH_XL="4" PCID="a27f25a9b1d8746d1cf3820759f37096"><PageContent PCID="2e77eb1d8a478497d69b99d938fda8e"><PlaceHolder ContentClass="Media" Height="300px"/></PageContent></GridCell><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="7" WIDTH_L="8" WIDTH_XL="8" PCID="8e769877102068dca417222215defaa9"><PageContent PCID="1f77eb1d8a478497d69b99d938fda8f"><PlaceHolder ContentClass="Text" Height="300px"/></PageContent></GridCell></Grid></PageContent><PageContent PCID="3f77eb1d8a478493d69b99d438fda8f"><PlaceHolder ContentClass="Question" Height="200px"/></PageContent></PageObject>
EOT;
        $this->db->update(
            "page_object",
            [
                "content" => ["clob", $content]
            ],
            [    // where
                 "page_id" => ["integer", 4],
                 "parent_type" => ["text", "stys"],
                 "lang" => ["text", '-'],
            ]
        );
    }

    public function step_11(): void
    {
        $content = <<<EOT
<PageObject><PageContent PCID="0fb0511a01dcb9b83d9f21eb0d588a19"><Grid><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="4" WIDTH_L="4" WIDTH_XL="4" PCID="b241816c4270ec842782a664cbe72979"><PageContent PCID="428c956f8035dc8ac59a9412bc19f955"><PlaceHolder Height="250px" ContentClass="Media"/></PageContent><PageContent PCID="6d0291683f92aa84920755184b0da66c"><PlaceHolder Height="250px" ContentClass="Text"/></PageContent></GridCell><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="4" WIDTH_L="4" WIDTH_XL="4" PCID="375c6f40533390645bd1bb58259bec54"><PageContent PCID="41f4e5a703244309231c2d6be0c49231"><PlaceHolder Height="250px" ContentClass="Media"/></PageContent><PageContent PCID="325dac9e34424f129a4e4f9a0c4e37c4"><PlaceHolder Height="250px" ContentClass="Text"/></PageContent></GridCell><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="4" WIDTH_L="4" WIDTH_XL="4" PCID="d8379b454401c9b7071d32efb69cc028"><PageContent PCID="5e744ec7c8784471e4668bcbc5e4b405"><PlaceHolder Height="250px" ContentClass="Media"/></PageContent><PageContent PCID="0ba252ad3ab834e2c8fba58708dc1995"><PlaceHolder Height="250px" ContentClass="Text"/></PageContent></GridCell></Grid></PageContent></PageObject>
EOT;
        $this->db->update(
            "page_object",
            [
                "content" => ["clob", $content]
            ],
            [    // where
                 "page_id" => ["integer", 5],
                 "parent_type" => ["text", "stys"],
                 "lang" => ["text", '-'],
            ]
        );
    }

    public function step_12(): void
    {
        $layout_id = $this->db->nextId("page_layout");
        $this->db->insert("page_layout", array(
            "layout_id" => array("integer", $layout_id),
            "active" => array("integer", 1),
            "title" => array("text", "Leading image with text"),
            "content" => array("clob", ""),
            "description" => array("text", "")
        ));

        $content = <<<EOT
<PageObject><PageContent PCID="6b1a4e68d752380bf108afff7fa66595"><PlaceHolder Height="300px" ContentClass="Media"/></PageContent><PageContent PCID="8535f59bec330f1cc30286898a36356f"><PlaceHolder Height="200px" ContentClass="Text"/></PageContent></PageObject>
EOT;
        $this->db->insert("page_object", array(
            "page_id" => array("integer", $layout_id),
            "parent_type" => array("text", "stys"),
            "content" => array("clob", $content),
            "lang" => array("text", "-")
        ));
    }

    public function step_13(): void
    {
        if ($this->db->tableExists("copg_pc_def")) {
            $query = "UPDATE copg_pc_def SET " . PHP_EOL
                . " component = REPLACE(component, 'Modules', 'components/ILIAS') " . PHP_EOL
                . " WHERE component LIKE ('Modules/%')";

            $this->db->manipulate($query);
        }
    }
}
