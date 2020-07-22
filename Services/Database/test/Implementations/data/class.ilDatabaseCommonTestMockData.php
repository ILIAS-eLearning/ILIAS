<?php

/**
 * Class ilDatabaseCommonTestMockData
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilDatabaseCommonTestMockData
{

    /**
     * @return string
     */
    public function getNow()
    {
        return "NOW()";
    }


    /**
     * @return string
     */
    public function getLike()
    {
        return " UPPER(column) LIKE(UPPER('22'))";
    }


    /**
     * @return string
     */
    public function getLocate()
    {
        return " LOCATE( needle,mystring,5) ";
    }


    /**
     * @return string
     */
    public function getConcat($allow_null = true)
    {
        if ($allow_null) {
            return " CONCAT(COALESCE(o,''),COALESCE(t,''),COALESCE(t,'')) ";
        } else {
            return " CONCAT(o,t,t) ";
        }
    }


    /**
     * @return array
     */
    public function getDBFields()
    {
        $fields = array(
            'id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true,
            ),
            'is_online' => array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => false,
            ),
            'is_default' => array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => false,
                'default' => 1,
            ),
            'latitude' => array(
                'type' => 'float',
            ),
            'longitude' => array(
                'type' => 'float',
            ),
            'elevation' => array(
                'type' => 'float',
            ),
            'address' => array(
                'type' => 'text',
                'length' => 256,
                'notnull' => false,
            ),
            'init_mob_id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => false,
            ),
            'comment_mob_id' => array(
                'type' => 'integer',
                'length' => 4,
            ),
            'container_id' => array(
                'type' => 'integer',
                'length' => 4,
            ),
            'big_data' => array(
                'type' => 'clob',
            ),
        );

        return $fields;
    }


    /**
     * @param bool $update_mob_id
     * @param bool $blob_null
     * @return array
     */
    public function getInputArray($update_mob_id = false, $blob_null = true, $with_clob = true)
    {
        $fields = array(
            'id' => array(
                'integer',
                56,
            ),
            'is_online' => array(
                'integer',
                true,
            ),
            'is_default' => array(
                'integer',
                false,
            ),
            'latitude' => array(
                'float',
                47.059830,
            ),
            'longitude' => array(
                'float',
                7.624028,
            ),
            'elevation' => array(
                'float',
                2.56,
            ),
            'address' => array(
                'text',
                'Farbweg 9, 3400 Burgdorf',
            ),
            'init_mob_id' => array(
                'integer',
                $update_mob_id ? $update_mob_id : 78,
            ),
            'comment_mob_id' => array(
                'integer',
                69,
            ),
            'container_id' => array(
                'integer',
                456,
            ),
        );
        if ($with_clob) {
            $fields['big_data'] = array(
                'clob',
                $blob_null ? null : 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.',
            );
        }

        return $fields;
    }


    /**
     * @return array
     */
    public function getInputArrayForTransaction()
    {
        $fields = array(
            'id' => array(
                'integer',
                123456,
            ),
            'is_online' => array(
                'integer',
                true,
            ),
            'is_default' => array(
                'integer',
                false,
            ),
        );

        return $fields;
    }


    /**
     * @param $table_name
     * @return string
     */
    abstract public function getInsertQuery($table_name);


    /**
     * @return mixed
     */
    public function getTableCreateSQL($tablename, $engine)
    {
        return "CREATE TABLE `" . $tablename . "` (
  `id` int(11) NOT NULL,
  `is_online` tinyint(4) DEFAULT NULL,
  `is_default` tinyint(4) DEFAULT '1',
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `elevation` double DEFAULT NULL,
  `address` varchar(256) DEFAULT NULL,
  `init_mob_id` int(11) DEFAULT NULL,
  `comment_mob_id` int(11) DEFAULT NULL,
  `container_id` int(11) DEFAULT NULL,
  `big_data` longtext,
  PRIMARY KEY (`id`)) ENGINE=$engine DEFAULT CHARSET=utf8";
    }


    /**
     * @return mixed
     */
    public function getTableCreateSQLAfterRename($tablename, $engine, $supports_fulltext)
    {
        $add_idex = '';
        if ($supports_fulltext) {
            $add_idex = ", FULLTEXT KEY `i2_idx` (`address`)";
        }

        return "CREATE TABLE `" . $tablename . "` (
  `id` int(11) NOT NULL,
  `is_online` tinyint(4) DEFAULT NULL,
  `is_default` tinyint(4) DEFAULT '1',
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `elevation` double DEFAULT NULL,
  `address` varchar(256) DEFAULT NULL,
  `init_mob_id` int(11) DEFAULT NULL,
  `comment_mob_id_altered` int(11) DEFAULT NULL,
  `container_id` int(11) DEFAULT NULL,
  `big_data` longtext,
  PRIMARY KEY (`id`), KEY `i1_idx` (`init_mob_id`)$add_idex) ENGINE=$engine DEFAULT CHARSET=utf8";
    }


    /**
     * @return mixed
     */
    public function getTableCreateSQLAfterAlter($tablename, $engine, $supports_fulltext)
    {
        $add_idex = '';
        if ($supports_fulltext) {
            $add_idex = ", FULLTEXT KEY `i2_idx` (`address`)";
        }

        return "CREATE TABLE `" . $tablename . "` (
  `id` int(11) NOT NULL,
  `is_online` tinyint(4) DEFAULT NULL,
  `is_default` tinyint(4) DEFAULT '1',
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `elevation` double DEFAULT NULL,
  `address` varchar(256) DEFAULT NULL,
  `init_mob_id` int(11) DEFAULT NULL,
  `comment_mob_id_altered` varchar(250) DEFAULT NULL,
  `container_id` int(11) DEFAULT NULL,
  `big_data` longtext,
  PRIMARY KEY (`id`), KEY `i1_idx` (`init_mob_id`)$add_idex) ENGINE=$engine DEFAULT CHARSET=utf8";
    }
}
