<?php
  $CI =& get_instance();
	$CI->load->model('model_user','user',TRUE);
	$CI->user->user_id = $this->session->userdata('uid');
	
?>


<div id="content" class="content">
  <?php if($CI->user->has_permission('god')):?>
	  <div class="panel_head" id="createResearcherHead">Create Researcher</div>
      <div class="panel_body">
		    <form id="researcherForm" action="" method="post">
			    <table class="researchTable" width="100%">
						<th>Experimenter Code (User ID)</th>
						<th>New Researcher's Email</th>
						<th>Password</th>
						<th>Assign Permission</th>
				    <tr>
							<td><input type="text" id="researcher_experimenter_code"/></td>
							<td><input type="text" id="researcher_email"/></td>
				      <td><input type="text" id="researcher_password"/></td>
						  <td>
						    <select id="researcher_permission">
								  <option value="G">Administrative</option>
								  <option value="RTA">Training and Assessment</option>
								  <option value="RA">Assessment</option>
						      <option value="RT">Training</option>
					      </select>
						  </td>
						</tr>
			    </table>
			    <h5>*Note: The researcher will be forced to change password on first log in.</h5>
			    <input type="submit" value="Submit"/>
		    </form>
    </div>
	  <div class="panel_head" id="createExperimentHead">Access User Account</div>
	  <div class="panel_body">
			<form id="accessUserAccount" action="<?php echo base_url();?>index.php/user/changeUserPortal" method="post">
			<label for="userToLogInAs">Enter the user you would like to log in as: </label>
			<input type="text" id="userToLogInAs" name="userToLogInAs"/>
			
			<h5>*Note: To log back in as yourself, log out normally and then log back in.</h5>
			<input type="submit" value="Log In"/>
			</form>
		</div>
  <?php endif;?>
	<div class="panel_head" id="createExperimentHead">Create New Experiment</div>
	<div align="center" class="panel_body">
    <h4>Current Experiments</h4>
    <p style="color:#515151;font-size:small">
        <img src="<?php echo base_url()?>assets/images/add.png"/>  -- Add Participants<br>
				<img src="<?php echo base_url()?>assets/images/delete.png"/> -- End Experiment
    </p>
		<table width="100%" id="prevExperiments">
			<th>Experiment Code</th>
      <th># of Participants</th>
      <th>Consent Form</th>
      <?php foreach($tasks as $name => $info):?>
      <?php $name = explode("_",$name);?>
        <th task_id="<?php echo $info->id?>"><?php echo $name[0]?></th>
      <?php endforeach;?>
      <colgroup>
        <col width="15%"/>
        <col width="5%"/>
        <col width="20%"/>
        <?php foreach($tasks as $t):?>
          <col width="10%"/>
        <?php endforeach;?>
      </colgroup>
			<?php foreach($exp_codes as $code=>$info):?>
			<?php if($info['active'] == '0'):?>
				<tr class="expired_experiment">
				<td style="text-align:left">
				  <a class="addLink"><img title="Add Participants" src="<?php echo base_url()?>assets/images/add.png"></a>
					<a class="deleteLink"><img title="End Experiment" src="<?php echo base_url()?>assets/images/delete.png"></a> 
			<?php else:?>
				<tr>
				<td style="text-align:left">
				  <a style="cursor: pointer" class="addLink"><img title="Add Participants" src="<?php echo base_url()?>assets/images/add.png"></a>
					<a style="cursor: pointer" class="deleteLink"><img title="End Experiment" src="<?php echo base_url()?>assets/images/delete.png"></a> 
			<?php endif;?>
					&nbsp;&nbsp;<label><?php print $code?></label>
					
				</td>
        <td experiment="<?php echo $code?>">
            <?php print $info['num_participants']?>
        </td>
        <td>
				    <?php echo $info['consent']?>
				</td>
        <?php foreach($tasks as $t):?>
        <td>
          <?php if(in_array($t->id,$info['permissions'])):?>
            <img src="<?php echo base_url()?>assets/images/accept.png"/>
          <?php else:?>
            <img src="<?php echo base_url()?>assets/images/cross-circle.png"/>
          <?php endif;?>
        </td>
        <?php endforeach;?>
			</tr>
			<?php endforeach;?>
		</table>
    <?php echo form_open('',array('id'=>'updateExperiment','style'=>'text-align:center;width:400px;font-size:small'))?>
      <br>
      <label># of Users to Add</label>
      <br>
			<select style="margin-bottom:10px" name="end_range" id="new_end_range">
      <?php for($i=1;$i<101;$i++):?>
        <option value="<?php echo $i?>"><?php echo $i?></option>
      <?php endfor;?>
      </select>
      <br><br>
      <div id="password_popup">
		    <ul>
				  <li><input type="radio" name="password_popup" value="random_popup" id="randomRadio_popup" checked/><label for="randomRadio">Random</label></li>
				  <li><input type="radio" name="password_popup" value="shared_popup" id="sharedRadio_popup"/><label for="sharedRadio">Shared</label></li>
				  <li><input type="radio" name="password_popup" value="temporary_popup" id="temporaryRadio_popup"/><label for="temporaryRadio">Temporary</label></li>
		    </ul>
		    <div id="random_popup">
		      <p>This will create random passwords for each participant.  The list will be emailed to you.</p>
		      <p>
		      <input type="text" name="researchEmailAddress_popup"/>Email Address
		      </p>
		    </div>
		    <div id="shared_popup">
		      <p>This will set every participants password to be the one specified in the box below.</p>
		      <p>
				    <input type="text" name="sharedPassword_popup"/>Password
		      </p>
		    </div>
		    <div id="temporary_popup">
		      <p>This will set a default password that the user will change on first log in.</p>
          <p><input type="text" name="temporaryPassword_popup"/>Password</p>
        </div>
		  </div>
      <input type="submit" value="Update"/>
    </form>
    <br>
		<?php echo form_open('research/createExperiment',array('id'=>'experimentForm'))?>
		  <ul>
			  <li><h3>Step 1: Create Experiment Code</h3></li>
			  <table border="0">
				  <th>Experimenter Code</th>
				  <th>Experiment Code</th>
				  <tr>
					  <td><?php echo get_instance()->session->userdata('uid')?></td>
					  <td><input type="text" id="experimentCode"/></td>
					  <td>(This will be used to create the participant's User ID's)</td>
				  </tr>
			  </table>
			  <hr>
			  <li style="margin-bottom:10px"><h3>Step 2: Set User Groups</h3></li>
          <!--<select style="margin-bottom:10px" name="numParticipants" id="numParticipants">
            <?php for($i=1;$i<101;$i++):?>
              <option value="<?php echo $i?>"><?php echo $i?></option>
            <?php endfor;?>
          </select>-->
		  <label for="numGroups">How many groups?</label>
		  <select name="numGroups" id="numGroups">
			<?php for($i=1;$i<101;$i++):?>
			  <option value="<?php echo $i?>"><?php echo $i?></option>
			<?php endfor;?>
		  </select>
		  <label for="usersPerGroup">How many users per group?</label>
		  <select name="usersPerGroup" id="usersPerGroup">
			<?php for($i=1;$i<101;$i++):?>
			  <option value="<?php echo $i?>"><?php echo $i?></option>
			<?php endfor;?>
		  </select>
			  <hr>
			  <li><h3>Step 3: Assign Permissions and Parameters</h3></li>
			
			<label for="groupNumberPermissions">Parameters for group number: </label>
			<select name="groupNumberPermissions" id="groupNumberPermissions">
			  <option value="1">1</option>
			</select>
		
		<div id="parameter_tables">
        <table class='parameters' id="parameters1" cellpadding="10" border="0">
          <colgroup>
            <col width="33%"/>
            <col width="33%"/>
            <col width="33%"/>
          </colgroup>
          <th>Tasks</th>
          <th>Parameters</th>
          <th>Session Info (if applicable)</th>
            <tr>
              <td>
                <ul>
                <?php foreach($tasks as $name => $info):?>
                <li>
                  <input type="checkbox" name="1<?php echo $name?>" id="1<?php echo $name . "-" . $info->type?>" task_id="<?php echo $info->id?>" task_type="<?php echo $info->type?>"/>
                  <label for="1<?php echo $name . "-" . $info->type?>">
                  <?php $name = explode("_",$name);?>
                  
                  <?php echo $name[0]?>
                  <?php if(preg_match('/training/i',$info->type)):?>
                    <?php echo '(training)'?>
                  <?php else:?>
                    <?php echo '(assessment)'?>
                  <?php endif;?>
                  </label>
                </li>
                <?php endforeach;?>
                </ul>
              </td>
              <td>
                <?php foreach($tasks as $name => $info):?>
                <div id="1<?php echo $name . '-' . $info->type . '_params'?>">
                  <?php $name = explode("_",$name);?>
                  <h5><?php echo $name[0]?><?php if(preg_match('/training/i',$info->type)) echo '(training)'?></h5>
                  <?php if(preg_match('/training/i',$info->type)):?>
                    <select name="1<?php echo $name[0] . "-" . $info->type . '_time'?>" id="1<?php echo 'time-'.$info->id?>">
                    <?php for($i = 1; $i < 25; $i++):?>
                      <option value="<?php echo $i?>"><?php echo $i?> min</option>
                    <?php endfor;?>
                    </select>&nbsp;&nbsp;Time Limit
                  <?php endif;?>
                  <hr>
                </div>
                <?php endforeach;?>
              </td>
              <td>
                <h5>Number of sessions</h5>
                <select disabled='true' name="1num_of_sessions" id="1num_of_sessions">
                  <?php for($i = 1; $i < 201; $i++):?>
                    <option value="<?php echo $i?>"><?php echo $i?></option>
                  <?php endfor;?>
                </select>
              </td>
            </tr>
          </table>
		</div>
			    <hr>
				<li><h3>Step 4: Task Ordering</h3></li>
					<input id="assessments_before" type="radio" name="task_ordering" value="assessmentsBefore"/><label for="assessments_before"><span class="task_ordering_title">Selected assessments are taken prior to training.</span></label><br><span class="task_ordering_desc">Training tasks are disabled until assessments are completed. Assessments are then disabled until after completing training.</span><br><br>
					<input id="unlimited_assessments" type="radio" name="task_ordering" value="assessmentsUnlimited" checked/><label for="unlimited_assessments"><span class="task_ordering_title">Unlimited assessments.</span></label><br><span class="task_ordering_desc">Subject can complete an assessment whenever they wish.</span>
				<hr>
			  <li><h3>Step 5: Consent</h3></li>
			  <ul id="consentForms">
				<?php if(isset($consent_forms)):?>
				  <?php foreach($consent_forms as $form):?>
				    <li><input type="radio" name="consent" value="<?php print $form?>"/><?php print $form?></li>
				  <?php endforeach;?>
				<?php endif;?>
				  <li><input type="radio" name="consent" value="noconsent"/>No Consent</li>
				  <li><div id="upload_button" class="upload_button" style="background-color:gray;width:133px;text-align:center">Upload</div></li>
			  </ul>
			  <hr>
			  <li><h3>Step 6: Password</h3></li>

			  <div id="password">
				  <ul>
					  <li><input type="radio" name="password" value="random" id="randomRadio" checked/><label for="randomRadio">Random</label></li>
					  <li><input type="radio" name="password" value="shared" id="sharedRadio"/><label for="sharedRadio">Shared</label></li>
					  <li><input type="radio" name="password" value="temporary" id="temporaryRadio"/><label for="temporaryRadio">Temporary</label></li>
				  </ul>
				  <div id="random">
					  <p>This will create random passwords for each participant.  The list will be emailed to you.</p>
					  <p><input type="text" name="researchEmailAddress"/>Email Address</p>
				  </div>
				  <div id="shared">
					  <p>This will set every participants password to be the one specified in the box below.</p>
					  <p><input type="text" name="sharedPassword"/>Password</p>
				  </div>
				  <div id="temporary">
					  <p>This will set a default password that the user will change on first log in.</p>
					  <input type="text" name="temporaryPassword"/>Password
				  </div>
			  </div>
			  <hr>
		  </ul>
		  <input type="submit" value="Create Experiment"/>
		</form>
  </div>
	<div class="panel_head">Get Task Data</div>
  
	<div class="panel_body" id="getTaskData">
		<form id="downloadData" action="<?php echo base_url()?>index.php/research/downloadData">
      <table id="download" width="100%">
        <th>Select</th>
        <th>Experiment Code</th>
        <th># Assessments Completed</th>
        <th># Training Sessions Completed</th>
        <th>Total Participants</th>
        <colgroup>
          <col width="10%"/>
          <col width="40%"/>
          <col width="25%"/>
          <col width="25%"/>
        </colgroup>
				<?php foreach($exp_codes as $code=>$info):?>
          <tr>
            <td>
              <input type="checkbox" value="<?php echo $code?>"/>
            </td>
            <td>
              <?php echo $code?>
              <a title="View Detailed Report" code="<?php echo $code?>" class="detailLink">
              <img src="<?php echo base_url() . 'assets/images/information.png'?>"/></a>
            </td>
            <td>
              <?php if(isset($info['num_assessment_complete'])):?>
                <?php echo $info['num_assessment_complete']?>
              <?php else:?>
                N/A
              <?php endif;?>
            </td>
            <td>
              <?php if(isset($info['num_training_complete'])):?>
                <?php echo $info['num_training_complete']?>
              <?php else:?>
                N/A
              <?php endif;?>
            </td>
            <td>
              <?php echo $info['num_participants']?>  
            </td>
          </tr>
					<?php endforeach;?>
        </table>
        <span style="display:block;text-align:left;font-size:12;margin-left:8px"><label for="selectAll">Select All</label><input type="checkbox" name="selectAll" id="selectAll" value="selectAll"></input></span>
        <br>
				<input type="submit" value="Download Data"/>
		  </form>
	  </div>
  </div>
</body>
</html>