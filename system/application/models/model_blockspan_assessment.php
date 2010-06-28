<?php
class Model_blockspan_assessment extends Model{
  /**
   *  Table: blockspan_assessment
   *
   *  Holds all data for the assessment task blockspan
   *  
   *  @author: MikeD
   *
   */
  
  //Table columns
  var $user_id;       //Participant ID
  var $i_round;       //Round #
  var $i_sequence;    //Sequence #
  var $num_sequence;  //The total # of sequences
  var $num_symbols;   //# of blocks lit up during round
  var $num_cor;       //How many blocks the user selected
  var $abs_cor;       //If the user selected all the correct blocks
  var $display;       //Which blocks lit up
  var $input;         //Whhich blocks the user selected
  var $start_time;    //Round start time
  var $end_time;      //Round end time
  var $score;         //Current score
  var $id;            //Random # to differentiate between trials
  
  //Table name
  const TABLE_NAME = "blockspan_assessment";
  
  function __construct(){
    parent::Model();
  }
  
  /**
   *  get() looks funny but its slick.  Cycles through each class var and
   *  adds a where statement for every set variable.  Then queries the database.
   *
   */
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
  
  function save() {

    $db_array = array(
      'user_id' => $this->user_id,
      'i_round' => $this->i_round,
      'i_sequence' => $this->i_sequence,
      'num_sequence' => $this->num_sequence,
      'num_symbols' => $this->num_symbols,
      'num_cor' => $this->num_cor,
      'abs_cor' => $this->abs_cor,
      'display' => $this->display,
      'input' => $this->input,
      'start_time' => $this->start_time,
      'end_time' => $this->end_time,
      'score' => $this->score,
      'id' => $this->id,
    );
    
    $this->db->insert('blockspan_assessment', $db_array);
  }
  
  //Gets the number of times the user has complete this task
  function get_num_complete(){
    $sql = "SELECT DISTINCT id from " . self::TABLE_NAME . " where user_id = ?";
    $query = $this->db->query($sql,array($this->user_id));
    return $query->num_rows();
  }
 
  /**
   *  Get highscores for each time the user has played
   *
   *  @return: array(uniq_id=>highscore)
   *
   */
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
  
  /**
   *  Gets average score for user across all plays
   *
   *  @return: int-average score
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
