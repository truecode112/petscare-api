<?php

class Notification extends ActiveRecord\Model {

    static $table_name = 'tbl_notifications';
 static $belongs_to = array(
     array('company'),array('client')
   );
 
}
