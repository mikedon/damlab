<?php
/**
 *  Main controller.
 *
 *
 */
class IncreaseIntellect extends Controller {
	
	function IncreaseIntellect(){
		parent::Controller();
		/**
		 *  user table, see User model for more information
		 */
		$this->load->model('model_user','user',TRUE);
		
		/**
		 *  session_info table, see session_info model for more information
		 */
		$this->load->model('model_session_info','session_info',TRUE);
		
		/**
		 *  consent table, see consent model for more information
		 */
		$this->load->model('model_consent','consent',TRUE);
		
		/**
		 *  experiments table, see experiments model for more information
		 */
		$this->load->model('model_experiments','experiments',TRUE);
		
		/**
		 *  tasks table, see table model for more information
		 */
		$this->load->model('model_tasks','tasks',TRUE);
		
		/**
		 *  Provides authentication and forces users to log in before visiting site
		 */
		$this->load->library('auth');
	}
	
	function index(){
		echo "here";
		$this->home();
	}
	
	/**
	 *  Pages:
	 *  
	 *    /
	 *    /assessment
	 *    /training
	 *    /research
	 *    /task/task_name
	 *
	 *    Each page, except for the login page requires the user to have logged in.
	 *    To assure this, call $this->auth->restrict() at the top of each function.
	 *    Some pages require the user to have permission to view it.  To check for
	 *    this, you must do the following:
	 *
	 *     $this->user->user_id = $this->session->userdata('uid');
	 *     $this->auth->restrict(!$this->user->has_permission('type'));
	 *
	 *     *where 'type' is the page name but can also be many other values, see
	 *      User model for more information
	 *
	 *    In some cases the user will be required to accept a consent form before
	 *    playing any tasks.  To do this add the following to the top of each page:
	 *
	 *    To check if the user needs to change their password before moving forward
	 *    then add the following to the top of the page:
	 *   
	 *     $active = $this->user->get()->active;
	 *     if($active == '0')
   *       redirect('/user/changePassword'); 
	 *
	 */
	function home(){
		$this->auth->restrict();
		
		$consent = $this->requiresConsent();
		$this->user->user_id = $this->session->userdata('uid');
		$active = $this->user->get()->active;
		
		if($active == '0'){
				//Go to password change page
				redirect('/user/changePassword');
		}elseif($this->session->userdata('consent') != "accept" && $consent != ""){
				//Go to consent page

				$info['header'] = 'Consent Form';
				$info['css'] = array('increaseintellect.css','boxy.css');
				$info['js'] = array('jquery.js','jquery.boxy.js');
				$data['consent_form'] = $consent;
					
				$this->load->view('header',$info);
				$this->load->view('consent_form',$data);
		}else{
				//Go to home page
				$this->auth->restrict();
    		$info['header'] = 'Increase Intellect';
				$info['css'] = array('increaseintellect.css');
				$info['js'] = array();
				
				$this->session_info->user_id = $this->session->userdata('uid');
				$session = $this->session_info->get();
		
				if(isset($session[0]->max_session)){
		  		$info['max_session'] = $session[0]->max_session;
			  	$num_sessions_complete = $this->session_info->get_num_sessions_complete();
				  $info['curr_session'] = $num_sessions_complete;
				}

				$this->load->view('header',$info);
				$this->load->view('navbar');
				$this->load->view('home');
		}
	}
	
	function login(){
		//Allow anyone to view
		$this->auth->restrict(TRUE);
		
		//If submLogin received from login page then check to see if valid user
		//Else redirect back to login page with error message
		if ($this->input->post('submLogin') != FALSE){
			$login = array($this->input->post('UserID'),$this->input->post('Password'));
			if($this->auth->process_login($login)){
				$this->auth->redirect();
			}else{
				$data['error'] = 'Login failed, please try again';
				$this->load->vars($data);
			}
		}
		//Default to showing login page
		$info['header'] = 'Increase Intellect | Login';
		$info['css'] = array('increaseintellect.css');
		//$info['js'] = array('changePassword.js');
		$info['js'] = array();
		
		$this->load->view('header',$info);
		$this->load->view('login');
	}
	
