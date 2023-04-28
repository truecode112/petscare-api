<?php

class transactionlog extends ActiveRecord\Model {

    static $table_name = 'tbl_newtransaction_log';
 static $belongs_to = array(
     array('service','company','client','Credit','Pricenew','Pet')
   );
}
