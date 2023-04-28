<?php

class Lead extends ActiveRecord\Model {

    static $table_name = 'tbl_leads';
    
  static $belongs_to = array(
     array('parent', 'foreign_key' => 'parent_id', 'class_name' => 'Client')
   );

   static $has_many = array(
     array('children', 'foreign_key' => 'parent_id', 'class_name' => 'Client'),
     array('Contract','appointment','Credit','price','Pet')
   );
}
