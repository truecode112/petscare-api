<?php

class Credits1 extends ActiveRecord\Model {


    static $table_name = 'creditnew';
 static $belongs_to = array(
     array('company'),array('client')
   );
 
}
