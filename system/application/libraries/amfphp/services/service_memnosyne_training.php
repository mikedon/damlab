<?php
//Load CI DB Instance since we are not coming through index.php
require_once('amfci_db.php');

// Use this path if you are using a module (ie, MatchBox)
// require_once(AMFSERVICES.'/../../../modules/test_shop/models/product_model.php');

// Use this path if you are using standard install
require_once(AMFSERVICES.'/../../../models/model_memnosyne_training.php');
class Service_memnosyne_training extends Model_memnosyne_training{
  
  function insert($task_data){
    foreach($task_data as $var => $val){
      $this->$var = trim($val);   
    }
    $this->save();
        
    return $task_data;
  }
  
  function get_current_difficulty($user_id){
    $this->user_id = trim($user_id);
    $difficulty = $this->current_difficulty();
        
    if($difficulty != ''){
      return array($difficulty);
    }else{
      return array("error");
    }
  }
}