	function assessment(){
		$this->user->user_id = $this->session->userdata('uid');
		$this->auth->restrict(!$this->user->has_permission('assessment'));
		$active = $this->user->get()->active;
		if($active == '0')
      redirect('/user/changePassword');
    
		//Get tasks information for all assessments
		$data['tasks'] = array();
		$this->tasks->clear();
    $tasks = $this->tasks->get();
		
		//For every task see if it is in fact an assessment and make sure user
		//has permission to play the task
		foreach($tasks as $t){
      $tmp = explode('_',$t->name);
			$task_name = $tmp[0];
      if(preg_match('/assessment/i',$t->type) && $this->user->has_permission($t->id)){
				$task['name'] = $t->name;
				$task['task_name'] = $task_name;
				$task['pic_url'] = $t->pic_url;
				$task['playable'] = $this->is_playable($t->name);
				$task['id'] = $t->id;
				$task['type'] = $t->type;
				array_push($data['tasks'],$task);
      }
		}
		
		//Get session info if available
		$this->session_info->user_id = $this->session->userdata('uid');
		$session = $this->session_info->get();
		
		if(isset($session[0]->max_session)){
				$info['max_session'] = $session[0]->max_session;
				$num_sessions_complete = $this->session_info->get_num_sessions_complete();
				$info['curr_session'] = $num_sessions_complete;
		}
		$info['header'] = 'Increase Intellect | Assessment';
		$info['css'] = array('increaseintellect.css');
		$info['js'] = array('jquery.js','excanvas.compiled.js','highcharts.js');
		
		
		$this->load->view('header',$info);
		$this->load->view('navbar');
		$this->load->view('content',$data);
	}
	
	function training(){
    $this->user->user_id = $this->session->userdata('uid');
		$this->auth->restrict(!$this->user->has_permission('training'));
		$active = $this->user->get()->active;
		if($active == '0')
      redirect('/user/changePassword');
		
		$data['tasks'] = array();
		$this->tasks->clear();
    $tasks = $this->tasks->get();
		foreach($tasks as $t){
      $tmp = explode('_',$t->name);
			$task_name = $tmp[0];
      if(preg_match('/training/i',$t->type) && $this->user->has_permission($t->id)){
				$task['name'] = $t->name;
				$task['task_name'] = $task_name;
				$task['pic_url'] = $t->pic_url;
				$task['playable'] = $this->is_playable($t->name);
				$task['id'] = $t->id;
				$task['type'] = $t->type;
				array_push($data['tasks'],$task);
      }
		}
		$info['header'] = 'Increase Intellect | Spatial Training';
		$info['css'] = array('increaseintellect.css');
		$info['js'] = array('jquery.js','excanvas.compiled.js','highcharts.js');
		
    $this->session_info->user_id = $this->session->userdata('uid');
		$session = $this->session_info->get();
		
		if(isset($session[0]->max_session)){
				$info['max_session'] = $session[0]->max_session;
				$num_sessions_complete = $this->session_info->get_num_sessions_complete();
				$info['curr_session'] = $num_sessions_complete;
		}
		$this->load->view('header',$info);
		$this->load->view('navbar');
		$this->load->view('content',$data);		
	}
	
	function research(){
    $this->user->user_id = $this->session->userdata('uid');
		$this->auth->restrict(!$this->user->has_permission('research'));
		
		$info['header'] = 'Increase Intellect | Research';
		$info['css'] = array('increaseintellect.css','research.css','jquery-ui-1.7.2.custom.css',
												 'ui.slider.extras.css','boxy.css');
		$info['js'] = array('jquery.js','research.js','jquery-ui-1.7.2.custom.min.js',
												'selectToUISlider.jQuery.js','ajaxupload.js','jquery.boxy.js');
		
		$this->load->view('header',$info);
		$this->load->view('navbar');

    //Get Task info based on researcher permissions
		$this->tasks->clear();
		$tasks = array();
		if($this->user->has_permission('training') && $this->user->has_permission('assessment')){
			$tasks = $this->tasks->get();
		}elseif($this->user->has_permission('training')){
			$this->tasks->type = 'training';
			$tasks_info = $this->tasks->get_tasks();
			$this->tasks->clear();
			foreach($tasks_info as $id => $name){
				$this->tasks->id = $id;
				$t = $this->tasks->get();
				$tasks[] = $t[0];
			}
		}elseif($this->user->has_permission('assessment')){
			$this->tasks->type = 'assessment';
			$tasks_info = $this->tasks->get_tasks();
			$this->tasks->clear();
			foreach($tasks_info as $id => $name){
				$this->tasks->id = $id;
				$t = $this->tasks->get();
				$tasks[] = $t[0];
			}
		}
		$data['tasks'] = array();
		
    foreach($tasks as $t){
		  $data['tasks'][$t->name] = $t;		
    }
		//Get Experiment Info
		$this->experiments->experimenter_code = $this->session->userdata('uid');
		$experiments = $this->experiments->get();
		$data['exp_codes'] = array();
		
		foreach($experiments as $exp){
		  //$this->permissions->user_id = $this->session->userdata('uid') . $exp->experiment_code . '01';
		  //$permissions = $this->permissions->get();
			$prefix = $this->session->userdata('uid') . $exp->experiment_code;
			
			$this->user->user_id = $prefix . '01';
			$permissions = $this->user->get_permissions();
			
			$data['exp_codes'][$exp->experiment_code]['num_participants'] = $exp->num_participants;
			$data['exp_codes'][$exp->experiment_code]['consent'] = $exp->consent_form;
			$data['exp_codes'][$exp->experiment_code]['permissions'] = $permissions;
			$data['exp_codes'][$exp->experiment_code]['num_assessment_complete'] = "N/A";
			$data['exp_codes'][$exp->experiment_code]['active'] = $exp->active;
			
			foreach($tasks as $t){
				if(in_array($t->id,$permissions) && preg_match('/assessment/i',$t->type)){
					//Get number of participants who have completed assessment
					$this->tasks->id = $t->id;
					$this->tasks->user_id = $prefix;
					$data['exp_codes'][$exp->experiment_code]['num_assessment_complete'] += $this->tasks->get_num_complete($prefix);
				}
			}
			//Get Num training complete
			$this->session_info->user_id = $this->session->userdata('uid') . $exp->experiment_code;
			$num_training_complete = $this->session_info->get_num_complete();
			if(is_numeric($num_training_complete)){
				$data['exp_codes'][$exp->experiment_code]['num_training_complete'] = $num_training_complete;
			}
		}
		
		//Get consent forms
    $consent_dir = opendir('./assets/consent_forms/');
		while (false !== ($file = readdir($consent_dir))) {
		  if ($file != "." && $file != "..") {
			  $data['consent_forms'][] = $file;
       }
		}
		closedir($consent_dir);

		$this->load->view('research',$data);
	}
	
