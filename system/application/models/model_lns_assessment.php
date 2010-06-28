<?php
class Model_lns_assessment extends Model{
  /**
   *  Table: lns_assessment
   *
   *  Holds all data for the assessment task lns
   *
   *  @author: MikeD
   *
   */
  
  public $user_id;       //Participant ID
  public $i_round;       //Round #
  public $i_sequence;    //Sequence #
  public $num_seq;       //Total # of sequences in round
  public $num_symbols;   //# of blocks lit up during round
  public $num_cor;       //How many blocks the user selected
  public $abs_cor;       //If the user selected all the correct blocks
  public $display;       //Which blocks lit up
  public $input;         //Whhich blocks the user selected
  public $start_time;    //Round start time
  public $end_time;      //Round end time
  public $score;         //Current score
  public $id;
  
  const TABLE_NAME = "lns_assessment";
  
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
      'user_id' => $this->user_id,
      'i_round' => $this->i_round,
      'i_sequence' => $this->i_sequence,
      'num_seq' => $this->num_seq,
      'num_symbols' => $this->num_symbols,
      'num_cor' => $this->num_cor,
      'abs_cor' => $this->abs_cor,
      'display' => $this->display,
      'input' => $this->input,
      'start_time' => $this->start_time,
      'end_time' => $this->end_time,
      'score' => $this->score,
      'id' => $this->id
    );
    
    $this->db->insert(self::TABLE_NAME, $db_array);
  }
  function get_num_complete(){
    $sql = "SELECT DISTINCT id from " . self::TABLE_NAME . " where user_id = ?";
    $query = $this->db->query($sql,array($this->user_id));
    return $query->num_rows();
  }
  
  function get_high_scores(){
    if(!$this->user_id){
      return array();
    }
    $scores = array();
    $sql = "select distinct id, MAX(score) as score from ". self::TABLE_NAME . " where user_id = ? group by id";
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