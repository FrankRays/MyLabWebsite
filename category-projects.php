<?php

$lab_hard_slug = 'projects';

get_header();
get_sidebar();

$query = new WP_Query();
$query->query( array(
	'post_type' => 'project',
	'orderby' => 'meta_value_num',
	'meta_key' => '_lab_post_order',
	'order' => 'ASC'
) );

?>

<div class = "container">

	<?php if ( $query->have_posts() ):
		while ( $query->have_posts() ):
			$query->the_post();
	?>

	<h3><?php the_title(); ?></h3>

	<?php the_content(); ?>

	<?php endwhile; endif; ?>
	
</div>


<?php

get_footer();

?>