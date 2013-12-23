<?php

//I am going to try using a single "single.php" at first

//need some redirects to protect blog and protocols if not publically
//available
//also need to make sure comments number doesn't display for projects and
//protocols (can probably turn comments off when declaring the post type 
//in functions.php)

//also note that comments_number is not working within the 

if ( have_posts() ):
	while ( have_posts() ):
		the_post();
		$categories = get_the_category();
		$lab_hard_slug = $categories[0]->slug;
		get_header();
		get_sidebar();		

?>

<div class = "container">
	
	<h3><?php the_title(); ?></h3>

	<h5><?php the_time('M j, Y'); ?></h5>

	<?php the_content(); ?>

	<?php if ( $lab_hard_slug == 'blog' ): ?> 
		<h3 id = "commentsNumber"><?php comments_number('Be the first to comment', 'There is one comment', 'There are % comments'); ?></h3>
		<button class = "btn btn-success" id = "AddYours">Add Yours </button><br />
		<div id = "comments"><?php comments_template(); ?></div>
	<?php endif; ?>

	<?php endwhile; else: ?>
	<h3>Oops, the article you are looking for isn't here.</h3>
	<?php endif; ?>

</div>

<?php

get_footer();

?>