<?php
include_once ('misc_func.php');

session_start();
include "countries.php";
include "states.php";
include "map_icons.php";


if(get_option('wp_aff_use_recaptcha'))
{
    $publickey = get_option('wp_aff_captcha_public_key');
    $privatekey = get_option('wp_aff_captcha_private_key');
}
if(isset($_POST['doRegister']))
{ 
	function filter($arr) {
	    return array_map('mysql_real_escape_string', $arr);
	}
	$_POST = filter($_POST);

    if (get_option('wp_aff_use_recaptcha'))
    {
        if (!function_exists('_recaptcha_qsencode'))
        {
            require_once('recaptchalib.php');
        }
        $resp = recaptcha_check_answer ($privatekey,
                                          $_SERVER["REMOTE_ADDR"],
                                          $_POST["recaptcha_challenge_field"],
                                          $_POST["recaptcha_response_field"]);
    
        if (!$resp->is_valid) {
            die ("<p class='error message'>Image Verification failed! Go <a href='register.php'>back</a> and try again.</p>" .
                 "(reCAPTCHA said: " . $resp->error . ")");
        }
    }

//=================
$user_ip = $_SERVER['REMOTE_ADDR'];
$md5pass = md5($_POST['pwd']);
$host  = $_SERVER['HTTP_HOST'];
$host_upper = strtoupper($host);
$path   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$activ_code = rand(1000,9999);
$aemail = mysql_real_escape_string($_POST['aemail']);
$user_name = mysql_real_escape_string($_POST['user_name']);
//============

	$userid = mysql_real_escape_string($_POST['user_name']);
    global $wpdb;
    $affiliates_table_name = WP_AFF_AFFILIATES_TABLE;
    $result = $wpdb->get_results("SELECT refid FROM $affiliates_table_name where refid='$userid'", OBJECT);

    if($result)
	{
		$err = urlencode("<p class='error message'>ERROR: The username already exists. Please try again with different username and email.</a>");
		header("Location: register.php?msg=$err");
		exit();
	}
	
	if(isset($_POST['amapmarker']) && $_POST['amapmarker'] == 'Yes')
	{
    		//echo "Map marker Option=yes";
		    // Add the affiliate city marker to the correct map for his state or country
        	$result = add_affiliate_map_marker($_POST['acompany'],$_POST['astreet'],$_POST['atown'],$_POST['astate'],$_POST['apostcode'],$_POST['acountry'],$_POST['awebsite'], $_POST['marker_image'], $_POST['marker_icon']);
       
        	if($result)
		{
			//print ( "ERROR: result=$result");
			$err = urlencode("<p class='error message'>ERROR: $result .</a>");
			header("Location: register.php?msg=$err");
			exit();
		}
        	//print ( "after call to add_affiliate_map_marker: result=$result");
	}   
       
        
        // save and send notification email
        // check if referred by another affiliate
        $referrer = "";
        if (!empty($_SESSION['ap_id']))
        {
            $referrer = $_SESSION['ap_id'];
        }
        else if (isset($_COOKIE['ap_id']))
        {
            $referrer = $_COOKIE['ap_id'];
        }

		$commission_level = get_option('wp_aff_commission_level');
		$date = (date ("Y-m-d"));

        global $wpdb;
        $affiliates_table_name = WP_AFF_AFFILIATES_TABLE;
        $updatedb = "INSERT INTO $affiliates_table_name VALUES ('".$_POST['user_name']."', '".$_POST['pwd']."', '".$_POST['acompany']."', '".$_POST['atitle']."', '".$_POST['afirstname']."', '".$_POST['alastname']."', '".$_POST['awebsite']."', '".$_POST['aemail']."', '".$_POST['apayable']."', '".$_POST['astreet']."', '".$_POST['atown']."', '".$_POST['astate']."', '".$_POST['apostcode']."', '".$_POST['acountry']."', '".$_POST['aphone']."', '".$_POST['afax']."', '$date','".$_POST['paypal_email']."','".$commission_level."','".$referrer."')";
        $results = $wpdb->query($updatedb);

        $affiliate_login_url = get_option('wp_aff_login_url');

        $email_subj = get_option('wp_aff_signup_email_subject');			
        $body_sign_up = get_option('wp_aff_signup_email_body');	
        $from_email_address = get_option('wp_aff_senders_email_address');
        $headers = 'From: '.$from_email_address . "\r\n";	       		
        
        $tags1 = array("{user_name}","{email}","{password}","{login_url}");			
        $vals1 = array($user_name,$aemail,$_POST[pwd],$affiliate_login_url);			        
        $aemailbody = str_replace($tags1,$vals1,$body_sign_up);		

        if (get_option('wp_aff_admin_notification'))
        {
             $admin_email_subj = "New affiliate sign up notification";
             wp_mail($from_email_address, $admin_email_subj, $aemailbody);
        }
       
        wp_mail($_POST['aemail'], $email_subj, $aemailbody, $headers);
        
        
        header("Location: thankyou.php");
}
include "header.php"; ?>

