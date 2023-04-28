<?php

class Contract extends ActiveRecord\Model {

    static $table_name = 'tbl_contracts';
 static $belongs_to = array(
     array('company'),array('Client')
   );
 
}
