<?php
class Model_sentencical_training extends Model{
  /**
   *  Table: sentencical_training
   *
   *  Holds all data for the training task sentencical.
   *
   *  @author: MikeD
   *
   *
   */
  
  var $user_id;               //Participant ID
  var $sentence_id;           //Sentence ID
  var $user_answer;           //Either yes or no (1 or 0)
  var $cor_answer;            //Either yes or no (1 or 0)
  var $start_time_sentence;   //Time sentence began showing on the screen
  var $end_time_sentence;     //Time sentence cleared the screen
  var $start_time_question;   //Time question was shown
  var $end_time_question;     //Time question was answered
  var $score;                 //User score
  var $session;               //Session
  
  const TABLE_NAME = "sentencical_training";
  
  function __construct(){
    parent::Model();
    get_instance()->load->helper('file');
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
      'sentence_id' => $this->sentence_id,
      'user_answer' => $this->user_answer,
      'cor_answer' => $this->cor_answer,
      'start_time_sentence' => $this->start_time_sentence,
      'end_time_sentence' => $this->end_time_sentence,
      'start_time_question' => $this->start_time_question,
      'end_time_question' => $this->end_time_question,
      'score' => $this->score,
      'session' => $this->session
    );
    
    $this->db->insert(self::TABLE_NAME,$db_array);
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