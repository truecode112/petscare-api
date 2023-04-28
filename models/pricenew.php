<?php

class Pricenew extends ActiveRecord\Model {

    static $table_name = 'tbl_newprices';
 static $belongs_to = array(
     array('service','company','client','log','pet')
   );
}