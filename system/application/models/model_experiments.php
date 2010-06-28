<?php
class Model_experiments extends Model{
  
  var $experiment_code;     //Experiment code
  var $experimenter_code;   //Code of experimenter running experimenter
  var $consent_form;        //File name of consent form, 'noconsent' if none
  //var $num_participants;    //# of participants completing experiment
  var $active;              //If 1 then experiment in progress, otherwise ended
  var $ordering;            //If 1 then assessments must come before training, if 2 then unlimited assessments
  var $num_groups;
  var $users_per_group;
  
  const TABLE_NAME = "experiments";
  
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
    
    if($update){
      /*if($this->num_participants){ //Update number of participants in experiment
        $this->db->where('experiment_code',$this->experiment_code);
        $this->db->update(self::TABLE_NAME,array('num_participants'=>$this->num_participants));
      }else if($this->active){
        $this->db->where('experiment_code',$this->experiment_code);
        $this->db->update(self::TABLE_NAME,array('active'=>0));
      }*/
    }else{
      $db_array = array(
        'experiment_code' => $this->experiment_code,
        'experimenter_code' => $this->experimenter_code,
        'consent_form' => $this->consent_form,
        //'num_participants' => $this->num_participants,
        'num_groups' => $this->num_groups,
        'users_per_group' => $this->users_per_group,
        'active' => $this->active,
        'ordering' => $this->ordering
      );
    
      $this->db->insert(self::TABLE_NAME,$db_array);
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