
<style type="text/css">
	p{
		font-size: larger;
	}
	#consentForm {
		padding-top: 5px;
		border: ridge 2px gray;
		background-color: white;
	}
	form{
		text-align:center;
	}
	ul{
		list-style:none;
	}
	form ul{
		text-align:left;
	}

</style>


<div id="content">
	<p>
		Welcome to the HyGene project's Diagnostic Cognitive Testing and
		Training center. Here you will have the opportunity to take several
		measures of cognitive functioning. Each cognitive task will require
		between 5 and 10 minutes for completion. To participate in these tasks
		you must read and complete the informed consent form. The informed
		consent form will allow you access to the rest of the site.
	</p>


	<div>
		Click to see consent form.
	</div>
	<div id="consentForm">
		<embed width="650" height="450" href="<?php echo $consent_form?>" src="<?php echo base_url()?>assets/consent_forms/<?php echo $consent_form?>"/>

		<?php echo form_open('increaseintellect/consent/1')?>
		<ul>
			<li><input type="radio" name="consent" value="accept"> I have read and understand the above informed consent and agree to participate in the study</li>
			<li><input type="radio" name="consent" value="decline" checked > I do NOT agree to participate</li>
		</ul>
		<input type="submit" value="Submit"/>
		</form>
	</div>



</div>
<script type="text/javascript">
	$(document).ready(function(){

		//$('#consentForm').corners("30px");
		$('#consentForm').hide();

		$('div').click(function(){
			$('#consentForm').fadeIn("slow");
			$('#consentForm').prev().hide();
		});

		$('form').submit(function(){
			if( $("input[@name='consent']:checked").val() == 'decline'){
        Boxy.confirm('By not accepting you can not participate in the experiment.  Are you sure you want to do this?',
                     function(){
                        window.location = CI.base_url + "index.php/increaseintellect/consent/0";
                     },{
                        title: "Consent Form",
                        modal: true,
                        afterShow: function(){
                           $('embed').hide(); 
                        },
                        afterHide: function(){
                            $('embed').show();
                        }
                        
                     });      
				return false;
			}else{
				return true;
			}
		});
	});
</script>
</body>
</html>