	function task($task){
    $this->user->user_id = $this->session->userdata('uid');
		$this->auth->restrict(!$this->user->has_permission($task));

    $task_name = strtolower($task);
		
		$this->tasks->name = $task;
		$t = $this->tasks->get();
		$data['name'] = $t[0]->name;
		$data['task_url'] = $t[0]->task_url;
		$data['instructions'] = $t[0]->instructions;
		
    $this->session_info->user_id = $this->session->userdata('uid');
		$session_info = $this->session_info->get_curr_session();
		$data['curr_session'] = $session_info[0];
		
    $info['header'] = 'Increase Intellect | ' . $task;
    $info['css'] = array('increaseintellect.css','boxy.css');
    $info['js'] = array('jquery.js','swfobject.js','jquery.boxy.js','gamescreen.js');
	
		$this->user->user_id = $this->session->userdata('uid');
		$user_info = $this->user->get();
		
		if($this->is_playable($t[0]->name)){
      $tables = $this->db->list_tables();
			$task_params_table = $task_name . "_parameters";
			if(in_array($task_params_table,$tables)){
				$this->load->model("model_" . $task_params_table, $task_params_table, TRUE);
				$this->$task_params_table->experiment_code = $user_info->experiment_code;
				$task_params = $this->$task_params_table->get();
				if(isset($task_params[0])){
				  foreach($task_params[0] as $tp => $val){
				    if($tp == 'experiment_code')
				      continue;
				
				    $data[$tp] = $val;
				  }
			  }
			}
		}else{
				$this->home();
		}
		
		$this->session_info->user_id = $this->session->userdata('uid');
		$session = $this->session_info->get();
		
		if(isset($session[0]->max_session)){
		  $info['max_session'] = $session[0]->max_session;
			$curr_session = $this->session_info->get_num_sessions_complete();
			$info['curr_session'] = $curr_session;
		}
    $this->load->view('header',$info);
    $this->load->view('navbar');
		$this->load->view('gamescreen',$data);
	}
	//
  //END PAGES//
	//
	
	
	//
	//CONSENT//
	//
	function consent($accept){
		$this->auth->restrict();
		
		$this->user->user_id = $this->session->userdata('uid');
		$user_info = $this->user->get();
		
		$this->experiments->experiment_code = $user_info->experiment_code;
		$experiment_info = $this->experiments->get();
		
		$this->consent->user_id = $this->session->userdata('uid');
		$this->consent->date = date("Y-m-d H:i:s");
		$this->consent->experiment_code = $experiment_info[0]->experiment_code;
		$this->consent->experimenter_code = $experiment_info[0]->experimenter_code;
		$this->consent->consent_form = $experiment_info[0]->consent_form;
		$this->consent->accept = $accept;
		
		$this->consent->save();
		
		if($accept == "1"){
      $this->session->set_userdata('consent','accept');
      $this->home();
		}else{
      $this->logout();
		}
		
	}
	/**
	 *	Determines if the current user has to accept
	 *	consent before playing a task.
	 *
	 */
	function requiresConsent(){
    
		$this->user->user_id = $this->session->userdata('uid');
		$user_info = $this->user->get();
		
		if(isset($user_info->experiment_code)){
				
      $this->experiments->experiment_code = $user_info->experiment_code;
      $experiment_info = $this->experiments->get();
				
      if(isset($experiment_info[0]->consent_form)){
				return ($experiment_info[0]->consent_form == 'noconsent') ? "" : $experiment_info[0]->consent_form;
      }else{
				return "";
      }
		}else{
      return "";
		}
	}
	
