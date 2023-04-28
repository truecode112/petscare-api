<?php

class Service extends ActiveRecord\Model {

    static $table_name = 'tbl_services';
static $belongs_to = array(
     array('parent', 'foreign_key' => 'parent_id', 'class_name' => 'Service')
   );

   static $has_many = array(
     array('children', 'foreign_key' => 'parent_id', 'class_name' => 'Service'),
     array('price'), array('transactionlog')
   );
}
