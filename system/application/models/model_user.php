<?php
class Model_user extends Model{
  /**
   *  Table: user
   *
   *  Stores information for every user in the system.  This includes the
   *  user permissions.  More later.
   *
   *
   */
  
  var $user_id;          //Participants ID
  var $password;         //User PW hashed with SHA1
  var $active;           //If 0 than user needs to reset PW
  var $curr_session;     //Current Session, starting from 0
  var $experiment_code;  //Experiment code that the participant is apart of, NULL if no experiment
  
  /**
   *  User Permissions, in the form of task_id,task_id,etc...
   *
   *  Special Permissions:
   *
   *  'RA' => Assessment researcher
   *  'RTA' => Assessment and Training researcher
   *  'RT' => Training researcher
   *  'G' => All powerful researcher
   *  
   *
   */
  var $permissions;      //User Permissions, in the form of task_id,task_id,etc..
  
  const TABLE_NAME = "user";
  
  function __construct(){
    parent::Model();
  }
  
  /**
   *  Passing a value of TRUE to this function will return all users that
   *  match the criteria, otherwise it just returns one entry out of the table
   */
  function get($all = FALSE){
     foreach(get_class_vars(get_class($this)) as $key=>$value){
      if($key != "_parent_name"){
        $val = $this->$key;
        if($val){
          $this->db->where($key, $val);
        }
      }
    }
    
    $query = $this->db->get(self::TABLE_NAME);
    
    if($all){
      return ($query->result());
    }else{
      return ($query->row()); 
    }
  }
  
  /**
   *  A value of TRUE passed into this function will update the table.  You can update
   *  the active value for the user which indicates that the user has changed
   *  their password or you can update the curr_session of the user.
   *
   */
  function save($update = FALSE){
    if($update){
      
      if($this->password && $this->active && $this->user_id){
        
        $sql = "UPDATE " . self::TABLE_NAME . " SET password = SHA1(?),active = ? WHERE user_id = ?";
        $this->db->query($sql,array($this->password,$this->active,$this->user_id));
        
      }elseif($this->curr_session && $this->user_id){
        
        $sql = "UPDATE " . self::TABLE_NAME . " SET curr_session = ? where user_id = ?";
        $this->db->query($sql,array($this->curr_session,$this->user_id));
        
      }
    }else{
      $sql = "INSERT INTO " . self::TABLE_NAME . " VALUES(?,SHA1(?),?,?,?,?)";
      $this->db->query($sql,array(
        $this->user_id,
        $this->password,
        $this->active,
        $this->experiment_code,
        $this->curr_session,
        $this->permissions)
      );
    }
  }
  
  /**
   *  @return permissions for the user in the form of an array
   *
   */
  function get_permissions(){
    $this->db->where('user_id',$this->user_id);
    $query = $this->db->get(self::TABLE_NAME);
    
    if($query->num_rows() > 0){
      $row = $query->row();
      
      $permissions = $row->permissions;
      
      return explode(',',$permissions);
    }
    return array();
  }
  
  /**
   *  Determines if a user has permission for a specific task or
   *  general functionality.
   *
   *  @param $task - Either a task id, task name, or task type, function
   *  @return boolean
   *
   */
  function has_permission($task){
    if(!$this->user_id)
      return TRUE;
    
    $permissions = $this->get_permissions();
    
    if(empty($permissions))
      return false;
    
    //If researcher, automatically give permission
    if(in_array('G',$permissions))
      return TRUE;
    
    //Research area
    if($task == 'research'){
      if(in_array('G',$permissions) || in_array('RA',$permissions) ||
         in_array('RT',$permissions) || in_array('RTA',$permissions)){
        return TRUE;
      }else{
        return FALSE;
      }
    }
    //Training/Assessment Researchers
    
    $CI =& get_instance();
    $CI->load->model('model_tasks','tasks',TRUE);
    
    if(is_numeric($task)){ //Task ID
      $CI->tasks->id = $task;
      $task_info = $CI->tasks->get();
      if(isset($task_info[0]->name)){
        if(preg_match('/assessment/i',$task_info[0]->type) && (in_array('RTA',$permissions) ||
                                                  in_array('RA',$permissions))){
          return TRUE;
        }elseif(preg_match('/training/i',$task_info[0]->type) && (in_array('RTA',$permissions) ||
                                                  in_array('RT',$permissions))){
          return TRUE;
        }
      }
      
      
      return in_array($task,$permissions);
    }elseif($task == 'training'){
      
      if(in_array('RTA',$permissions) || in_array('RT',$permissions)){
        return TRUE;
      }
      $CI->tasks->type = $task;
      $tasks = $CI->tasks->get_tasks();
      foreach($tasks as $id => $name){
        if(in_array($id,$permissions)){
          return TRUE;
        }
      }
      return FALSE;
      
    }elseif($task == 'assessment'){ // Task Type
      
      if(in_array('RTA',$permissions) || in_array('RA',$permissions)){
        return TRUE;
      }
      
      $CI->tasks->type = $task;
      $tasks = $CI->tasks->get_tasks();
      foreach($tasks as $id => $name){
        if(in_array($id,$permissions)){
          return TRUE;
        }
      }
      return FALSE;
    }else{ //Task Name
      $CI->tasks->name = $task;
      
      $tasks = $CI->tasks->get();
      foreach($tasks as $t){
        if(preg_match('/assessment/i',$t->type) && (in_array('RTA',$permissions) ||
                                                  in_array('RA',$permissions))){
          return TRUE;
        }elseif(preg_match('/training/i',$t->type) && (in_array('RTA',$permissions) ||
                                                  in_array('RT',$permissions))){
          return TRUE;
        }
        
        if(in_array($t->id,$permissions)){
          return TRUE;
        }
      }
      return FALSE;
    }
  }
  
}
?>