<?php
class Model_consent extends Model{
  /**
   *  Table: consent
   *
   *  Stores information for experiments that require consent.
   *
   *  @author: MikeD
   *
   */
  
  var $user_id;             //Participant ID
  var $date;                //Date consent was accepted/declined
  var $consent_form;        //File name of consent form
  var $experiment_code;     //Experiment code user is apart of
  var $experimenter_code;   //Code of the experimenter running experiment
  var $accept;              //Value indicating whether consent was accepted 

  const TABLE_NAME = "consent";
  
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
      'date' => $this->date,
      'consent_form' => $this->consent_form,
      'experiment_code' => $this->experiment_code,
      'experimenter_code' => $this->experimenter_code,
      'accept' => $this->accept
    );
    
    $this->db->insert(self::TABLE_NAME, $db_array);
  }
}
?>