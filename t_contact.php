<?php
/*
Template name: Contact Template
*/

$lab_hard_slug = 'contact';

get_header();
get_sidebar();
?>

<div class = "container">

	<h2>Send us a message</h2>
  
	<?php the_content(); ?>

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
			echo '<h6 class = "alert-danger">Your message could not be sent because:<br />';
			echo $fail;
			echo '</h6>';
			contact_form();
		} else {
			$to = get_bloginfo('admin_email');
			$subject = 'Contact form email, from: '.$email;
			$headers = array("Reply-To: $email");
			if ( wp_mail($to, $subject, $message, $headers)) {
				echo '<h6 class = "alert alert-success">Thank you for your email. We will reply to you shortly</h6>';
			} else {
				echo '<h6 class = "alert alert-warning">Sorry, an unknown error occured. Please try again later.</h6>';
			}
		}
	} 
	
?>
<?php
function contact_form() {
?>	
<form id = "contactForm" class = "col-md-5 col-md-offset-1 col-sm-6 col-sm-offset-1" method = "POST" action = ".">
	<?php wp_nonce_field() ?>
	<p class = "row form-group">
		<label for = "YourName">Your Name</label> &nbsp
		<span class = "alert-danger" id = "warningYourName"></span>
		<input type = "text" class = "YourName form-control" name = "YourName" value = "" 
		placeholder = "<Your Name>" tabindex = "1">
	</p>
	<p class = "row form-group">
		<label for = "YourEmail">Your Email</label>
		<span class = "alert-danger" id = "warningYourEmail"></span>
		<input type = "text" class = "YourEmail form-control" name = "YourEmail" value = "" 
		placeholder = "<Your Email>" tabindex = "2">
	</p>
	<p class = "row form-group">
		<label for = "Message">Message</label>
		<textarea class = "Message form-control" name = "Message" tabindex = "3" rows = "10" placeholder = "<Send us an email>"></textarea>
	</p>
	<p class = "row form-group honeypot">
		<input type = "text" name = "honeypot" />
	</p>	
	<p class = "row form-group">
		<input type = "submit" name = "submit" class = "btn btn-success" id = "submit" value = "Submit" tabindex = "4" />
	</p>
</form>
<?php
}
?>

</div>

<?php
get_footer();
?>