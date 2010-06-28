<?php
class Model_shapebuilder_training extends Model{

  var $user_id;
  var $score;
  var $round;
  var $stim;
  var $resp;
  var $start_time;
  var $end_time;
  var $id;
  var $version;
  var $stim_count;
  
 
 const TABLE_NAME = "shapebuilder_training";
 
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
  
  function save($update = FALSE){
    $db_array = array(
      'user_id' => $this->user_id,
      'score' => $this->score,
      'round' => $this->round,
      'stim' => $this->stim,
      'resp' => $this->resp,
      'start_time' => $this->start_time,
      'end_time' => $this->end_time,
      'id' => $this->id,
      'session' => $this->session,
      'version' => $this->version,
      'stim_count' => $this->stim_count
    );
    
    $this->db->insert(self::TABLE_NAME, $db_array);
  } 
  
  function get_high_scores(){
    if(!($this->user_id)){
      return "";
    }
    
    $scores = array();
    
    $sql = "SELECT distinct session, MAX(score) as score from " . self::TABLE_NAME . " where user_id=? group by session";
    $query = $this->db->query($sql,array($this->user_id));
    if($query->num_rows() > 0){
      foreach($query->result() as $row){
        $scores[$row->session] = $row->score;
      }
    }
    
    return $scores;
    
  }
  
  /*
  function save_leveldata($user_id, $user_values, $targetValue, $levelNumber, $id){
    $db_array = array(
      'user_id' => $user_id,
      'targetValue' => $targetValue,
      'user_values' => $user_values,
      'levelNumber' => $levelNumber,
      'id' => $id
    );
    
    $this->db->insert('numberpiles_training_leveldata', $db_array);
    return $db_array;
  }
  */
  
  //used to get the higest stimulus difficulty through all sessions
  function get_high_stimCount(){
    if(!$this->user_id){
      return 2;
    }
    
    $sql = "SELECT MAX(stim_count) FROM ". self::TABLE_NAME ." WHERE user_id = ?";
    $values = array('user_id' => $this->user_id);
    $query = $this->db->query($sql,$values);
    
    
    if($query->num_rows() > 0){
      $row = $query->row_array();
      return $row['MAX(stim_count)'];
    }
        
    return 3;
  
  }
  
  //used to get last sessions difficulty
  function current_difficulty(){
    if(!($this->user_id)){
      return "";
    }
    
    $this->db->where("user_id",$this->user_id);
    $this->db->where("round !=", 0);
    $this->db->order_by("start_time","desc");
    $this->db->limit(1);
    
    $query = $this->db->get(self::TABLE_NAME);
    
    if($query->num_rows > 0){
      $row = $query->row();
      return $row->stim_count;
    }
    
    return "";
  }
  
  function get_high_score(){
    if(!$this->user_id){
      return array();
    }
    
    $sql = "SELECT MAX(score) FROM ". self::TABLE_NAME ." WHERE user_id = ?";
    $values = array('user_id' => $this->user_id);
    $query = $this->db->query($sql,$values);
    
    
    if($query->num_rows() > 0){
      $row = $query->row_array();
      return $row['MAX(score)'];
    }
        
    return "error";
  }
  
  function get_avg_score(){
    $CI =& get_instance();
    $CI->load->model('model_user','user',TRUE);
    $users = $CI->user->get(TRUE);
    
    $score = 0;
    $counter = 0;
    foreach($users as $u){
      
      $this->user_id = $u->user_id;
      $this->input = '';
      $data = $this->get();
      
      if(isset($data[0]->user_id)){
        $high_scores = $this->get_high_scores();
        foreach($high_scores as $id => $s){
          $score += $s;
          $counter++;
        }
      }
    }
    if($counter != 0){
      return $score/$counter;
    }
  }  
}