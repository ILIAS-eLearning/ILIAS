<?php
require_once 'DatabaseTest.php';

class DeleteTest extends DatabaseTest 
{
    var $insert = false;


    function testDeleteRef1()
    {
        if ($this->verbose > -1) {
            print "\n" . ">testDeleteRef1";
        }
        if ($this->verbose > 0) {
            print "\n" . "Delete reference Address => Street";
        }
        $db =& $this->db;

        $result = $db->deleteRef('Address', 'Street');
        if (PEAR::isError($result)) {
            print $result->getMessage();
            $this->assertTrue(false);
            return;
        }
         
        $ref = $db->getRef();
        if (PEAR::isError($ref)) {
            print $ref->getMessage();
            $this->assertTrue(false);
            return;
        } 
        if ($this->verbose > 0) {
            print "\n\nRef:";
            foreach ($ref as $ftable => $references) {
                foreach ($references as $rtable => $ref) {
                    print "\n$ftable => $rtable";
                }
            }
        }
            
        $ref_to = $db->getRefTo();
        if (PEAR::isError($ref_to)) {
            print $ref_to->getMessage();
            $this->assertTrue(false);
            return;
        } 
        if ($this->verbose > 0) {
            print "\n\nRefTo:";
            foreach ($ref_to as $rtable => $references) {
                $s = array();
                foreach ($references as $ftable) {
                    $s[] = $ftable;
                }
                print "\n$rtable <= (" . implode(', ', $s) . ')';
            }
        }

        $link = $db->getLink();
        if (PEAR::isError($link)) {
            print $link->getMessage();
            $this->assertTrue(false);
            return;
        } 
        if ($this->verbose > 0) {
            print "\n\nLink:";
            foreach ($link as $table1 => $list) {
                foreach ($list as $table2 => $links) {
                    $s = array();
                    foreach ($links as $link_table) {
                        $s[] = $link_table;
                    }
                    print "\n$table1, $table2 : (" . 
                          implode(', ', $s) . ')';
                }
            }
        }
    }

    function testDeleteRef2()
    {
        if ($this->verbose > -1) {
            print "\n" . ">testDeleteRef2";
        }
        if ($this->verbose > 0) {
            print "\n" . "Delete reference PersonAddress => Person";
        }
        $db =& $this->db;

        $result = $db->deleteRef('PersonAddress', 'Person');
        if (PEAR::isError($result)) {
            print $result->getMessage();
            $this->assertTrue(false);
            return;
        }
 
        $ref = $db->getRef();
        if (PEAR::isError($ref)) {
            print $ref->getMessage();
            $this->assertTrue(false);
            return;
        } 
        if ($this->verbose > 0) {
            print "\n\nRef:";
            foreach ($ref as $ftable => $references) {
                foreach ($references as $rtable => $ref) {
                    print "\n$ftable => $rtable";
                }
            }
        }
            
        $ref_to = $db->getRefTo();
        if (PEAR::isError($ref_to)) {
            print $ref_to->getMessage();
            $this->assertTrue(false);
            return;
        } 
        if ($this->verbose > 0) {
            print "\n\nRefTo:";
            foreach ($ref_to as $rtable => $references) {
                $s = array();
                foreach ($references as $ftable) {
                    $s[] = $ftable;
                }
                print "\n$rtable <= (" . implode(', ', $s) . ')';
            }
        }

        $link = $db->getLink();
        if (PEAR::isError($link)) {
            print $link->getMessage();
            $this->assertTrue(false);
            return;
        } 
        if ($this->verbose > 0) {
            print "\n\nLink:";
            foreach ($link as $table1 => $list) {
                foreach ($list as $table2 => $links) {
                    $s = array();
                    foreach ($links as $link_table) {
                        $s[] = $link_table;
                    }
                    print "\n$table1, $table2 : (" . 
                          implode(', ', $s) . ')';
                }
            }
        }
            
    }

