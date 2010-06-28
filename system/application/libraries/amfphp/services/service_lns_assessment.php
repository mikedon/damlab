<?php
//Load CI DB Instance since we are not coming through index.php
require_once('amfci_db.php');

// Use this path if you are using standard install
require_once(AMFSERVICES.'/../../../models/model_lns_assessment.php');
class Service_lns_assessment extends Model_lns_assessment{

  function insert($task_data){
    foreach($task_data as $var => $val){
      $this->$var = trim($val);   
    }
    $this->save();
        
    return array($this->db->last_query());
  }

}