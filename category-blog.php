<?php

if ( !get_option('lab_blog') ) {
	$url = home_url();
	header("Location:$url/oops");
}

$lab_hard_slug = 'blog';

get_header();
get_sidebar();


$query = new WP_Query();
$query->query('post_type=post');

?>

<div class = "container">

	<h1><?php bloginfo('name'); ?> Blog</h1>

	<?php if ( $query->have_posts() ) :
		while ( $query->have_posts() ): 
			$query->the_post();
	?>
		
	<h4><a href = "<?php the_permalink(); ?>">
		<?php the_title(); ?>
	</a></h4>

	<h5><?php the_time('M j, Y'); ?></h5>

	<?php the_content('<h5>Read more</h5>'); ?>

	<h5><a href = "<?php the_permalink(); ?>">
		<?php comments_number('Be the first to comment', 'There is one comment',
		'There are % comments'); ?></a>
	</h5>

	<?php endwhile; endif; ?>

</div>

<?php

	get_footer();

?>