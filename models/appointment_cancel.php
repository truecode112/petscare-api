<?php

class Appointment_cancel extends ActiveRecord\Model {

    static $table_name = 'tbl_appoint_cancel';
 static $belongs_to = array(
     array('company'),array('companyService'),array('client'),array('pet'),array('staff')
   );
 
}