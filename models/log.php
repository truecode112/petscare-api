<?php

class Log extends ActiveRecord\Model {

    static $table_name = 'tbl_transaction_log';
 static $belongs_to = array(
     array('service','company','client','Credit','price')
   );
}
