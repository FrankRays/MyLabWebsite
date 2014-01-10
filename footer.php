<div id = "footerWrapper">
	
	<div class = "container">
		<?php 
			$footer = get_option('lab_footertext');
			$footer = nl2br($footer);
			echo $footer;
		?>
		<h4 class = "align-right">This website created with FlexLab. &copy; Casey A. Ydenberg, <?php echo date('Y'); ?></h4>
		<h4 class = "align-right">Proudly powered by <a href = "http://www.wordpress.org">Wordpress</a></h4>
	</div>
	
</div>

<script type="text/javscript">
	//closing scripts go here
</script>

<?php wp_footer(); ?>

</body>
</html>