    function testDeleteTable1()
    {
        if ($this->verbose > -1) {
            print "\n" . ">testDeleteTable1";
        }
        if ($this->verbose > 0) {
            print "\n" . "Delete Table Person";
        }
        $db =& $this->db;

        $result = $db->deleteTable('Person');
        if (PEAR::isError($result)) {
            print $result->getMessage();
            $this->assertTrue(false);
            return;
        }

        $table = $db->getTable();
        if (PEAR::isError($table)) {
            print $table->getMessage();
            $this->assertTrue(false);
            return;
        } 
        print "\n\nTable: ";
        $s = array();
        foreach ($table as $name => $def) {
            $s[] = $name;
        }
        print implode(', ', $s);

        $col = $db->getCol();
        if (PEAR::isError($col)) {
            print $col->getMessage();
            $this->assertTrue(false);
            return;
        } 
        if ($this->verbose > 0) {
            print "\n\nCol:";
            foreach ($col as $column => $tables) {
                $s = array();
                foreach ($tables as $table) {
                    $s[] = $table;
                }
                print "\n$column : (" . implode(', ', $s) . ')';
            }
        }

        $foreign_col = $db->getForeignCol();
        if (PEAR::isError($foreign_col)) {
            print $col->getMessage();
            $this->assertTrue(false);
            return;
        } 
        if ($this->verbose > 0) {
            print "\n\nForeignCol:";
            foreach ($foreign_col as $column => $tables) {
                $s = array();
                foreach ($tables as $table) {
                    $s[] = $table;
                }
                print "\n$column : (" . 
                      implode(', ', $s) . ')';
            }
        }

        $ref = $db->getRef();
        if (PEAR::isError($ref)) {
            print $ref->getMessage();
            $this->assertTrue(false);
            return;
        } 
        if ($this->verbose > 0) {
            print "\n\nRef:";
            foreach ($ref as $ftable => $references) {
                foreach ($references as $rtable => $ref) {
                    print "\n$ftable => $rtable";
                }
            }
        }
            
        $ref_to = $db->getRefTo();
        if (PEAR::isError($ref_to)) {
            print $ref_to->getMessage();
            $this->assertTrue(false);
            return;
        } 
        if ($this->verbose > 0) {
            print "\n\nRefTo:";
            foreach ($ref_to as $rtable => $references) {
                $s = array();
                foreach ($references as $ftable) {
                    $s[] = $ftable;
                }
                print "\n$rtable <= (" . 
                      implode(', ', $s) . ')';
            }
        }

        $link = $db->getLink();
        if (PEAR::isError($link)) {
            print $link->getMessage();
            $this->assertTrue(false);
            return;
        } 
        if ($this->verbose > 0) {
            print "\n\nLink:";
            foreach ($link as $table1 => $list) {
                foreach ($list as $table2 => $links) {
                    $s = array();
                    foreach ($links as $link_table) {
                        $s[] = $link_table;
                    }
                    print "\n$table1, $table2 : (" . 
                          implode(', ', $s) . ')';
                }
            }
        }
            
    }

    function testDeleteTable2()
    {
        if ($this->verbose > -1) {
            print "\n" . ">testDeleteTable2";
        }
        if ($this->verbose > 0) {
            print "\n" . "Delete Table PersonAddress";
        }
        $db =& $this->db;

        $result = $db->deleteTable('PersonAddress');
        if (PEAR::isError($result)) {
            print $result->getMessage();
            $this->assertTrue(false);
            return;
        }
         
        $table = $db->getTable();
        if (PEAR::isError($table)) {
            print $table->getMessage();
            $this->assertTrue(false);
            return;
        } 
        print "\n\nTable: ";
        $s = array();
        foreach ($table as $name => $def) {
            $s[] = $name;
        }
        print implode(', ', $s);

        $col = $db->getCol();
        if (PEAR::isError($col)) {
            print $col->getMessage();
            $this->assertTrue(false);
            return;
        } 
        if ($this->verbose > 0) {
            print "\n\nCol:";
            foreach ($col as $column => $tables) {
                $s = array();
                foreach ($tables as $table) {
                    $s[] = $table;
                }
                print "\n$column : (" . implode(', ', $s) . ')';
            }
        }

        $foreign_col = $db->getForeignCol();
        if (PEAR::isError($foreign_col)) {
            print $col->getMessage();
            $this->assertTrue(false);
            return;
        } 
        if ($this->verbose > 0) {
            print "\n\nForeignCol:";
            foreach ($foreign_col as $column => $tables) {
                $s = array();
                foreach ($tables as $table) {
                    $s[] = $table;
                }
                print "\n$column : (" . 
                      implode(', ', $s) . ')';
            }
        }

        $ref = $db->getRef();
        if (PEAR::isError($ref)) {
            print $ref->getMessage();
            $this->assertTrue(false);
            return;
        } 
        if ($this->verbose > 0) {
            print "\n\nRef:";
            foreach ($ref as $ftable => $references) {
                foreach ($references as $rtable => $ref) {
                    print "\n$ftable => $rtable";
                }
            }
        }
            
        $ref_to = $db->getRefTo();
        if (PEAR::isError($ref_to)) {
            print $ref_to->getMessage();
            $this->assertTrue(false);
            return;
        } 
        if ($this->verbose > 0) {
            print "\n\nRefTo:";
            foreach ($ref_to as $rtable => $references) {
                $s = array();
                foreach ($references as $ftable) {
                    $s[] = $ftable;
                }
                print "\n$rtable <= (" . 
                      implode(', ', $s) . ')';
            }
        }

        $link = $db->getLink();
        if (PEAR::isError($link)) {
            print $link->getMessage();
            $this->assertTrue(false);
            return;
        } 
        if ($this->verbose > 0) {
            print "\n\nLink:";
            foreach ($link as $table1 => $list) {
                foreach ($list as $table2 => $links) {
                    $s = array();
                    foreach ($links as $link_table) {
                        $s[] = $link_table;
                    }
                    print "\n$table1, $table2 : (" . 
                          implode(', ', $s) . ')';
                }
            }
        }
    }

