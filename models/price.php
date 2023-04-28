<?php

class Price extends ActiveRecord\Model {

    static $table_name = 'tbl_prices';
 static $belongs_to = array(
     array('service','company','client')
   );
}


