<?php

$lab_hard_slug = 'protocols';

if ( !get_option('lab_protocols') ) {
	header("Location:$url/oops");
}

get_header();
get_sidebar();

$query = new WP_Query();
$query->query( array(
	'post_type' => 'protocol',
	'orderby' => 'meta_value_num',
	'meta_key' => '_lab_post_order',
	'order' => 'ASC'
) );

?>

<div class = "container">

These are the protocols:

	<ul>

		<?php if ( $query->have_posts() ):
			while ( $query->have_posts() ):
				$query->the_post();
		?>

		<li><a href = "<?php the_permalink(); ?>">
		<?php the_title(); ?></a> (<i>Last updated</i>: <?php the_time('M j, Y'); ?>)
		</li>

		<?php endwhile; endif; ?>

	</ul>

</div>

<?php

get_footer();

?>