<?php
class Research extends Controller {
    
	function Research() {
		parent::Controller();
    $this->load->model('model_user','user',TRUE);
    $this->load->model('model_experiments','experiments',TRUE);
    $this->load->model('model_session_info','session_info',TRUE);
    $this->load->model('model_tasks','tasks',TRUE);
	}
        
  function updateExperiment(){
    $json = array();
            
    $data = $this->input->post('data');
    $data = json_decode($data);
            
    $old_num_users = 0;
    $this->experiments->experiment_code = $data->experiment_code;
    $experiment = $this->experiments->get();
    $old_num_users = $experiment[0]->num_participants;
    
    $this->user->user_id = $this->session->userdata('uid') . $data->experiment_code . '01';
    $data->permissions = $this->user->get_permissions();
    
    if(preg_match('/training/i',implode($this->tasks->get_type($data->permissions)))){
        
        $this->session_info->user_id = $this->session->userdata('uid') . $data->experiment_code . '01';
        $session = $this->session_info->get();
        $data->num_of_sessions = $session[0]->max_session;
        
    }
            
    //Update user and permissions table
    $userPass = "List for Experiment: " . $data->experiment_code . "\n\nUSER ID | PASSW\n\n";
    for( $i = $old_num_users + 1 ; $i < ($old_num_users + $data->new_users ) + 1 ; $i ++){
      $user_id = $this->session->userdata('uid') . $data->experiment_code . sprintf("%02d",$i);
                
      if(preg_match('/random/i',$data->password_type)) {
        $data->password = $this->generatePassword();
        $userPass .= $user_id . " " . $password . "\n";
      }
                
      $data->user_id = $user_id;
      $this->createNewUser($data);
    }
    
    if(preg_match('/random/i',$data->password_type)){
      mail($data->email,'User List',$userPass);
    }
    //Update experiments table            
    $this->experiments->num_participants = sprintf("%02d",$old_num_users + $data->new_users);
    $this->experiments->save(TRUE);
            
    $json['msg'] = "User range extended.";
    //$json['num_participants'] = sprintf("%02d",$old_num_users+$data->new_users);
    $json['num_participants'] = $old_num_users+$data->new_users;      
    print json_encode($json);
  }
        
  /**
   *    Creates one experiment with multiple participants.
   *
   */
	function createExperiment() {
    $json = array();
            
		$data = $this->input->post('data');
		$data = json_decode($data);

		$password = "";
		$email = "";
                
		switch($data->password_type) {
			case "random":
				$email = $data->email;
				break;
			case "temporary":
			case "shared":
				$password = $data->password;
				break;
		}
		$prefix = $this->session->userdata('uid') . $data->experiment_code;

    $this->experiments->experiment_code = $data->experiment_code;
    $experiment = $this->experiments->get();
    if(isset($experiment[0]->experiment_code)){
        $json['error'] = "Experiment already exists.  Please use the table to update this experiment.";
        print json_encode($json);
    }else{
        //Experiment Table
        $this->experiments->experiment_code = $data->experiment_code;
        $this->experiments->consent_form = $data->consent;
        $this->experiments->experimenter_code = $this->session->userdata('uid');
        //$this->experiments->num_participants = $data->num_participants;
		//NEW USER GROUP FUNCTIONALITY
		$this->experiments->num_groups = $data->num_groups;
		$this->experiments->users_per_group = $data->users_per_group;
		$this->experiments->active = '1';
		$this->experiments->ordering = $data->ordering;
        $this->experiments->save();
     
        //Parameter tables
        
        foreach($data->task_params as $tp){
            $task_id = explode('_',$tp->task);
            $task_id = end($task_id);
            
            $this->tasks->id = $task_id;
            $task = $this->tasks->get();
            $task_name = strtolower($task[0]->name) . '_parameters';
            
            $tables = $this->db->list_tables();
            if(in_array($task_name,$tables)){
                $this->load->model('model_'.$task_name,$task_name,TRUE);
                $this->$task_name->experiment_code = $data->experiment_code;
                $this->$task_name->insert_parameters($tp);
            }
            
        }
        $userPass = "List for Experiment: " . $data->experiment_code . "\n\nUSER ID | PASSW\n\n";
		
		//user table
        //for($i=1 ; $i <= $data->num_participants ; $i++) {
        for($i=1;$i<=$data->num_groups;$i++){
			
			for($j=1;$j<=$data->users_per_group;$j++){
				$suffix = sprintf("%02d",$i) . sprintf("%02d",$j);
				$user_id = $prefix . $suffix;
				if(preg_match('/random/i',$data->password_type)){
					$password = $this->generatePassword();
						$userPass .= $user_id . " " . $password . "\n";
				}
				$data->user_id = $user_id;
				$data->password = $password;
				$this->createNewUser($data);
			}
				
        }
        if(preg_match('/random/i',$data->password_type)){
          mail($data->email,'User List',$userPass);
        }
        $data->result = "Experiment created successfully.";
        print json_encode($data);
    }
  }   

