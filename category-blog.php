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

	<?php if ( $query->have_posts() ) :
		while ( $query->have_posts() ): 
			$query->the_post();
	?>
		
	<h3><a href = "<?php the_permalink(); ?>">
		<?php the_title(); ?>
	</a></h3>

	<h4><?php the_time('M j, Y'); ?></h2>

	<?php the_content('<h4>Read more</h4>'); ?>

	<h4><a href = "<?php the_permalink(); ?>">
		<?php comments_number('Be the first to comment', 'There is one comment',
		'There are % comments'); ?></a>
	</h4>

	<hr>

	<?php endwhile; endif; ?>

	<h4>
		<?php previous_posts_link('Newer posts', $query->max_num_pages); ?>
		<span class = "align-right"><?php next_posts_link('Older posts', $query->max_num_pages); ?></span>
	</h4>

</div>

<?php

	get_footer();

?>