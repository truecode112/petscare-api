<?php

class Credits extends ActiveRecord\Model {


    static $table_name = 'tbl_newcredits';
 static $belongs_to = array(
     array('company'),array('client'),array('pet')
   );
 
}
