<?php
//Load CI DB Instance since we are not coming through index.php
require_once('amfci_db.php');

// Use this path if you are using standard install
require_once(AMFSERVICES.'/../../../models/model_session_info.php');
class Service_session_info extends Model_session_info{
  //$this->load->model('model_memnosyne','memnosyne',TRUE);
  
    
  function session_complete($user_id){
    $CI =& get_instance();
    
    //$CI->load->model('model_permissions','permissions',TRUE);
    $CI->load->model('model_user','user',TRUE);
    
    $CI->user->user_id = $user_id;//$this->user_id;$this->session->userdata('uid');
    $permissions = $CI->user->get_permissions();
		$user_info = $CI->user->get();
		
    $CI->load->model('model_tasks','tasks',TRUE);
		$tasks = array();
		foreach($permissions as $p){
				$CI->tasks->id = $p;
			  $t = $CI->tasks->get();
				$tasks[] = strtolower($t[0]->name);
		}
    
    //$this->user_id = $this->session->userdata('uid');
    //$this->curr_session = $user_info->curr_session;
    $session = $this->get();
    
    foreach($tasks as $t){
      $task_status = $t . '_status';
      if($session[0]->$task_status == '0000-00-00 00:00:00'){
        return FALSE;
      }
    }
    return TRUE;
  }
  
  /**
   *  Updates table session_info to reflect progress of participant after
   *  completing a task.
   *
   *  @var session_data - array
   */
  function update($session_data){
		$curr_time = date("Y-m-d H:i:s");
		
    $this->user_id = trim($session_data['user_id']);
    $this->curr_session = trim($session_data['session']);
    $session = $this->get();  //Current entry in session_info
    
    //Check to make sure user is in session_info
    if(isset($session[0]->max_session)){
      //Update training completion time
      $type = trim($session_data['type']);
      $this->$type = $curr_time;
      $this->save(TRUE);
      $this->$type = '';
      //If session complete then add new entry in table for next session unless
      //we are on the last session
      if($this->session_complete(trim($session_data['user_id']))){
        $this->end_time = $curr_time;
        $this->save(TRUE);
        if($session[0]->curr_session != $session[0]->max_session){
          $this->end_time = '0000-00-00 00:00:00';
          $this->start_time = '0000-00-00 00:00:00';
          $this->memnosyne_training_status = '0000-00-00 00:00:00';
          $this->sentencical_training_status = '0000-00-00 00:00:00';
          $this->numberpiles_training_status = '0000-00-00 00:00:00';
	  $this->shapebuilder_training_status = '0000-00-00 00:00:00';
          $this->max_session = $session[0]->max_session;
          $this->curr_session = $session[0]->curr_session + 1;
          $this->save();
          
          //update curr session in user table as well
          $CI =& get_instance();
          $CI->load->model('model_user','user',TRUE);
          $CI->user->user_id = $this->user_id;
          $CI->user->curr_session = $session[0]->curr_session + 1;
          $CI->user->save(TRUE);
        }
      }
    }
    return array($session[0]->max_session);
  }

  
  /**
   *  Starts the current session of the user if not already started.
   *
   *  @var sesion_data - array
   */
  function start($session_data){
    $this->user_id = trim($session_data['user_id']);
    $this->curr_session = trim($session_data['session']);
    $session = $this->get();
    if(isset($session[0]->start_time)){
      if($session[0]->start_time == '0000-00-00 00:00:00'){  //All 0's=not started
        $this->start_time = trim($session_data['time']);
        $this->save(TRUE);
      }
    }
    return array($this->db->last_query());
  }
  
}