  /**
   *    Adds one researcher.
   */
	function addResearcher() {

		$data = $this->input->post('data');
		$data = json_decode($data);
		
		$user_id = $data->experimenter_code;
		$password = $data->password;
		$email = $data->email;

    $this->user->user_id = $user_id;
    $user = $this->user->get();
    
    if(isset($user->user_id)){
        print "User " . $user_id . " already exists.";
    }else{
        $this->user->password = $password;
        $this->user->curr_session = 1;
        $this->user->active = '0';
        $this->user->experiment_code = $user_id;
        $this->user->permissions = $data->permissions;
        $this->user->save();
        
				$researcher_type = '';
				
				switch($data->permissions){
					case "G":
						$researcher_type = "Administrative Researcher";
						break;
					case "RA":
						$researcher_type = "Assessment Researcher";
						break;
					case "RT":
						$researcher_type = "Training Researcher";
						break;
					case "RTA":
						$researcher_type = "Training and Assessment Researcher";
						break;
				}
				$email_text = "You have been added as a " . $researcher_type . " to the Diagnostic Cognitive Testing and Training system.\n\nUser ID: " . $user_id . "\nTemporary Password: " . $password . "\n\nTo access the system point your browser to " . base_url() . "index.php/increaseintellect \nUpon first logging in you will be required to change your password.";
				
				mail($email,'An Account Has Been Created For You',$email_text);
				
        print "User " . $user_id . " created.  An email has been sent to notify them.";
    }
    
	}

  /**
   *  Collects all data requested by a researcher.  *All* refers to all
   *  data from every experiment the researcher has run.  If *All* not
   *  specified then it looks for any data from experiments specified
   *  by the experiment codes passed in as POST input.
   */
	function downloadData() {
		$this->load->dbutil();
		$this->load->library('zip');
    $this->load->model('model_tasks','tasks',TRUE);
    ini_set('memory_limit', '64M');
    
		$codes = $this->input->post('codes');
		$codes = json_decode($codes);
 
		/*if(in_array("*All*",$codes->codes)) {

      $tasks = $this->tasks->get();

			foreach($tasks as $task) {

				$name = $task->name . '.csv';

				$this->db->select('*');
				$this->db->from(strtolower($row->name));
				$this->db->where('user_id REGEXP', $this->session->userdata('uid'));
				$query = $this->db->get();

				if($query->num_rows > 0) {
					$csv = $this->dbutil->csv_from_result($query);
					$this->zip->add_data($name,$csv);
				}
			}
		}else {*/

			foreach($codes->codes as $c) {
        
        $tasks = $this->tasks->get();
        
				foreach($tasks as $task) {
            
					/*if(strtolower($task->name) == 'numberpiles_training')
						continue;*/
					$name = $c . '_' . $task->name . '.csv';

					$this->db->select('*');
					$this->db->from(strtolower($task->name));
					$this->db->where('user_id REGEXP', $c);
					$query = $this->db->get();

					if($query->num_rows > 0) {
						$csv = $this->dbutil->csv_from_result($query);
						$this->zip->add_data($name,$csv);
					}
				}
			}
		//}
		$filename = $this->session->userdata('uid') . "_" . date("ymd") . ".zip";
		$this->zip->archive("user_data/".$filename);
		print base_url()."user_data/" . $filename;
	}

