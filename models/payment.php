<?php

class Payment extends ActiveRecord\Model {

    static $table_name = 'tbl_payments';
    static $belongs_to = array(
     array('parent', 'foreign_key' => 'parent_id', 'class_name' => 'Payment')
   );

   static $has_many = array(
     array('children', 'foreign_key' => 'parent_id', 'class_name' => 'Payment'),
     array('company','client','appointment')
   );
   
//   static $has_many = array(
//     array('children', 'foreign_key' => 'parent_id', 'class_name' => 'Company'),
//     array('appointment')
//   );
    
//    public function addServices($companyId, $serviceIds) {
//        
//    }

}
