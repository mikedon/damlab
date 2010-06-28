<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

class Auth {

	var $CI = NULL;

	function __construct()
	{
		$this->CI =& get_instance();
		
		// Load additional libraries, helpers, etc.
		$this->CI->load->library('session');
		$this->CI->load->model('model_user','user',TRUE);
		$this->CI->load->model('model_experiments','experiments',TRUE);
	}

	/**
	 *
	 * Process the data from the login form
	 *
	 * @access	public
	 * @param	array	array with 2 values, username and password (in that order)
	 * @return	boolean
	 */	
	function process_login($login = NULL)
	{
		// A few safety checks
		// Our array has to be set
		if(!isset($login))
			return FALSE;
			
		//Our array has to have 2 values
		//No more, no less!
		if(count($login) != 2)
			return FALSE;
			
		$userID = $login[0];
		$passw = $login[1];
		
		// Query time
		$sql = "SELECT * FROM user WHERE user_id = ? AND password = SHA1(?)";
		$query = $this->CI->db->query($sql, array($userID,$passw));
		
		if ($query->num_rows() == 1)
		{
		  
			// Our user exists but still need to make sure experiment is active
			$this->CI->user->user_id = $userID;
			$user_info = $this->CI->user->get();
			
			$this->CI->experiments->experiment_code = $user_info->experiment_code;
			$experiment = $this->CI->experiments->get();
			if(isset($experiment[0]->experiment_code)){
				if($experiment[0]->active == '0'){
					return FALSE;
				}
			}
			
			$this->CI->session->set_userdata('active',$user_info->active);
			$this->CI->session->set_userdata('uid', $userID);
			
		  $this->CI->db->insert('currently_logged_in',array('user_id'=>$userID,'time'=>date("Y-m-d H:i:s")));	
			
			return TRUE;
		}
		else 
		{
			// No existing user.
			return FALSE;
		}
	}
	
	/**
	 *
	 * This function redirects users after logging in
	 *
	 * @access	public
	 * @return	void
	 */	
	function redirect()
	{
		if ($this->CI->session->userdata('redirected_from') == FALSE)
		{
			redirect('/increaseintellect');
		} else {
			redirect($this->CI->session->userdata('redirected_from'));
		}
		
	}
	
	/**
	 *
	 * This function restricts users from certain pages.
	 * use restrict(TRUE) if a user can't access a page when logged in
	 *
	 * @access	public
	 * @param	boolean	wether the page is viewable when logged in
	 * @return	void
	 */	
	function restrict($logged_out = FALSE)
	{
		// If the user is logged in and he's trying to access a page
		// he's not allowed to see when logged in,
		// redirect him to the index!
		if ($logged_out && $this->logged_in())
		{
			redirect('/increaseintellect');
		}
		
		// If the user isn' logged in and he's trying to access a page
		// he's not allowed to see when logged out,
		// redirect him to the login page!
		if ( ! $logged_out && ! $this->logged_in()) 
		{
			$this->CI->session->set_userdata('redirected_from', $this->CI->uri->uri_string()); // We'll use this in our redirect method.
			redirect('/increaseintellect/login');
		}
	}
	
	/**
	 *
	 * Checks if a user is logged in
	 *
	 * @access	public
	 * @return	boolean
	 */	
	function logged_in()
	{
		if ($this->CI->session->userdata('uid') == FALSE)
		{
			return FALSE;
		}
		else 
		{
			return TRUE;
		}
	}
	
	/**
	 *
	 * Logs user out by destroying the session.
	 *
	 * @access	public
	 * @return	TRUE
	 */	
	function logout() 
	{
		$this->CI->db->delete('currently_logged_in',array('user_id'=>$this->CI->session->userdata('uid')));
		$this->CI->session->sess_destroy();
		
		return TRUE;
	}
	
}

/* End of file: Auth.php */
/* Location: ./system/application/libraries/Auth.php */