<script language="JavaScript" type="text/javascript" src="js/jquery-1.3.2.min.js"></script>
<script language="JavaScript" type="text/javascript" src="js/jquery.validate.js"></script>
  <script>
  $(document).ready(function(){
    $.validator.addMethod("username", function(value, element) {
        return this.optional(element) || /^[a-z0-9\_]+$/i.test(value);
    }, "Username must contain only letters, numbers, or underscore.");

    $("#regForm").validate();
  });
  </script>

    <p>
	<?php if (isset($_GET['done'])) {
	  echo "<h2 class='title'>Thank you</h2> <p class='message'>Your registration is now complete and you can <a style='color:#CC0000;' href=\"login.php\">login here</a></p>";
	  exit();
	  }
	?>
    </p>

    <h3 class="title"><?php echo AFF_SIGNUP_PAGE_TITLE; ?></h3>
    <p><?php echo AFF_SIGNUP_PAGE_MESSAGE;?></p>

	 <?php
      if (isset($_GET['msg'])) {
	  $msg = mysql_real_escape_string($_GET['msg']);
	  echo "<div class=\"msg\">$msg</div>";
	  }
	  if (isset($_GET['done'])) {
	  echo "<h2 class='title'>Thank you</h2> <p class='message'>Your registration is now complete and you can <a style='color:#CC0000;' href=\"login.php\">login here</a></p>";
	  exit();
	  } ?>

    <!-- Start Registration Form -->
      <form action="register.php" method="post" name="regForm" id="regForm" >
	
        <table width="95%" border="0" cellpadding="0" cellspacing="0" class="forms" >
          <colgroup width="150" align="left"></colgroup>
  	  <colgroup width="150" align="left"></colgroup>
          <tr> 
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"><?php echo AFF_TITLE; ?>:</font></b></td>
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"> 
              <select name="atitle">
                <option value="Mr">Mr</option>
                <option value="Mrs">Mrs</option>
                <option value="Miss">Miss</option>
                <option value="Ms">Ms</option>
                <option value="Dr">Dr</option>
              </select>
              </font></b></td>
          </tr>
          <tr> 
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"><?php echo AFF_FIRST_NAME; ?>: *</font></b></td>
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"> 
              <input type="text" name="afirstname" size=20 value="<?php echo $_POST['afirstname']; ?>" class="required">
              </font></b></td>
          </tr>
          <tr> 
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"><?php echo AFF_LAST_NAME; ?>: *</font></b></td>
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"> 
              <input type="text" name="alastname" size=20 value="<?php echo $_POST['alastname']; ?>" class="required">
              </font></b></td>
          </tr>
          <tr> 
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"><?php echo AFF_COMPANY; ?>:</font></b></td>
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"> 
              <input type="text" name="acompany" size=20 value="<?php echo $_POST['acompany']; ?>">
              </font></b></td>
          </tr>
           <tr> 
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"><?php echo AFF_WEBSITE; ?>:</font></b></td>
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"> 
              <input type="text" name="awebsite" size=20 value="<?php echo $_POST['awebsite']; ?>">
              </font></b></td>
          </tr>
          <tr> 
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"><?php echo AFF_EMAIL; ?>: *</font></b></td>
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"> 
              <input type="text" name="aemail" size=20 value="<?php echo $_POST['aemail']; ?>" class="required email">
              </font></b></td>
          </tr>
          <tr>
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"><?php echo AFF_PAYPAL_EMAIL; ?>: </font></b></td>
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"> 
              <input type="text" name="paypal_email" size=20 value="<?php echo $_POST['paypal_email']; ?>">
              </font></b></td>
          </tr>
          <tr> 
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"><?php echo AFF_ADDRESS; ?>:</font></b></td>
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"> 
              <input type="text" name="astreet" size=20 value="<?php echo $_POST['astreet']; ?>">
              </font></b></td>
          </tr>
          <tr> 
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"><?php echo AFF_TOWN; ?>:</font></b></td>
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"> 
              <input type="text" name="atown" size=20 value="<?php echo $_POST['atown']; ?>">
              </font></b></td>
          </tr>
          <tr> 
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"><?php echo AFF_STATE; ?>:</font></b></td>
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"> 
              <select name=astate class=dropdown value="CA" >
<?php
            foreach($GLOBALS['states'] as $key => $state)
                print '<option value="'.$state.'" '.($_POST['astate'] == $state ? 'selected' : '').'>'.$state.'</option>'."\n";
?>
              </select>
              </font></b></td>
          </tr>

          <tr> 
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"><?php echo AFF_ZIP; ?>:</font></b></td>
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"> 
              <input type="text" name="apostcode" size=20 value="<?php echo $_POST['apostcode']; ?>">
              </font></b></td>
          </tr>
          <tr> 
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"><?php echo AFF_COUNTRY; ?>:</font></b></td>
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"> 
             <select name=acountry class=dropdown value="USA" >
<?php
            foreach($GLOBALS['countries'] as $key => $country)
                print '<option value="'.$key.'" '.($_POST['acountry'] == $key ? 'selected' : '').'>'.$country.'</option>'."\n";
