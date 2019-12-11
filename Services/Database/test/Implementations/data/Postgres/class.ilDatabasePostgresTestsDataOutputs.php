<?php
require_once('./Services/Database/test/Implementations/data/class.ilDatabaseCommonTestsDataOutputs.php');

/**
 * Class ilDatabaseCommonTestsDataOutputs
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDatabasePostgresTestsDataOutputs extends ilDatabaseCommonTestsDataOutputs
{

    /**
     * @param $table_name
     * @return string
     */
    public function getCreationQueryBuildByILIAS($table_name)
    {
        return "CREATE TABLE $table_name (id INT NOT NULL, is_online SMALLINT DEFAULT NULL, is_default SMALLINT DEFAULT 1, latitude FLOAT8 DEFAULT NULL, longitude FLOAT8 DEFAULT NULL, elevation FLOAT8 DEFAULT NULL, address VARCHAR(256) DEFAULT NULL NULL, init_mob_id INT DEFAULT NULL, comment_mob_id INT DEFAULT NULL, container_id INT DEFAULT NULL, big_data TEXT)";
    }


    /**
     * @return array
     */
    public function getPrimaryInfo($table_name = '')
    {
        return array(
            'name'   => $table_name . '_pk',
            'fields' => array(
                'id' => array(
                    'position' => 1,
                    'sorting'  => 'ascending',
                ),
            ),
        );
    }


    /**
     * @param bool $with_fulltext
     * @param string $table_name
     * @return array
     */
    public function getIndexInfo($with_fulltext = false, $table_name = '')
    {
        return array(
            0 => array(
                'name'     => $table_name . '_i1',
                'fulltext' => false,
                'fields'   => array(
                    'init_mob_id' => array(
                        'position' => 1,
                        'sorting'  => 'ascending',
                    ),
                ),
            ),
        );
    }


    /**
     * @param string $table_name
     * @return array
     */
    public function getTableConstraints($table_name = '')
    {
        return array(
            0 => $table_name . '_pk',
        );
    }


    /**
     * @param string $table_name
     * @return array
     */
    public function getNativeTableIndices($table_name = '', $fulltext = false)
    {
        if ($fulltext) {
            return array(
                0 => $table_name . '_i1',
                1 => $table_name . '_i2',
            );
        }

        return array(
            0 => $table_name . '_i1',
        );
    }


    public function getTableFieldDefinition()
    {
        return array(
            0 => array(
                'notnull'    => false,
                'nativetype' => 'int4',
                'length'     => 4,
                'unsigned'   => false,
                'default'    => null,
                'type'       => 'integer',
                'mdb2type'   => 'integer',
            ),
        );
    }


    /**
     * @param $table_name
     * @return array
     */
    public function getListTables($table_name)
    {
        $tables = parent::getListTables($table_name);
        unset($tables[0]);
        return $tables;
    }


    /**
     * @param $table_name
     * @return array
     */
    public function getTableSequences($table_name)
    {
        return array(
            0   => 'addressbook_mlist',
            1   => 'addressbook_mlist_ass',
            2   => 'adm_settings_template',
            3   => 'adv_md_record',
            4   => 'adv_mdf_definition',
            5   => 'aicc_object',
            6   => 'aicc_units',
            7   => 'ass_log',
            8   => 'benchmark',
            9   => 'booking_entry',
            10  => 'booking_object',
            11  => 'booking_reservation',
            12  => 'booking_reservation_group',
            13  => 'booking_schedule',
            14  => 'bookmark_data',
            15  => 'bookmark_social_bm',
            16  => 'cal_categories',
            17  => 'cal_ch_group',
            18  => 'cal_entries',
            19  => 'cal_notification',
            20  => 'cal_rec_exclusion',
            21  => 'cal_recurrence_rules',
            22  => 'chatroom_admconfig',
            23  => 'chatroom_history',
            24  => 'chatroom_prooms',
            25  => 'chatroom_psessions',
            26  => 'chatroom_sessions',
            27  => 'chatroom_settings',
            28  => 'chatroom_smilies',
            29  => 'chatroom_uploads',
            30  => 'cmi_comment',
            31  => 'cmi_correct_response',
            32  => 'cmi_interaction',
            33  => 'cmi_node',
            34  => 'cmi_objective',
            35  => 'conditions',
            36  => 'cp_node',
            37  => 'crs_archives',
            38  => 'crs_f_definitions',
            39  => 'crs_file',
            40  => 'crs_objective_lm',
            41  => 'crs_objective_qst',
            42  => 'crs_objective_tst',
            43  => 'crs_objectives',
            44  => 'crs_start',
            45  => 'didactic_tpl_a',
            46  => 'didactic_tpl_fp',
            47  => 'didactic_tpl_settings',
            48  => 'ecs_cmap_rule',
            49  => 'ecs_cms_data',
            50  => 'ecs_container_mapping',
            51  => 'ecs_course_assignments',
            52  => 'ecs_crs_mapping_atts',
            53  => 'ecs_events',
            54  => 'ecs_remote_user',
            55  => 'ecs_server',
            56  => 'event',
            57  => 'event_appointment',
            58  => 'event_file',
            59  => 'exc_assignment',
            60  => 'exc_crit',
            61  => 'exc_crit_cat',
            62  => 'exc_returned',
            63  => 'frm_data',
            64  => 'frm_notification',
            65  => 'frm_posts',
            66  => 'frm_posts_deleted',
            67  => 'frm_posts_tree',
            68  => 'frm_threads',
            69  => 'glossary_definition',
            70  => 'glossary_term',
            71  => 'help_module',
            72  => 'help_tooltip',
            73  => 'history',
            74  => 'il_bibl_attribute',
            75  => 'il_bibl_entry',
            76  => 'il_bibl_settings',
            77  => 'il_blog_posting',
            78  => 'il_custom_block',
            79  => 'il_dcl_data',
            80  => 'il_dcl_field',
            81  => 'il_dcl_field_prop_s_b',
            82  => 'il_dcl_record',
            83  => 'il_dcl_record_field',
            84  => 'il_dcl_stloc1_value',
            85  => 'il_dcl_stloc2_value',
            86  => 'il_dcl_stloc3_value',
            87  => 'il_dcl_table',
            88  => 'il_dcl_view',
            89  => 'il_exc_team',
            90  => 'il_exc_team_log',
            91  => 'il_external_feed_block',
            92  => 'il_gc_memcache_server',
            93  => 'il_md_cpr_selections',
            94  => 'il_meta_annotation',
            95  => 'il_meta_classification',
            96  => 'il_meta_contribute',
            97  => 'il_meta_description',
            98  => 'il_meta_educational',
            99  => 'il_meta_entity',
            100 => 'il_meta_format',
            101 => 'il_meta_general',
            102 => 'il_meta_identifier',
            103 => 'il_meta_identifier_',
            104 => 'il_meta_keyword',
            105 => 'il_meta_language',
            106 => 'il_meta_lifecycle',
            107 => 'il_meta_location',
            108 => 'il_meta_meta_data',
            109 => 'il_meta_relation',
            110 => 'il_meta_requirement',
            111 => 'il_meta_rights',
            112 => 'il_meta_tar',
            113 => 'il_meta_taxon',
            114 => 'il_meta_taxon_path',
            115 => 'il_meta_technical',
            116 => 'il_new_item_grp',
            117 => 'il_news_item',
            118 => 'il_poll_answer',
            119 => 'il_qpl_qst_fq_res',
            120 => 'il_qpl_qst_fq_res_unit',
            121 => 'il_qpl_qst_fq_ucat',
            122 => 'il_qpl_qst_fq_unit',
            123 => 'il_qpl_qst_fq_var',
            124 => 'il_rating_cat',
            125 => $table_name,
            126 => 'il_wiki_page',
            127 => 'ldap_rg_mapping',
            128 => 'ldap_role_assignments',
            129 => 'ldap_server_settings',
            130 => 'link_check',
            131 => 'lm_data',
            132 => 'lm_menu',
            133 => 'loc_tst_assignments',
            134 => 'mail',
            135 => 'mail_man_tpl',
            136 => 'mail_obj_data',
            137 => 'media_item',
            138 => 'mep_item',
            139 => 'note',
            140 => 'notification_data',
            141 => 'notification_osd',
            142 => 'obj_stat_log',
            143 => 'object_data',
            144 => 'object_reference',
            145 => 'object_reference_ws',
            146 => 'openid_provider',
            147 => 'orgu_types',
            148 => 'page_layout',
            149 => 'page_style_usage',
            150 => 'pg_amd_page_list',
            151 => 'prg_settings',
            152 => 'prg_translations',
            153 => 'prg_type',
            154 => 'prg_type_adv_md_rec',
            155 => 'prg_usr_assignments',
            156 => 'prg_usr_progress',
            157 => 'qpl_a_cloze',
            158 => 'qpl_a_errortext',
            159 => 'qpl_a_essay',
            160 => 'qpl_a_imagemap',
            161 => 'qpl_a_matching',
            162 => 'qpl_a_mc',
            163 => 'qpl_a_mdef',
            164 => 'qpl_a_mterm',
            165 => 'qpl_a_ordering',
            166 => 'qpl_a_sc',
            167 => 'qpl_a_textsubset',
            168 => 'qpl_fb_generic',
            169 => 'qpl_fb_specific',
            170 => 'qpl_hint_tracking',
            171 => 'qpl_hints',
            172 => 'qpl_num_range',
            173 => 'qpl_questionpool',
            174 => 'qpl_questions',
            175 => 'qpl_sol_sug',
            176 => 'rbac_log',
            177 => 'rbac_operations',
            178 => 'reg_er_assignments',
            179 => 'reg_registration_codes',
            180 => 'role_desktop_items',
            181 => 'sahs_sc13_seq_node',
            182 => 'sahs_sc13_seq_templts',
            183 => 'sahs_sc13_tree_node',
            184 => 'sc_resource_dependen',
            185 => 'sc_resource_file',
            186 => 'scorm_object',
            187 => 'search_data',
            188 => 'shib_role_assignment',
            189 => 'skl_level',
            190 => 'skl_profile',
            191 => 'skl_self_eval',
            192 => 'skl_tree_node',
            193 => 'sty_media_query',
            194 => 'style_parameter',
            195 => 'style_template',
            196 => 'svy_anonymous',
            197 => 'svy_answer',
            198 => 'svy_category',
            199 => 'svy_constraint',
            200 => 'svy_finished',
            201 => 'svy_inv_usr',
            202 => 'svy_material',
            203 => 'svy_phrase',
            204 => 'svy_phrase_cat',
            205 => 'svy_qblk',
            206 => 'svy_qblk_qst',
            207 => 'svy_qpl',
            208 => 'svy_qst_constraint',
            209 => 'svy_qst_matrixrows',
            210 => 'svy_qst_oblig',
            211 => 'svy_qtype',
            212 => 'svy_question',
            213 => 'svy_relation',
            214 => 'svy_settings',
            215 => 'svy_svy',
            216 => 'svy_svy_qst',
            217 => 'svy_times',
            218 => 'svy_variable',
            219 => 'sysc_groups',
            220 => 'sysc_tasks',
            221 => 'tax_node',
            222 => 'tos_acceptance_track',
            223 => 'tos_versions',
            224 => 'tst_active',
            225 => 'tst_manual_fb',
            226 => 'tst_mark',
            227 => 'tst_rnd_cpy',
            228 => 'tst_rnd_qpl_title',
            229 => 'tst_rnd_quest_set_qpls',
            230 => 'tst_solutions',
            231 => 'tst_test_defaults',
            232 => 'tst_test_question',
            233 => 'tst_test_result',
            234 => 'tst_test_rnd_qst',
            235 => 'tst_tests',
            236 => 'tst_times',
            237 => 'udf_definition',
            238 => 'usr_account_codes',
            239 => 'usr_data_multi',
            240 => 'usr_ext_profile_page',
            241 => 'usr_portfolio_page',
            242 => 'webr_items',
            243 => 'webr_params',
            244 => 'write_event',
            245 => 'xhtml_page',
            246 => 'xmlnestedset',
            247 => 'xmlnestedsettmp',
            248 => 'xmltags',
            249 => 'xmlvalue',
        );
    }
}