  /**
   *    This function is called when a researcher uploads a new consent form
   *      on the researcher page.  The consent forms are placed in
   *      'assets/consent_forms/' and only pdf's are allowed.
   */
	function upload_consent_form() {
		$config['upload_path'] = 'assets/consent_forms/';
		$config['allowed_types'] = 'pdf';
		$config['max_size']	= '0';
		$config['max_width']  = '0';
		$config['max_height']  = '0';

		$this->load->library('upload', $config);
		$this->upload->do_upload();
		echo "success";
	}
	
/**
	 *	Ends an experiment by setting 'active' to 0;
	 *
	 */
	function end_experiment(){
		$data = $this->input->post('data');
		$data = json_decode($data);
		
		$experiment_id = $data->experiment;
		
		$this->experiments->clear();
		$this->experiments->experiment_code = $experiment_id;
		$this->experiments->active = 'no';
		$this->experiments->save(TRUE);
		
		print "Experiment Ended.";
		
	}
	/**
	 *	Generates a random password of the given length with
	 * the given strength.  Code taken from:
	 *
	 *	http://www.webtoolkit.info/php-random-password-generator.html
	 *
	 * @param <int> $length
	 * @param <int> $strength
	 * @return <string>
	 *
	 */
	function generatePassword($length=9, $strength=0) {
		$vowels = 'aeuy';
		$consonants = 'bdghjmnpqrstvz';
		if ($strength & 1) {
			$consonants .= 'BDGHJLMNPQRSTVWXZ';
		}
		if ($strength & 2) {
			$vowels .= "AEUY";
		}
		if ($strength & 4) {
			$consonants .= '23456789';
		}
		if ($strength & 8) {
			$consonants .= '@#$%';
		}

		$passw = '';
		$alt = time() % 2;
		for ($i = 0; $i < $length; $i++) {
			if ($alt == 1) {
				$passw .= $consonants[(rand() % strlen($consonants))];
				$alt = 0;
			} else {
				$passw .= $vowels[(rand() % strlen($vowels))];
				$alt = 1;
			}
		}
		return $passw;
	}
  
  /**
   *    Adds a new user to the system by updating 3 tables:
   *        user
   *        permissions
   *        session_info (if applicable)
   *
   */
  function createNewUser($user_data){
     //User Table
     $this->user->user_id = $user_data->user_id;
     $this->user->password = $user_data->password;
     $this->user->curr_session = 1;
     if(preg_match('/temporary/',$user_data->password_type)){
        $this->user->active = '0';
     }else{
        $this->user->active = '1';
     }
     $this->user->experiment_code = $user_data->experiment_code;
	 
//     $this->user->permissions = implode(',',$user_data->permissions);
	$group = explode($user_data->experiment_code,$user_data->user_id);
	$group = substr($group[2],-4,2);
     $this->user->permissions = $user_data->permissions->$group;
     $this->user->save();
              
    //Session table
    //if($user_data->language == '1' || $user_data->mathematics == '1' || $user_data->spatial == '1'){
    //if(in_array(array('spatial_training','language_trainning','mathematics_training'),$this->tasks->get_type($data->permissions)));
    if(preg_match('/training/i',implode($this->tasks->get_type($user_data->permissions)))){
        $this->session_info->user_id = $user_data->user_id;
        $this->session_info->curr_session = 1;
        $this->session_info->max_session = $user_data->num_of_sessions;
        $this->session_info->memnosyne_training_status = "0000-00-00 00:00:00";
        $this->session_info->sentencical_training_status = "0000-00-00 00:00:00";
        $this->session_info->numberpiles_training_status = "0000-00-00 00:00:00";
		$this->session_info->shapebuilder_training_status = "0000-00-00 00:00:00";
        $this->session_info->start_time = "0000-00-00 00:00:00";
        $this->session_info->end_time = "0000-00-00 00:00:00";
        $this->session_info->save();
    }
  }
}
