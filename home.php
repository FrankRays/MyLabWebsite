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

$count = $query->post_count;

?>

<div class = "container">

	<div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
		<!-- Indicators -->
		<ol class="carousel-indicators">
	    	<li data-target="#carousel-example-generic" data-slide-to="0" class="active"></li>
			<?php for ($x = 1; $x < $count; $x++) {
				echo "<li data-target=\"#carousel-example-generic\" data-slide-to=\"$x\"></li>\n";
			}
			?>
	 	</ol>
		<div class="carousel-inner">
		
		<?php
			//initiate The Loop 
			$item_num = 0;
			if ( $query->have_posts() ):
			while ( $query->have_posts() ):
				$query->the_post();
				$id = get_the_ID();
				$src = wp_get_attachment_url( $id );
				$alt = get_post_meta( $id, '_wp_attachment_image_alt', true );
				$caption = get_the_excerpt();
		?>
			<!-- Wrapper for slides -->
		    <?php if ($item_num == 0) {
					echo '<div class = "item active">';
				} else {
					echo '<div class = "item">';
				}
			?>
		    	<img src="<?php echo $src; ?>" alt="<?php echo $alt; ?>" height = "300px" class = "sliderImg" />
		    	<div class="carousel-caption">
		        	<h3><?php echo $caption; ?></h3>
		      	</div>
		    </div>

		<?php 
			//end the loop
			$item_num++;
			endwhile; endif; 
		?>
		
		</div>
	

	</div>

</div>

<?php
$query->rewind_posts();
unset($query);
get_footer();

?>