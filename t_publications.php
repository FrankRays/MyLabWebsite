<?php
/*
Template name: Publications Template
*/

$lab_hard_slug = 'publications';

get_header();
get_sidebar();
?>

<div class = "container">

	<?php
	//PUBLICATIONS LIST GOES HERE
	if ( $pubs_list = lab_display_pubs( 0 ) ) {
		echo '<h1>Publications</h1>';
		echo $pubs_list;
	} else {
		echo 'Publication list not found or not properly configured';
	}

	?>

</div>

<?php
	get_footer();
?>