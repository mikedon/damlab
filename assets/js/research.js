$(document).ready(function(){
  
  function isValidEmail(str){
    return (str.lastIndexOf(".") > 2) && (str.indexOf("@") > 0) && (str.lastIndexOf(".") > (str.indexOf("@")+1)) && (str.indexOf("@") == str.lastIndexOf("@"));
  }

  //General Page JS
	$(".panel_body").hide();
  $(".panel_head").click(function(){
		$(this).next(".panel_body").slideToggle("300",function(){
			if($(this).is(":visible")){
        //down
        $(this).prev().css('background-image','url('+CI.base_url+'assets/images/bullet_toggle_minus.png)');
			}else{
       //right
       $(this).prev().css('background-image','url('+CI.base_url+'assets/images/bullet_toggle_plus.png)');
			}
		});
	});
	
	
  //Create Researcher
  $('#researcherForm').submit(function(){
    var jsonData = new Object();
    jsonData.experimenter_code = $('#researcher_experimenter_code').val();
    jsonData.password = $('#researcher_password').val();
    jsonData.permissions = $('#researcher_permission').val();
		jsonData.email = $("#researcher_email").val();
    if(!isValidEmail(jsonData.email)){
    	alert("Invalid email address");
    	return false;
    }       
    var jsonString = JSON.stringify(jsonData);
    $.post(CI.base_url + '/index.php/research/addResearcher',{
    	data:jsonString
    },function(resp){
        alert(resp);
    });
    return false;
  });
  
  //Create Experiment
  $('.parameters div').hide();
	$('#assessments_before').attr('disabled','disabled');
  var num_training_clicked = 0;
	
	//Click on experiment to add permission
  var onTaskClickHandler = function(){
	var groupNum = $(this).attr('id').substring(0,1);
    var reggie = /training/;
    if($(this).is(":checked")){
      if(reggie.test(this.id)){
        num_training_clicked++;
        $('#num_of_sessions').attr('disabled','');
		$('#assessments_before').attr('disabled','');
      }
      $("#" + $(this).attr('id') + "_params").show();
    }else{
			
      if(reggie.test(this.id) && --num_training_clicked == 0){
        $('#num_of_sessions').attr('disabled','disabled');
				$('#assessments_before').attr('disabled','disabled');
      }
      $("#" + $(this).attr('id') + "_params").hide();
    }
  }
  $(".parameters input[type='checkbox']").click(onTaskClickHandler);
  
  /*$('#numParticipants').selectToUISlider({
    labels : 2
    });
  */
  $('#updateExperiment').hide();
  
  new AjaxUpload('#upload_button', {
		action: CI.base_url + '/index.php/research/upload_consent_form',
		onComplete: function(file,response){
			$('#upload_button').before("<li><input type='radio' name='consent' value='"+file+"'/>"+file);
		}
	});
  
  $('#password div:not(#random)').hide();
	$("#password li input[value='random']").attr('checked',true);
	$("#password input[type='radio']").click(function(){
		$('#'+$(this).val()).show();
		$('#password div').not('#'+$(this).val()).hide();
	});
        
  $('#password_popup div:not(#random_popup)').hide();
	$("#password_popup li input[value='random_popup']").attr('checked',true);
	$("#password_popup input[type='radio']").click(function(){
		$('#'+$(this).val()).show();
		$('#password_popup div').not('#'+$(this).val()).hide();
	});
  
	$("#consentForms li input[value='noconsent']").attr('checked',true);
  
  //Changes the permission group selector depending on how many groups were selected
  $('#numGroups').change(function(){
	var numGroups = $('#numGroups').val();
	$("#groupNumberPermissions > option[value='1']").siblings().remove();
	
	//Add the new options to select element
	for(i=2;i<=numGroups;i++){
	  $('#groupNumberPermissions').append('<option value='+i+'>'+i+'</option>');
	}
	//When user selects a group id then insert a new table if one doesnt already exist;
	//Note: only show one table at a time and create table when user goes to
	//assign permissions...takes too long otherwise...
	$('#groupNumberPermissions').change(function(){
	  var selectedGroup = $('#groupNumberPermissions').val();
	  var newTable;
	  var currTable = $('table.parameters:visible');
	  if($('table#parameters'+selectedGroup).length == 0){
		newTable = $('table#parameters1').clone();
		
		//change id of new table
		newTable.attr('id','parameters'+selectedGroup);
		
		//Insert now so we can access the elements inside the table
		currTable.hide();
		newTable.insertAfter(currTable);
		newTable.show();
		
  	    //change input for tesk selection
		$("#parameters"+selectedGroup + " input[type='checkbox']").each(function(){
		  var oldID = $(this).attr('id');
		  var newID = oldID.replace(/\d/,selectedGroup);
		  var oldName = $(this).attr('name');
		  var newName = oldName.replace(/\d/,selectedGroup);
		  var oldLabel = $(this).next('label').attr('for');
		  var newLabel = oldLabel.replace(/\d/,selectedGroup);
		  //alert("old id: " + oldID + "..new id: " + newID + "..oldName: " + oldName + "..oldLabel: " + oldLabel + "..newLabel: " + newLabel);
		  $(this).attr('id',newID);
		  $(this).attr('name',newName);
		  $(this).next('label').attr('for',newLabel);
		  $(this).attr('checked',false);
		});//end each
		
		//change selects for parameter selections
		$("#parameters"+selectedGroup + " div").each(function(){
		  var oldID = $(this).attr('id');
		  var newID = oldID.replace(/\d/,selectedGroup);
		  $(this).attr('id',newID);
		  $(this).hide();
		  $(this).children('select').each(function(){
			var oldSelectID = $(this).attr('id');
			var oldSelectName = $(this).attr('name');
			var newSelectID = oldSelectID.replace(/\d/,selectedGroup);
			var newSelectName = oldSelectName.replace(/\d/,selectedGroup);
			//alert("old select id: " + oldSelectID + "..new select id: " + newSelectID + "..oldSelectName: " + oldSelectName + "..newSelectName: " + newSelectName );
			$(this).attr('id',newSelectID);
			$(this).attr('name',newSelectName);
		  });//end each
		  
		  $(".parameters input[type='checkbox']").click(onTaskClickHandler);
		});//end onChange event handler
		
	  }else{
		newTable = $('table#parameters'+selectedGroup);
		currTable.hide();
		newTable.show();
	  }
	  //newTable.hide();
	});
  });
  
  $('#experimentForm').submit(function(){
    var jsonData = new Object();
    jsonData.experiment_code = $('#experimentCode').val();
    if(jsonData.experiment_code == ''){
      alert("Experiment Code is empty.");
      return false;
    }
    //jsonData.num_participants = $('#numParticipants').val();
	jsonData.num_groups = $('#numGroups').val();
	jsonData.users_per_group = $('#usersPerGroup').val();
    
    var task_params = new Array();
    var permissions = new Array();
    
    $(".parameters input[type='checkbox']:checked").each(function(){
        permissions.push($(this).attr('task_id'))
        var reggie = /training/;
        if(reggie.test($(this).attr('task_type'))){
          jsonData.num_of_sessions = $('#num_of_sessions').val();
          var task = $(this).attr('task_type') + "_" + $(this).attr('task_id');
          var task_info = {
            'task' : task,
            'time_limit' : $("#time-"+$(this).attr('task_id')).val() * 60
          }
          task_params.push(task_info);
        }
    });
    jsonData.permissions = permissions;
    jsonData.task_params = task_params;
		switch($("input:radio[name='task_ordering']:checked").val()){
			case 'assessmentsUnlimited':
				jsonData.ordering = '2';
				break;
			case 'assessmentsBefore':
				jsonData.ordering = '1';
				break;
		}
    jsonData.consent = $("input:radio[name='consent']:checked").val();
    jsonData.password_type = $("input:radio[name='password']:checked").val();
    switch(jsonData.password_type){
    	case 'random':
    		jsonData.email = $("input:text[name='researchEmailAddress']").val();
    		if(!isValidEmail(jsonData.email)){
    			alert("Invalid email address");
    			return false;
    		}
    		break;
    	case 'temporary':
    		jsonData.password = $("input:text[name='temporaryPassword']").val();
    		break;
    	case 'shared':
    		jsonData.password = $("input:text[name='sharedPassword']").val();
    		break;
    }
  
    var jsonString = JSON.stringify(jsonData);
    $.post(CI.base_url + 'index.php/research/createExperiment',{
    	data:jsonString
    },function(data){
        var resp = eval('(' + data + ')');
        if(resp.error != undefined){
            alert(resp.error);
        }else{
          var html = "<tr><td style='text-align:left'>"+
          "<a class='addLink' style='color: #BA2A0D;font-size:x-small;cursor:pointer'>"+
          "<img src='"+CI.base_url+"assets/images/add.png'>"+"</a>&nbsp;&nbsp;"+
          resp.experiment_code+"</td><td>"+
          resp.num_participants+"</td><td>"+
          resp.consent+"</td>";
          $('#prevExperiments th[task_id]').each(function(){
            if($.inArray($(this).attr('task_id'),resp.permissions) > -1){
              html += "<td><img src='"+CI.base_url+"assets/images/accept.png'></td>";
            }else{
              html += "<td><img src='"+CI.base_url+"assets/images/cross-circle.png'></td>";
            }
          });
          html += "</tr>";
          $('#prevExperiments').append(html);
          alert(resp.result);
        } 
      }
    ,"text");
    return false;
  });
	
	//Add Participants
   var boxy = new Boxy('#updateExperiment',{
    title: "this is a title",
    modal: true,
    show: false,
    afterHide: function(){
                        
      }
    });
    var exp_code = "";
    $('.addLink').click(function(){
      boxy.setTitle("Add Participants to Experiment: "+ $(this).next().text());
      exp_code = $(this).next().next().text();
      boxy.show();
    });
    
    $('#updateExperiment').submit(function(){
      var jsonData = new Object();
      jsonData.new_users = $('#new_end_range').val();
      jsonData.experiment_code = exp_code;
      jsonData.password_type = $("input:radio[name='password_popup']:checked").val();
      switch(jsonData.password_type){
      	case 'random_popup':
          jsonData.email = $("input:text[name='researchEmailAddress_popup']").val();
          if(!isValidEmail(jsonData.email)){
          	alert("Invalid email address");
          	return false;
          }
          break;
			case 'temporary_popup':
				jsonData.password = $("input:text[name='temporaryPassword_popup']").val();
				break;
			case 'shared_popup':
				jsonData.password = $("input:text[name='sharedPassword_popup']").val();
				break;
      }
      var jsonString = JSON.stringify(jsonData);
      $.post(CI.base_url + 'index.php/research/updateExperiment',{
        data:jsonString
      },function(data){
        if(data.end_range  != "-1"){
          $("td[experiment='"+exp_code+"']").text(data.num_participants);
        }
        alert(data.msg);
      },"json");
        boxy.hide();
        return false;                
    });
  
	//End Experiment

	$(".deleteLink").click(function(){
		var jsonData = new Object();
		jsonData.experiment = $(this).next().text();
		var jsonString = JSON.stringify(jsonData);
		var row = $(this).parents('tr');
		
	  Boxy.confirm("Are you sure you want to end the experiment: " + $(this).next().text() + "?", function() {
			$.post(CI.base_url + 'index.php/research/end_experiment',{
				data: jsonString
			},function(data){
				row.fadeTo(500,.5);
				row.find('a').unbind('click');
				row.find('a').css('cursor','auto');
				row.find('a').click(function(){
					return false;
				});
				alert("Experiment Ended.");
			});
			return;
		  },
			{
				title: 'End Experiment'
		  }
		);
    return false;

	});
		
  //Download Experiment Data
  $('#selectAll').click(function(){
    if($(this).attr('checked') == true){
      $('#download tr > td:first-child input').attr('checked',true);
    }else{
      $('#download tr > td:first-child input').attr('checked',false);
    }
  });
	$('#downloadData').submit( function(){
    
    var jsonData = new Object();
                
    var codes = new Array();
                
    $('#download tr > td:first-child input:checked').each(function(){
      codes.push($(this).val());
    });
    jsonData.codes = codes;
    var jsonString = JSON.stringify(jsonData);
                
    $.post(CI.base_url+'/index.php/research/downloadData',{
    	codes:jsonString
    },function(url){
        var elemIF = document.createElement("iframe");
        elemIF.src = url;
        elemIF.style.display = "none";
        document.body.appendChild(elemIF);
      });
    return false;
  });
  
	//Information link in the download data table
  $('.detailLink').click(function(){
    Boxy.load(CI.base_url + "index.php/utility/get_experiment_info/"+$(this).attr('code'),
      {
        type:"POST",
        modal: true,
        title: "Information"
      });
  });
	
	
	//Disable links when experiment becomes expired
	$('.expired_experiment').fadeTo(.1,.5);
	$('.expired_experiment a').unbind('click');
	$('.expired_experiment a').click(function(){
		return false;
	});
	
});
