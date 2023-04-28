<?php

class Contact_backup extends ActiveRecord\Model {

    static $table_name = 'tbl_contact_backup';
    
  static $belongs_to = array(
     array('parent', 'foreign_key' => 'parent_id', 'class_name' => 'Contact_backup')
   );

   static $has_many = array(
     array('children', 'foreign_key' => 'parent_id', 'class_name' => 'Contact_backup'),
     array('Client','appointment','Credit','price')
   );
}