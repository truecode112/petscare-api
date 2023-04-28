<?php

class Appointment extends ActiveRecord\Model {

    static $table_name = 'tbl_appointments';
 static $belongs_to = array(
     array('company'),array('companyService'),array('client'),array('pet'),array('staff'),
   );
 
}
