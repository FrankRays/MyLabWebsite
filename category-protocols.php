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
	'order' => 'ASC',
	'nopaging' => 'true'
) );

?>

<div class = "container">

	<ul id = "protocolsList">

		<?php if ( $query->have_posts() ):
			while ( $query->have_posts() ):
				$query->the_post();
		?>

		<h3><a href = "<?php the_permalink(); ?>"><?php the_title(); ?></a> &nbsp;
		<span class = "inlineDate"><?php the_time('M j, Y'); ?></span></h3>

		<?php endwhile; endif; ?>

	</ul>

</div>

<?php

get_footer();

?>