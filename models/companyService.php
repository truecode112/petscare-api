<?php

class CompanyService extends ActiveRecord\Model {

    static $table_name = 'tbl_company_services';
 static $belongs_to = array(
     array('parent', 'foreign_key' => 'parent_id', 'class_name' => 'CompanyService')
   );

   static $has_many = array(
     array('children', 'foreign_key' => 'parent_id', 'class_name' => 'CompanyService'),
     array('appointment')
   );
}
