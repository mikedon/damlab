<?php
class Model_lns_training_parameters extends Model{
  
  var $experiment_code; //Experiment code participant is apart of
  var $time_limit;      //Amount of time alloted to play task
  
  const TABLE_NAME = "lns_training_parameters";
  
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
      'experiment_code' => $this->experiment_code,
      'time_limit' => $this->time_limit
    );
    
    $this->db->insert(self::TABLE_NAME,$db_array);
  }
  
  function insert_parameters($params){
    $this->time_limit = $params->time_limit;
    $this->save();
  }
}
