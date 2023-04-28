<?php

class Company extends ActiveRecord\Model {

    static $table_name = 'tbl_companies';
    static $belongs_to = array(
     array('parent', 'foreign_key' => 'parent_id', 'class_name' => 'Company')
   );

   static $has_many = array(
     array('children', 'foreign_key' => 'parent_id', 'class_name' => 'Company'),
     array('Contract','appointment','staff')
   );
   
//   static $has_many = array(
//     array('children', 'foreign_key' => 'parent_id', 'class_name' => 'Company'),
//     array('appointment')
//   );
    
//    public function addServices($companyId, $serviceIds) {
//        
//    }

}
