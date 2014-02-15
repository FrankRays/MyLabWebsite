<?php
/*
Template name: Contact Template
*/

$lab_hard_slug = 'contact';

get_header();
get_sidebar();
?>

<div class = "container">

	<?php the_content(); ?>
	
	<div id = "contactFormWrapper" class = "col-md-7 col-sm-12">
	
		<h3>Send us a message</h3>

	<?php 

	if (!$_POST) {
		contact_form();
	} else {
		$nonce = $_POST['_wpnonce'];
		if ( !wp_verify_nonce($nonce) ) {
			die('<h1>Sorry, an unknown error occured</h1>');
		}
		$name = $_POST["YourName"];
		$email = $_POST["YourEmail"];
		$message = htmlspecialchars( $_POST["Message"] );
		$honeypot = $_POST["honeypot"];
		$fail = "";
		$fail .= validate_name($name);
		$fail .= validate_email($email);
		$fail .= validate_message($message);
		$fail .= validate_honeypot($honeypot);
		if ( $fail != '' ) {
			echo '<h4 class = "alert-danger">Your message could not be sent because:<br />';
			echo $fail;
			echo '</h6>';
			contact_form();
		} else {
			$to = get_bloginfo('admin_email');
			$subject = 'Contact form email, from: '.$email;
			$headers = array("Reply-To: $email");
			if ( wp_mail($to, $subject, $message, $headers)) {
				echo '<h4 class = "alert alert-success">Thank you for your email. We will reply to you shortly</h4>';
			} else {
				echo '<h4 class = "alert alert-warning">Sorry, an unknown error occured. Please try again later.</h4>';
			}
		}
	} 
	
?>
	
	
<?php
function contact_form() {
?>	
<form id = "contactForm" method = "POST" action = ".">
	<?php wp_nonce_field() ?>
	<p class = "row form-group">
		<label for = "YourName">Your Name</label> &nbsp
		<span class = "alert-danger" id = "warningYourName"></span>
		<input type = "text" id = "YourName" class = "form-control" name = "YourName" value = "" placeholder = "<Your Name>" tabindex = "1">
	</p>
	<p class = "row form-group">
		<label for = "YourEmail">Your Email</label>
		<span class = "alert-danger" id = "warningYourEmail"></span>
		<input type = "text" id = "YourEmail" class = "form-control" name = "YourEmail" value = "" placeholder = "<Your Email>" tabindex = "2">
	</p>
	<p class = "row form-group">
		<label for = "Message">Message</label>
		<textarea id  = "Message" class = "form-control" name = "Message" tabindex = "3" rows = "10" placeholder = "<Send us an email>"></textarea>
	</p>
	<p class = "row form-group honeypot">
		<input type = "text" name = "honeypot" />
	</p>	
	<p class = "row form-group">
		<input type = "submit" name = "submit" class = "btn btn-success" id = "submit" value = "Submit" tabindex = "4" />
	</p>
</form>
</div>
<?php
}
?>

</div>
</div><!--/container-->
<div class = "clearfix"></div>
<?php
get_footer();
?>