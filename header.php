<?php
	$theme_url = get_stylesheet_directory_uri();
	$url = home_url();
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?php bloginfo('name') ?></title>
  <meta name = "description" content = "<?php bloginfo('description')?>">
  <meta name = "author" content = "Casey A. Ydenberg">
  <meta name = "keywords" content = "research, science, academia, labs, graduate students, education, professors, publications, protocols, papers, citations, altmetrics">
  <link rel = "stylesheet" href = "<?php echo $theme_url; ?>/css/bootstrap.min.css" type = "text/css" media = "all">
  <link rel = "stylesheet" href = "<?php echo $theme_url; ?>/style.css" type = "text/css" media = "all">  
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
  <script src="<?php echo $theme_url; ?>/js/bootstrap.min.js"></script>
  <script src="<?php echo $theme_url; ?>/js/jquery.sticky.js"></script>
  <script src="<?php echo $theme_url; ?>/js/lab_script.js"></script>
  <?php wp_head(); ?>
</head>

<body>

<div id = "titleLineWrapper">
	<div class = "container"  id = "titleLine">
		<h1><a href = "<?php echo $url; ?>">
			<?php bloginfo('name'); ?>		
		</a></h1>
		<a href = "<?php echo $url ?>/wp-login.php" id = "signIn" class = "btn btn-success">Sign-in</a>
		<h2><?php bloginfo('description'); ?></h2>
	</div>
</div>

<div id = "navbarWrapper">
	<nav class="container navbar navbar-default" id = "navbar" role="navigation">
		<div class="navbar-header">
	    	<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
		      <span class="sr-only">Toggle navigation</span>
		      <span class="icon-bar"></span>
		      <span class="icon-bar"></span>
		      <span class="icon-bar"></span>
	    	</button>
	    	<a class="navbar-brand" href="#">Blog</a>
		</div>
		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
		    <ul class="nav navbar-nav">
		    	<?php lab_nav_list(); ?>
		    </ul>
		    <ul class="nav navbar-nav navbar-right">
		    	<!--empty (for now)-->
		    </ul>
	  	</div><!-- /.navbar-collapse -->
	</nav>
</div>

