<?php
class Utility extends Controller{
  
  function __construct(){
    parent::Controller();
    
    $this->load->model('model_session_info','session_info',TRUE);
	$this->load->model('model_user','user',TRUE);
	$this->load->model('model_tasks','tasks',TRUE);
    $this->load->model('model_experiments','experiments',TRUE);
  }
  
  /**
	 *  Returns information on all participants in experiment
	 *
	 * @param $exp_code - experiment code for participants
	 * @return $html - HTML string to display
	 * 
	 */ 
  function get_experiment_info($exp_code){
    
    $prefix = $this->session->userdata('uid') . trim($exp_code);
    $html = '';
    //Get permissions for experiment
		$this->user->user_id = $prefix . '01';
    $permissions = $this->user->get_permissions();
		if(empty($permissions)){
			for($i=2;$i<100;$i++){
				$this->user->user_id = $prefix . sprintf("%02d",$i);
				$permissions = $this->user->get_permissions();
				if(!empty($permissions)){
					break;
				}
			}
		}
    
    //Check to see if experiment/users exists
    if(!empty($permissions)){
      
      $html = "<div>Information for experiment: $exp_code<hr>";
      
      //Get number of users for experiment
      $this->experiments->experiment_code = $exp_code;
      $experiment = $this->experiments->get();
      $num_participants = $experiment[0]->num_participants;
      
      //Check to see if we need to look at any session data
      if(preg_match('/training/i',implode($this->tasks->get_type($permissions)))){
		    $html .= "Session Information<div id='session_infomation'>
					<table class='exp_information' width='600px'>
					<th>User ID</th>
					<th>Sessions Completed</th>
					<th>Maximum Sessions</th>
					<th>Most Recent Completion Time</th>
					<th>Experiment Start Time</th>";
        
        //Grab current session and start time for that session for each user
        for($i = 1; $i <= $num_participants; $i++){
          $user_id = $prefix . sprintf("%02d",$i);
          
          $this->session_info->user_id = $user_id;
					
					//Get Number of completed sessions
          $num_complete = $this->session_info->get_num_sessions_complete();
					$this->session_info->curr_session = $num_complete;
					
					//Get Max number of sessions
					$session_info = $this->session_info->get();
          $max_session = $session_info[0]->max_session;
					
					//Get end time of last completed session
					$last_completed = $session_info[0]->end_time;
					
					//Get start time of first session
					$this->session_info->curr_session = 1;
					$session_info = $this->session_info->get();
					$first_session = $session_info[0]->start_time;
					
          $html .= "<tr>
                      <td>$user_id</td>
                      <td>$num_complete</td>
											<td>$max_session</td>
                      <td>$last_completed</td>
											<td>$first_session</td>
                    </tr>";
                    
        }
        $html .= "</table></div>";
      }//Check for assessment data
      if(preg_match('/assessment/i',implode($this->tasks->get_type($permissions)))){
				$this->tasks->type = 'assessment';
				$tasks = $this->tasks->get_tasks();  // Get all assessment tasks
				
        $html .= "Assessment Information<div id='assessment_information'>
          <table class='exp_information' width='600px'>
          <th>User ID</th>";
					
				foreach($tasks as $id=>$name){
				  $html .= "<th># Completed  $name</th>";	
				}
				
				$html .= "<th>Last Date Played</th>";
				
				for($i = 1;$i <= $num_participants; $i++){
          $user_id = $prefix . sprintf("%02d",$i);
          $time_played = "0000-00-00 00:00:00";
					
					$html .= "<tr><td>$user_id</td>";
					
					//Get # assessments complete and last time played for each user
				  foreach($tasks as $id=>$name){
				    if(in_array($id,$permissions)){
						  $task_name = strtolower($name);
						  $this->load->model('model_'.$task_name,$task_name,TRUE);
						  $this->$task_name->user_id = $user_id;
						  $num_complete = $this->$task_name->get_num_complete();
							
							if(isset($num_complete)){
								$html .= "<td>$num_complete</td>";
							
								if(strtotime($time_played) < strtotime($this->$task_name->get_last_time_played())){
								  $time_played = $this->$task_name->get_last_time_played();
								}
							}
					  }else{
							$html .= "<td>N/A</td>";
					  }
				  }
          $html .= "<td>$time_played</td></tr>";
				}
        $html .= "</table></div>";
      }
			$html .="</div>";
    }
    print $html;	
  }
	
	function test_file(){
    $sentences = read_file('./assets/multimedia/tasks/Sentencical/user_lists/'.'2222session01'.'.list');
		print_r($sentences);
	}
}