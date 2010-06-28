<?php
class Model_numberpiles_assessment extends Model{

  var $user_id;
  var $score;
  var $levelNumber;
  var $levelMatches;
  var $levelMisses;
  var $start_time;
  var $end_time;
  var $id;
  
  const TABLE_NAME = "numberpiles_assessment";
   
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
      'levelNumber' => $this->levelNumber,
      'levelMatches' => $this->levelMatches,
      'levelMisses' => $this->levelMisses,
      'start_time' => $this->start_time,
      'end_time' => $this->end_time,
      'id' => $this->id
    );
    
    $this->db->insert(self::TABLE_NAME, $db_array);
  }
  
  function save_leveldata($user_id, $user_values, $targetValue, $levelNumber, $id){
    $db_array = array(
      'user_id' => $user_id,
      'targetValue' => $targetValue,
      'user_values' => $user_values,
      'levelNumber' => $levelNumber,
      'id' => $id
    );
    
    $this->db->insert('numberpiles_assessment_leveldata', $db_array);
    return $db_array;
  }
  
  function get_high_score(){
    if(!$this->user_id){
      return array();
    }
    
    $sql = "SELECT MAX(score) FROM " . self::TABLE_NAME . " WHERE user_id = ?";
    $values = array('user_id' => $this->user_id);
    $query = $this->db->query($sql,$values);
    
    
    if($query->num_rows() > 0){
      $row = $query->row_array();
      return $row['MAX(score)'];
    }
        
    return "error";
  }
  
  function get_high_scores(){
    if(!$this->user_id){
      return array();
    }
    
    $scores = array();
    $sql = "select distinct id, MAX(score) as score from " . self::TABLE_NAME . " where user_id = ? group by id";
    $query = $this->db->query($sql,array($this->session->userdata('uid')));
    if($query->num_rows() > 0){
      foreach($query->result() as $row){
        $scores[$row->id] = $row->score;
        
      }
    }
    return $scores;
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
  function get_num_complete(){
    $sql = "SELECT DISTINCT id from " . self::TABLE_NAME . " where user_id = ?";
    $query = $this->db->query($sql,array($this->user_id));
    return $query->num_rows();
  }
  function get_last_time_played(){
    $sql = "SELECT MAX(start_time) as start_time from " . self::TABLE_NAME . " where user_id = ?";
    $query = $this->db->query($sql,array($this->user_id));
    if($query->num_rows() > 0){
      $row = $query->row();
      return $row->start_time; 
    }else{
      return "0000-00-00 00:00:00";
    }
  }

}
