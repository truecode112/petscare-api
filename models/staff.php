<?php

class Staff extends ActiveRecord\Model {

    static $table_name = 'tbl_staffs';
 static $belongs_to = array(
      array('parent', 'foreign_key' => 'parent_id', 'class_name' => 'Staff'),
     array('company')
   );
  static $has_many = array(
     array('children', 'foreign_key' => 'parent_id', 'class_name' => 'Staff'),
     array('appointment')
   );
}

