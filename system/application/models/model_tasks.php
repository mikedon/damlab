<?php
class Model_tasks extends Model{
  
  /**
   *  Table: tasks
   *
   *  Purpose: Hold information about different tasks
   *
  */
  
  
  /**
   *  Table Vars
  */
  var $name;   //Name of the task
  var $type;        //Task type (e.g. spatial_assessment, language_training)
  var $pic_url;     //Location of task picture
  var $task_url;    //Location of task swf
  var $id;
  
  const TABLE_NAME = "tasks";
  
  function __construct(){
    parent::Model();
  }
  
  function get(){
    foreach(get_class_vars(get_class($this)) as $key=>$value){
      if($key != "_parent_name"){
        $val = $this->$key;
        if($val){
          $this->db->where($key, $val);
        }
      }
    }
    
    $query = $this->db->get(self::TABLE_NAME);
    
    return ($query->result());      
  }
  
  function save(){
    $db_array = array(
      'name' => $this->name,
      'type' => $this->type,
      'pic_url' => $this->pic_url,
      'task_url' => $this->task_url,
      'id' => $this->id
    );
    
    $this->db->insert(self::TABLE_NAME,$db_array);
    
  }
  /**
   *  Gets all tasks of a certain type.
   */
  function get_tasks(){
    $tasks = array();
    
    $this->db->where('type REGEXP', $this->type);
    $query = $this->db->get(self::TABLE_NAME);
    if($query->num_rows() > 0){
      foreach($query->result() as $row){
        $tasks[$row->id] = $row->name; 
      }
    }
    return $tasks;
  }
  /**
   *  Returns the 'type' of a set of tasks, either assessment or some type of training
   *
   *  @param $tasks - an array of task id's, if none passed
   *                  in than look at $this->task_name
   *  @return array task types as declared in database
   */
  function get_type($tasks = NULL){
    
    $task_type = array();
    
    if($tasks != NULL){
      foreach($tasks as $t){
        $this->db->where('id',$t);
        $query = $this->db->get(self::TABLE_NAME);
        if($query->num_rows() > 0){
          $row = $query->row();
          $task_type[$t] = $row->type;
        }
      }
    }else{
      $this->db->where('name',$this->name);
      $query = $this->db->get(self::TABLE_NAME);
      if($query->num_rows() > 0){
        $row = $query->row();
        return array($row->type);
      }
    }
    return $task_type;
  }
  
  //Gets num complete for assessment tasks
  function get_num_complete($user_id){
    $this->db->where('id',$this->id);
    $query = $this->db->get(self::TABLE_NAME);
    
    if($query->num_rows() > 0){
      $row = $query->row();
      $task_name = strtolower($row->name);
      
      $sql = "SELECT DISTINCT id from $task_name where user_id REGEXP ?";
      $query = $this->db->query($sql,array($user_id));
      return $query->num_rows();
    }
  }
   /**
   *  Clears all values for future queries
   */
  function clear(){
    foreach(get_class_vars(get_class($this)) as $key=>$value){
      if($key != "_parent_name"){
        $this->$key = '';
      }
    }
  }
}
?>