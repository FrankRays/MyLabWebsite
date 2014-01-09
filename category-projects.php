<?php

$lab_hard_slug = 'projects';

get_header();
get_sidebar();

$query = new WP_Query();
$query->query( array(
	'post_type' => 'project',
	'orderby' => 'meta_value_num',
	'meta_key' => '_lab_post_order',
	'order' => 'ASC',
	'nopaging' => 'true',
) );

?>

<div class = "container">

	<?php if ( $query->have_posts() ):
		while ( $query->have_posts() ):
			$query->the_post();
	?>

	<h3><a href = "<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

	<?php the_content('<h4>Read more</h4>'); ?>

	<?php endwhile; endif; ?>
	
</div>


<?php

get_footer();

?>