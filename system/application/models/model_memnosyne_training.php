<?php
/**
 *  Table: memnosyne_training
 *
 *  Holds all data for the training task memnosyne.
 *
 *  @author: MikeD
 *
 */
class Model_memnosyne_training extends Model{

  public $user_id;       //Participant ID
  public $session;       //Session #
  public $i_round;       //Round #
  public $num_symbols;   //# of blocks lit up during round
  public $perc_correct;  //Correct # of blocks clicked as a %
  public $num_cor;       //How many blocks the user selected
  public $abs_cor;       //If the user selected all the correct blocks
  public $display;       //Which blocks lit up
  public $input;         //Whhich blocks the user selected
  public $start_time;    //Round start time
  public $end_time;      //Round end time
  public $score;         //Current score

  const TABLE_NAME = "memnosyne_training";
  
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
  
  function delete(){
    if(!($this->user_id) || !($this->session)){
      return FALSE;
    }else{
      $this->db->where('session',$this->session);
      $this->db->where('user_id',$this->user_id);
      $this->db->delete(self::TABLE_NAME);
      return TRUE;
    }
  }
  
  function save() {

    $db_array = array(
      'user_id' => $this->user_id,
      'session' => $this->session,
      'i_round' => $this->i_round,
      'num_symbols' => $this->num_symbols,
      'perc_correct' => $this->perc_correct,
      'num_cor' => $this->num_cor,
      'abs_cor' => $this->abs_cor,
      'display' => $this->display,
      'input' => $this->input,
      'start_time' => $this->start_time,
      'end_time' => $this->end_time,
      'score' => $this->score,
    );
    
    $this->db->insert(self::TABLE_NAME, $db_array);
  }
  
  /**
   *  Grabs user_id's current difficulty based on the last time they played
   *
   *  @return current difficulty
   */
  function current_difficulty(){
    if(!($this->user_id)){
      return "";
    }
    
    $this->db->where("user_id",$this->user_id);
    $this->db->order_by("start_time","desc");
    $this->db->limit(1);
    
    $query = $this->db->get(self::TABLE_NAME);
    
    if($query->num_rows > 0){
      $row = $query->row();
      return $row->num_symbols;
    }
    
    return "";
  }
  
  /**
   *  Get's the max score for user during session as specified by $this->session
   *
   *  @param $userID - a user id, NULL if none passed in
   *  @return max score
   *
   */
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
  
  
  /**
   *  Get average score for task during a given session as specified by
   *  $this->session.  Average calculated by looking at the max score for
   *  every user during a session and dividing by total number of users who
   *  have played memnosyne.
   *
   *  @return average score
   *
   */
  function get_avg_score(){
    $CI =& get_instance();
    $CI->load->model('model_user','user',TRUE);
    $users = $CI->user->get(TRUE);
    
    $score = 0;
    $counter = 0;
    foreach($users as $u){
      $this->user_id = $u->user_id;
      $this->input = '';
      $this->session = '';
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
