<?php

$lab_hard_slug = 'people';

get_header();
get_sidebar();

$users = get_users( array (
	'orderby' => 'meta_value_num',
	'order' => 'ASC',
	'meta_key' => '_lab_position',
	'nopaging' => 'true',
) );

?>

<div class = "container">
	
	<?php
	$mem_num = 0;
	foreach ($users as $user) {
		$meta_user = new Lab_User($user);		
		?>
		<div class = "labMemberSummary col-md-4 col-sm-4 col-xs-6" 
		id = "memSum<?php echo $mem_num; ?>">
			<h3><?php echo $meta_user->name; ?></h3>
			<h4><?php echo $meta_user->pos; ?></h4>
			<h6><a>More ...</a></h6>
			<img src = "<?php echo $meta_user->pic; ?>" width = "140 px" >
		</div>
		
		
		<?php	
		$mem_num++;
	}
	?>
<!--close container-->
</div>

<div id = "popupBckgd"> </div>
	<?php
		//restart the loop
		$mem_num = 0;
		foreach ($users as $user) {
			$meta_user = new Lab_User($user);
			?>
			<div class = "popup container labMemberFull" id = "labMemFull<?php echo $mem_num; ?>">
				<div class = "popupHeader row">
					<div class = "col-xs-6">
						<h1><?php echo $meta_user->name; ?></h3>
						<h2><?php echo $meta_user->pos; ?></h4>
					</div>
					<img src = "<?php echo $meta_user->pic; ?>" 
					class = "col-xs-6 labMemberFullPic" width = "140 px" >
				</div>
				<div class = "row">
					<p class = "bio col-xs-12"><?php echo $meta_user->bio; ?></p>
				</div>
				
				<?php if ($meta_user->pubs_bool): ?>
					<div class = "row">
						<h4 class = "col-xs-12">Publications</h4>
						<div class = "col-xs-12">
							<?php echo $meta_user->pubs_str; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
			<?php
			$mem_num++;
		}	

	?>
<?php

get_footer();

/*
Object Lab_User(object $WP_User)
stores ALL user data for this theme. This is essentially
a wrapper around WP_User that takes a WP_User instance as an argument,
and extracts out all the data we will need.
Methods:
Construct
Properties:
Self-explanatory
pubs_bool is whether any publications exist in the database
pubs_str is the actual HTML for that publications list
*/
class Lab_User {
	public $wp_user, $id, $name, $pic, $position_num, $pos, $bio, $pubs_bool, $pubs_str;
	function __construct($wp_user) {
		$this->wp_user = $wp_user;
		$this->id = $wp_user->ID;
		$this->name = $wp_user->display_name;
		$this->pic = get_user_meta( $this->id, '_lab_picture', TRUE);
		$this->position_num = get_user_meta( $this->id, '_lab_position', TRUE);
		switch ($this->position_num) {
			case 1: $this->pos = 'Principle Investigator'; break;
			case 2: $this->pos = 'Postdoctoral Fellow'; break;
			case 3: $this->pos = 'Graduate Student'; break;
			case 4: $this->pos = 'Undergraduate Researcher'; break;
			case 5: $this->pos = 'Technician'; break;
			case 6: $this->pos = 'Lab Manager'; break;
			case 7: $this->pos = 'Administrative Assistant'; break;
			case 8: $this->pos = 'Lab member'; break;
		}		
		$this->bio = get_user_meta( $this->id, 'description', TRUE);
		if ( $this->pubs_str = get_user_meta( $this->id, '_lab_publication_html', TRUE ) ){
			$this->pubs_bool = TRUE;
		} else {
			$this->pubs_bool = FALSE;
		}
	}
}

?>