	//
	//END CONSENT//
	//
	
  //
  //UTILITY FUNCTIONS//
	//
	
	/**
	 *	Determines if the training task is playable according the users
	 *	session status.
	 */
	function is_playable($task){
				
     //If researcher let them play
		$this->user->user_id = $this->session->userdata('uid');
		if($this->user->has_permission('research')){
      return TRUE;
		}
		
		//Get user data
		$user_info = $this->user->get();
		
		//Get experiment info
		$this->experiments->experiment_code = $user_info->experiment_code;
		$experiment = $this->experiments->get();
		
		//Get task type
		$task_name = strtolower($task);
		$this->tasks->name = $task;
		$task_type = $this->tasks->get_type();
		//Task is an assessment
		if(preg_match('/assessment/',$task_type[0])){
				
			if($experiment[0]->ordering == '1'){
				//Load Model for assessment
				$model_name = "model_" . $task_name;
				$this->load->model($model_name,$task_name,TRUE);
				
				//Get the number of times user has completed the assessment
				$this->$task_name->user_id = $user_info->user_id;
				$num_complete = $this->$task_name->get_num_complete();
				//Base Case
				if($num_complete == 0){
					return TRUE;
				}
				
				//If there are sessions for user then the num complete will only
				//equal curr session when the user still has training to complete
				//If no sessions for user, then the base case takes care of the first time
				//and then the curr session will always == 0 therefore this will always
				//return true
				//have to account for playing assessment one last time
				$this->session_info->user_id = $user_info->user_id;
				$session = $this->session_info->get();
				
				if($num_complete == $user_info->curr_session){
					if($num_complete == $session[0]->max_session){
						return TRUE;
					}
					return FALSE;
				}elseif($num_complete > $session[0]->max_session){
					return FALSE;
				}else{
					return TRUE;
				}
			}else{
				return TRUE;
			}
		}elseif(preg_match('/training/',$task_type[0])){
				
			//if order matters
			if($experiment[0]->ordering == '1'){
				$permissions = $this->user->get_permissions();
				$this->tasks->clear();
				foreach($permissions as $id){
					$this->tasks->id = $id;
					$type = $this->tasks->get_type(array($id));
					foreach($type as $task_id=>$tt){
						if(preg_match('/assessment/',$tt)){
							if($user_info->curr_session != $this->tasks->get_num_complete($user_info->user_id)){
								return FALSE;
							}
						}
					}
				}
			}
			//Check 12 hour time frame
			$this->session_info->user_id = $this->session->userdata('uid');
			$this->session_info->curr_session = $user_info->curr_session - 12;
			if($this->session_info->curr_session == 0){
				return TRUE;
			}
			$session = $this->session_info->get();
			if(isset($session[0]->end_time)){
				if((time() - strtotime($session[0]->end_time))/3600 < 12){
				echo (time() - strtotime($session[0]->end_time))/3600;
					return FALSE;
				}
			}
				
			$this->session_info->curr_session = $user_info->curr_session;
			$session = $this->session_info->get();
			
			//Get status for current session
			$task_status = strtolower($task . '_status');
			if(isset($session[0]->$task_status)){
				//If session is complete they cannot play
				if($this->session_complete()){
					return FALSE;
				}
			
				//If they haven't complete the training task then allow
				return $session[0]->$task_status == '0000-00-00 00:00:00';
			}else{
				return TRUE;
			}
		}
	}
	
	function logout(){
		if($this->auth->logout())
			redirect('/increaseintellect/login');
	}
	
	/**
	 *	A session is complete when all of the training tasks the user is
	 *	supposed to complete for a session are completed.
	 *
	 *	First check to see if user has permission for particular training task
	 *	then see if user has completed the training by looking in session_info
	 */
	function session_complete(){
    
    $this->user->user_id = $this->session->userdata('uid');
    $permissions = $this->user->get_permissions();
		$user_info = $this->user->get();
		
		$tasks = array();
		$this->tasks->name = '';
		foreach($permissions as $p){
				$this->tasks->id = $p;
			  $t = $this->tasks->get();
				if(preg_match('/training/i',$t[0]->type))
				  $tasks[] = $t[0]->name;
		}
    
    $this->session_info->user_id = $this->session->userdata('uid');
    $this->session_info->curr_session = $user_info->curr_session;
    $session = $this->session_info->get();
    
    foreach($tasks as $t){
			$task_status = strtolower($t.'_status');
      if($session[0]->$task_status == '0000-00-00 00:00:00'){
        return FALSE;
      }
    }
    return TRUE;
  }
	//
	// END UTILITY
	//
}
?>
