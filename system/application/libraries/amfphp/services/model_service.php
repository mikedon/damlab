<?php
//Load CI DB Instance since we are not coming through index.php
require_once('amfci_db.php');

// Use this path if you are using a module (ie, MatchBox)
// require_once(AMFSERVICES.'/../../../modules/test_shop/models/product_model.php');

// Use this path if you are using standard install
require_once(AMFSERVICES.'/../../../models/Model_session_info.php');
class Service_session extends Model_session_info{
  //$this->load->model('model_memnosyne','memnosyne',TRUE);
  
  function update($session_data){
    
  }
  
  function start($session_data){
    $this->session_info->user_id = $session_data->user_id;
    $this->session_info->curr_session = $session_data->session;
    $session = $this->session_info->get();
    if(isset($session->max_session)){
        
      $this->session_info->$data->type = $session_data->time;
      $this->session_info->save(TRUE);
        
      if($this->session_complete($data)){
        $this->session_info->end_time = $session_data->time;
        $this->session_info->save(TRUE);
            
        if($data->session != $session->max_session - .01){
          $this->session_info->end_time = '0000-00-00 00:00:00';
          $this->session_info->start_time = '0000-00-00 00:00:00';
          $this->session_info->max_session = $session->max_session;
          $this->session_info->curr_session = $session->curr_session + .01;
          $this->session_info->save();
        }
      }
    }  
  }
  
  
  
}