?>
              </select>
              </font></b></td>
          </tr>

          <tr> 
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"><?php echo AFF_PHONE; ?>:</font></b></td>
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"> 
              <input type="text" name="aphone" size=20 value="<?php echo $_POST['aphone']; ?>">
              </font></b></td>
          </tr>
          <tr> 
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"><?php echo AFF_FAX; ?>:</font></b></td>
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"> 
              <input type="text" name="afax" size=20 value="<?php echo $_POST['afax']; ?>">
              </font></b></td>
          </tr>
		
		  <tr> 
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
	
          <tr>
            <td colspan="2">&nbsp;</td>
          </tr>
          <tr> 
            <td colspan="2"><h4><strong><?php echo "Map Options"; ?></strong></h4></td>
          </tr>
          <tr> 

	    <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"><?php echo "Include Link on Maps"; ?>:</font></b></td>
            <td><input type="checkbox" name="amapmarker" margin-left:0px value="Yes" align="left" checked="checked" >  </td>
	   </tr>
           <tr>
	    <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"><?php echo "Map Marker Image"; ?>:</font></b></td>
            <td><input name="marker_image" type="text" id="marker_image" > 
	   </tr>
	   <tr>
	    <td>&nbsp;</td>
	    <td><a href="../../../../wp-admin/media-upload.php" target="_blank" class="add-new-h2"><?php echo esc_html_x('Put the address of your image here, or use the link to upload one. (this can be done later if you do not have it)', 'file'); ?></a>
			</font></b></td>
		</tr>
        <tr> 
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"><?php echo "Map Marker Icon"; ?>:</font></b></td>
            <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"> 
              <select name=marker_icon class=dropdown value="red-dot" >
<?php
            foreach($GLOBALS['icons'] as $key => $icon)
                print '<option value="'.$key.'" '.($_POST['marker_icon'] == $key ? 'selected' : '').'>'.$icon.'</option>'."\n";
?>
              </select>
              </font></b></td>
          </tr>

		 
          <tr> 
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>


          <tr>
            <td colspan="2">&nbsp;</td>
          </tr>
          <tr> 
            <td colspan="2"><h4><strong><?php echo AFF_LOGIN_DETAILS; ?></strong></h4></td>
          </tr>
          <tr> 
            <td><?php echo AFF_USERNAME; ?><span class="required"><font color="#CC0000">*</font></span></td>
            <td><input name="user_name" type="text" id="user_name" class="required username" minlength="5" > 
              <input name="btnAvailable" type="button" class="button" id="btnAvailable" 
			  onclick='$("#checkid").html("<?php echo AFF_SI_PLEASE_WAIT; ?>"); $.get("checkuser.php",{ cmd: "check", user: $("#user_name").val() } ,function(data){  $("#checkid").html(data); });'
			  value="<?php echo AFF_AVAILABILITY_BUTTON_LABEL; ?>"> 
			    <span style="color:red; font: bold 12px verdana; " id="checkid" ></span> 
            </td>
          </tr>
          <tr>
            <td><?php echo AFF_PASSWORD; ?><span class="required"><font color="#CC0000">*</font></span> 
            </td>
            <td><input name="pwd" type="password" class="required password" minlength="5" id="pwd"> 
              <span class="example">** <?php echo AFF_MIN_PASS_LENGTH; ?>..</span></td>
          </tr>
          <tr> 
            <td><?php echo AFF_RETYPE_PASSWORD; ?><span class="required"><font color="#CC0000">*</font></span> 
            </td>
            <td><input name="pwd2"  id="pwd2" class="required password" type="password" minlength="5" equalto="#pwd"></td>
          </tr>
          <tr> 
            <td colspan="2">&nbsp;</td>
          </tr>
          <?php
          if (get_option('wp_aff_use_recaptcha'))
          {
              echo '<tr>
                  <td width="22%"><strong>'.AFF_IMAGE_VERIFICATION.' </strong></td>
                  <td width="78%">';
		        if (!function_exists('_recaptcha_qsencode'))
		        {
		            require_once('recaptchalib.php');
		        }
              echo recaptcha_get_html($publickey);
              echo '</td></tr>';
          }
          ?>

        </table>
	
	<p align="center">
          <input name="doRegister" type="submit" id="doRegister" class="button" value="<?php echo AFF_SIGN_UP_BUTTON_LABEL; ?>">
        </p>
        <p align="center">
        <?php
        $terms_url = get_option('wp_aff_terms_url');
        if (!empty($terms_url))
        {
            echo AFF_YOU_AGREE_TO.' <strong><a href="'.$terms_url.'" target="_blank">'.AFF_TERMS_AND_COND.'</a></strong>';
        }
        ?>
        </p>
      </form>

      <p>&nbsp;</p>
      <p><?php echo AFF_ALREADY_MEMBER; ?>? <img src="images/login.png" /> <a style="color:#CC0000" href=login.php><?php echo AFF_LOGIN_HERE; ?></a></p>

<?php include "footer.php"; ?>