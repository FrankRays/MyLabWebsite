<?php

$lab_hard_slug = 'people';

get_header();
get_sidebar();

$users = get_users( array (
	'orderby' => 'meta_value_num',
	'order' => 'ASC',
	'meta_key' => '_lab_position'
) );

?>

<div class = "container">

	<h1>Lab members</h1>

	<?php
	foreach ($users as $user) {
		$id = $user->ID;
		$name = $user->display_name;
		$pic = get_user_meta( $id, '_lab_picture', TRUE);
		$position_num = get_user_meta( $id, '_lab_position', TRUE);
		switch ($position_num) {
			case 1: $pos = 'Principle Investigator'; break;
			case 2: $pos = 'Postdoctoral Fellow'; break;
			case 3: $pos = 'Graduate Student'; break;
			case 4: $pos = 'Undergraduate Researcher'; break;
			case 5: $pos = 'Technician'; break;
			case 6: $pos = 'Lab Manager'; break;
			case 7: $pos = 'Administrative Assistant'; break;
			case 8: $pos = 'Lab member'; break;
		}		
		$bio = get_user_meta( $id, 'description', TRUE);
		$pubs = get_user_meta( $id, '_lab_publication_html', TRUE);
		echo "<h3>$name</h3>";
		echo "<h4>$pos</h4>";
		echo "<img src = \"$pic\" width = \"140px\" >";
		echo "<p>$bio</p>";
	}

	?>

</div>

<?php

get_footer();

?>