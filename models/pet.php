<?php

class Pet extends ActiveRecord\Model {

    static $table_name = 'tbl_pets';
  static $belongs_to = array(
     array('parent', 'foreign_key' => 'parent_id', 'class_name' => 'Pet')
   );

   static $has_many = array(
     array('children', 'foreign_key' => 'parent_id', 'class_name' => 'Pet'),
     array('appointment'), array('transactionlog'),
   );
 
}
