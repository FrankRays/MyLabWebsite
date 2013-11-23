<?php

get_header();

$query = new WP_Query();
$query->query( array (
	'post_type' => 'attachment',
	'post_status' => 'any',
	'meta_query' => array (
		array(
			'key' => '_lab_post_order',
			'value' => 1,
			'type' => 'numeric',
			'compare' => '>=',
		)
	),
	'meta_key' => '_lab_post_order',
	'orderby' => 'meta_value_num',
	'order' =>'ASC',
));


?>

<div class = "container">

	<?php if ( $query->have_posts() ):
		while ( $query->have_posts() ):
			$query->the_post();
			$id = get_the_ID();
			$src = wp_get_attachment_url( $id );
			echo "<h3>A slider item</h3>";
	?>

	<img src = "<?php echo $src ?>" width = "300px" />

	<?php endwhile; endif; ?>

</div>

<?php

get_footer();

?>