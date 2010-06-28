<?php
class Model_session_info extends Model{
  /**
   *  Table: session_info
   *
   *  Stores session information for every user who has to complete sessions.
   *
   */
  
  public $user_id;                       //Participant ID
  public $curr_session;                  //Current session # (e.g. 1.10)
  public $max_session;                   //Maximum session #
  public $memnosyne_training_status;     //MySQL datetime, all 0's when incomplete
  public $sentencical_training_status;   //  when not all 0's then this represents
  public $numberpiles_training_status;   //  the time it was completed
  public $shapebuilder_training_status;
  public $start_time;                    //Time when user started session
  public $end_time;                      //Time when user completed session
  
  const TABLE_NAME = "session_info";
  
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
  
  /**
   *  If $update = TRUE
   *
   *    Function looks to update an entry in the table.  When a user completes
   *    a part of their session then the entry in the table is updated.  Also
   *    updated when the user starts a session and ends a session.
   *
   */
  function save($update = FALSE){
    if($update){
      $this->db->where('user_id',$this->user_id);
      $this->db->where('curr_session',$this->curr_session);
      
      if($this->memnosyne_training_status){
        $this->db->update(self::TABLE_NAME,array('memnosyne_training_status'=>$this->memnosyne_training_status));  
      }elseif($this->sentencical_training_status){
        $this->db->update(self::TABLE_NAME,array('sentencical_training_status'=>$this->sentencical_training_status));  
      }elseif($this->numberpiles_training_status){
        $this->db->update(self::TABLE_NAME,array('numberpiles_training_status'=>$this->numberpiles_training_status));  
      }elseif($this->shapebuilder_training_status){
        $this->db->update(self::TABLE_NAME,array('shapebuilder_training_status'=>$this->shapebuilder_training_status));
      }elseif($this->start_time){
        $this->db->update(self::TABLE_NAME,array('start_time'=>$this->start_time));
      }elseif($this->end_time){
        $this->db->update(self::TABLE_NAME,array('end_time' => $this->end_time));
      }
    }else{
      $db_array = array(
        "user_id" => $this->user_id,
        "curr_session" => $this->curr_session,
        "max_session" => $this->max_session,
        "memnosyne_training_status" => $this->memnosyne_training_status,
        "sentencical_training_status" => $this->sentencical_training_status,
        "numberpiles_training_status" => $this->numberpiles_training_status,
        "shapebuilder_training_status" => $this->shapebuilder_training_status,
        "start_time" => $this->start_time,
        "end_time" => $this->end_time
      );
      $this->db->insert(self::TABLE_NAME,$db_array);
    }
  }
  
  /**
   *  Returns the number of participants who have completed all their training.
   *
   */
  function get_num_complete(){
    $this->db->where('user_id REGEXP', $this->user_id);
    $query = $this->db->get(self::TABLE_NAME);
    if($query->num_rows > 0){
      $row = $query->row();
      $this->max_session = $row->max_session - 1;
    
      $this->db->where('user_id REGEXP', $this->user_id);
      $this->db->where('curr_session',$this->max_session);
      $this->db->where('end_time !=','0000-00-00 00:00:00');
      $query = $this->db->get(self::TABLE_NAME);
      return $query->num_rows();
    }else{
      return "";
    }   
  }
  
  
  /**
   *
   *  Gets the current session of $user_id
   *
   *  @return - array : Current session, Session start time
   */
  function get_curr_session(){
    if(!$this->user_id){
      return "";
    }
    
    $this->db->select_max('curr_session');
    $this->db->select('start_time');
    $this->db->where('user_id',$this->user_id);
    
    $query = $this->db->get(self::TABLE_NAME);
    
    if($query->num_rows() > 0){
      $row = $query->row();
      
      $this->db->select('end_time');
      $this->db->where('user_id',$this->user_id);
      $this->db->where('curr_session',$row->curr_session);
      
      $query = $this->db->get(self::TABLE_NAME);
      
      if($query->num_rows() > 0){
        $end_time = $query->row();
        if($end_time->end_time == '0000-00-00 00:00:00'){
          return array($row->curr_session,$row->start_time);
        }else{
          return array($row->curr_session + 1,$row->start_time);
        }
      }
      
    }
  }
  
  function get_num_sessions_complete(){
    if(!$this->user_id){
      return "";
    }
    
    $this->db->select_max('curr_session');
    $this->db->select('start_time');
    $this->db->where('user_id',$this->user_id);
    
    $query = $this->db->get(self::TABLE_NAME);
    
    if($query->num_rows() > 0){
      $row = $query->row();
      
      $this->db->select('end_time');
      $this->db->where('user_id',$this->user_id);
      $this->db->where('curr_session',$row->curr_session);
      
      $query = $this->db->get(self::TABLE_NAME);
      
      if($query->num_rows() > 0){
        $end_time = $query->row();
        if($end_time->end_time == '0000-00-00 00:00:00'){
          return $row->curr_session - 1;
        }else{
          return $row->curr_session;
        }
      }
      
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