    function testDeleteTable3()
    {
        if ($this->verbose > -1) {
            print "\n" . ">testDeleteTable3";
        }
        if ($this->verbose > 0) {
            print "\n" . "Delete Table Address";
        }
        $db =& $this->db;

        $result = $db->deleteTable('Address');
        if (PEAR::isError($result)) {
            print $result->getMessage();
            $this->assertTrue(false);
            return;
        }
         
        $table = $db->getTable();
        if (PEAR::isError($table)) {
            print $table->getMessage();
            $this->assertTrue(false);
            return;
        } 
        print "\n\nTable: ";
        $s = array();
        foreach ($table as $name => $def) {
            $s[] = $name;
        }
        print implode(', ', $s);

        $col = $db->getCol();
        if (PEAR::isError($col)) {
            print $col->getMessage();
            $this->assertTrue(false);
            return;
        } 
        if ($this->verbose > 0) {
            print "\n\nCol:";
            foreach ($col as $column => $tables) {
                $s = array();
                foreach ($tables as $table) {
                    $s[] = $table;
                }
                print "\n$column : (" . implode(', ', $s) . ')';
            }
        }

        $foreign_col = $db->getForeignCol();
        if (PEAR::isError($foreign_col)) {
            print $col->getMessage();
            $this->assertTrue(false);
            return;
        } 
        if ($this->verbose > 0) {
            print "\n\nForeignCol:";
            foreach ($foreign_col as $column => $tables) {
                $s = array();
                foreach ($tables as $table) {
                    $s[] = $table;
                }
                print "\n$column : (" . 
                      implode(', ', $s) . ')';
            }
        }

        $ref = $db->getRef();
        if (PEAR::isError($ref)) {
            print $ref->getMessage();
            $this->assertTrue(false);
            return;
        } 
        if ($this->verbose > 0) {
            print "\n\nRef:";
            foreach ($ref as $ftable => $references) {
                foreach ($references as $rtable => $ref) {
                    print "\n$ftable => $rtable";
                }
            }
        }
            
        $ref_to = $db->getRefTo();
        if (PEAR::isError($ref_to)) {
            print $ref_to->getMessage();
            $this->assertTrue(false);
            return;
        } 
        if ($this->verbose > 0) {
            print "\n\nRefTo:";
            foreach ($ref_to as $rtable => $references) {
                $s = array();
                foreach ($references as $ftable) {
                    $s[] = $ftable;
                }
                print "\n$rtable <= (" . 
                      implode(', ', $s) . ')';
            }
        }

        $link = $db->getLink();
        if (PEAR::isError($link)) {
            print $link->getMessage();
            $this->assertTrue(false);
            return;
        } 
        if ($this->verbose > 0) {
            print "\n\nLink:";
            foreach ($link as $table1 => $list) {
                foreach ($list as $table2 => $links) {
                    $s = array();
                    foreach ($links as $link_table) {
                        $s[] = $link_table;
                    }
                    print "\n$table1, $table2 : (" . 
                          implode(', ', $s) . ')';
                }
            }
        }
    }

}

?>
