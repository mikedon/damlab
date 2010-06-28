<?php
/**
 *
 *  The User controller is used to do anything deemed user related.  This involves
 *  changing passwords to gathering task data for a particular user.
 *
 *
 */
class User extends Controller {
	function User() {
    parent::Controller();
		$this->load->library('auth');
		$this->load->model('model_session_info','session_info',TRUE);
		$this->load->model('model_user','user',TRUE);
		$this->load->model('model_tasks','tasks',TRUE);
  }
  
	/**
	 *  Loads the page to change a users password.  This is only required
	 *  if the participant was given a temporary password.  The change password
	 *  form posts data to setPassword()
	 *
	 */
  function changePassword(){
		$this->auth->restrict();
		
		if($this->session->userdata('active') == '1'){
			redirect('increaseintellect/home');
		}
		$info['header'] = 'Increase Intellect | Change Password';
		$info['css'] = array('increaseintellect.css');
		$info['js'] = array('jquery.js','changePassword.js');
			
    $this->load->view('header',$info);
    $this->load->view('changePassword');
    
  }
  
	function changeUserPortal(){
		$this->auth->restrict();
		
		$this->user->user_id = $this->session->userdata('uid');
		if($this->user->has_permission('god')){
			
			$new_user = $this->input->post('userToLogInAs');
			$info['header'] = 'Increase Intellect | Change User';
			$info['css'] = array('increaseintellect.css');
			$info['js'] = array();
			
			$data['user_id'] = $new_user;
			$this->user->user_id = $new_user;
			$user_info = $this->user->get();
			
			$data['user_exists'] = TRUE;
			if(!isset($user_info->user_id)){
				$data['user_exists'] = FALSE;
			}else{
				$this->session->set_userdata('uid',$user_info->user_id);
			}
			$this->load->view('header',$info);
			$this->load->view('changeUserPortal',$data);
			
		}else{
			redirect('increaseintellect/');
		}
	}
	/**
	 *  Receives data from changePassword form.  Updates user database with
	 *  new password and sets active equal to 1.  
	 *
	 */
  function setPassword(){
    
		$this->auth->restrict();
		
    $pw = $this->input->post('passw1');
    
		$this->user->user_id = $this->session->userdata('uid');
		$this->user->password = $pw;
		$this->user->active = '1';
		$this->user->save(TRUE);
		
    redirect('/increaseintellect/home');
  }
	
	/**
	 *  Returns data for a given task id based on currently logged in user.
	 *
	 */
	function get_data($task_id){
		//Get task info
		$this->tasks->id = $task_id;
		$task = $this->tasks->get();
		$task_name = strtolower($task[0]->name);
		$this->load->model('model_'.$task_name,$task_name,TRUE);
		
		//Set up graph data object
		$graph_data = new stdClass();
		$graph_data->user_data = array();
		$graph_data->avg_data = array();
		$graph_data->max_score = 0;
		
		$total_score = 0;
		$play_count = 0;
		
		if(preg_match('/training/i',$task[0]->type)){
  		$graph_data->max_session = '';
	  	$graph_data->curr_session = '';
			
			//Get basic session info for user to get max_session num
		  $this->session_info->user_id = $this->session->userdata('uid');
		  $session = $this->session_info->get();
			
			//Regular training particicpant
			if(isset($session[0]->max_session)){
			
  			$max_session = $session[0]->max_session;
	  		$graph_data->max_session = $max_session;
			
		  	//Get current session
				$curr_session = $this->session_info->get_curr_session();
			  $graph_data->curr_session = $curr_session[0];
			
			  //Get max score uid had for each session they've completed for task
			  $this->$task_name->user_id = $this->session->userdata('uid');
				
				$high_scores = $this->$task_name->get_high_scores();
				foreach($high_scores as $session => $score){
					if($graph_data->max_score < $score){
						$graph_data->max_score = $score;
					}
					$session_data = new stdClass();
					
					$session_data->x = $session;
					$session_data->y = $score;
					$total_score += $score;
					$play_count++;
					$this->session_info->curr_session = $session;
					
					$s = $this->session_info->get();
					//Get start time and end time for session
					$session_data->start_time = '';
					$session_data->end_time = '';
					if(isset($s[0]->start_time)){
					  $session_data->start_time = $s[0]->start_time;
					}
					if(isset($s[0]->end_time)){
						$session_data->end_time = $s[0]->end_time;
					}
				
					//Add session_data to graph data
					$graph_data->user_data[] = $session_data;
				}
				$graph_data->max_score += 100;
				//Grab avg data for user
				if($play_count != 0){
					$avg_data = new stdClass();
					$avg_data->x = 0;
					$avg_data->y = $total_score/$play_count;//$this->$task_name->get_avg_score();
					$graph_data->avg_data[] = $avg_data;
					
					$avg_data = new stdClass();
					$avg_data->x = $max_session;
					$avg_data->y = $total_score/$play_count;//$this->$task_name->get_avg_score();
					$graph_data->avg_data[] = $avg_data;
				}
			}//Might be a researhcer so check for that, otherwise no data for user
			else{
				
				
			}
		}elseif(preg_match('/assessment/i',$task[0]->type)){
			$this->$task_name->user_id = $this->session->userdata('uid');
			
			$high_scores = $this->$task_name->get_high_scores();
			
			$counter = 1;
			foreach($high_scores as $id => $score){
				if($graph_data->max_score < $score){
					$graph_data->max_score = $score;
				}
				
				
				$usr_data = new stdClass();
				$usr_data->y = $score;
				$total_score += $score;
				$play_count++;
				$usr_data->x = $counter++;
				
				$graph_data->user_data[] = $usr_data;
			}
			$graph_data->max_score += 100;
			$avg_data = new stdClass();
			$avg_data->x = 0;
			if($play_count == 0){
				$avg_data->y = 0;
			}else{
				$avg_data->y = ceil($total_score/$play_count);//$this->$task_name->get_avg_score();
			}
			
			$graph_data->avg_data[] = $avg_data;
			$avg_data = new stdClass();
			$avg_data->x = $counter;
			if($play_count == 0){
				$avg_data->y = 0;
			}else{
				$avg_data->y = ceil($total_score/$play_count);//$this->$task_name->get_avg_score();
			}
			
			$graph_data->avg_data[] = $avg_data;
		}
		print json_encode($graph_data);
	}
}