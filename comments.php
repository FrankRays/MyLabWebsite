<?php
// Do not delete these lines
	if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die ('Server encountered a problem. Press the back button and try again');

	if ( post_password_required() ) { ?>
		<p class="nocomments">This post is password protected. Enter the password to view comments.</p>
	<?php
		return;
	}
?>

<?php

lab_comment_form();

?>
<div class = "clearfix"></div>
<?php 

if ( $comments ) {
	$args = array(
		'style' => 'div',
		'type' => 'comment',
	);	
	wp_list_comments( $comments );
}







function lab_comment_form(){
	echo '<div id = "mobileCommentForm" class = "col-md-7 col-sm-12">';
	echo '<h3>';
	comment_form_title();
	echo '</h3>';
	?>
	<h4 class ="alert alert-success" id = "submitOutcome"></h4>
	<a id = "respond"></a>
	<form action="http://localhost/wordpress/wp-comments-post.php" method="post" id = "commentForm">
		<p class="comment-notes">
			Your email address will not be published. Required fields are marked <span class="required">*</span>
		</p>
		<p class = "row form-group">
			<label for="author">Your Name <span class="required">*</span></label>
			<span class = "alert-danger" id = "warningYourName"></span>
			<input name="author" id = "YourName" class = "form-control" type="text" placeholder = "<Your Name>" tabindex = "1" /> 
		</p>
		<p class = "row form-group">
			<label for="email">Your Email <span class="required">*</span></label>
			<span class = "alert-danger" id = "warningYourEmail"></span>
			<input name="email" id = "YourEmail" class = "form-control" type="text" placeholder="<Your Email>" tabindex = "2" />
		</p>
		<p class = "row form-group">
			<label for="url">Your Website</label>
			<span class = "alert-danger" id = "warningYourWebsite"></span>
			<input id="YourWebsite" class = "form-control" name="url" type="text" value="<Your Website>" tabindex = "3">
		</p>
		<p class = "row form-group">
			<label for="comment">Comment</label>
			<textarea id="comment" name="comment" class = "form-control" rows="10" placeholder = "<Your comment>" tabindex = "4"></textarea>
		</p>
		<p class = "row form-group">
			<input type = "submit" name = "submit" class = "btn btn-success" id = "submit" value = "Submit Comment" tabindex = "5" />
		</p>
		<?php comment_id_fields(); ?>
	</form>
</div>
<div class = "clearfix"></div>
<?php
}
?>
