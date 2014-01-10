<?php

/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
THEME ACTIVATION (INITIAL) AND 
REACTIVATION FUNCTIONS
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
//this action is triggered when we switch OFF this theme
add_action( 'switch_theme' , 'lab_deactivate');
function lab_deactivate() {
	update_option( 'lab_active', 'FALSE' );
	wp_clear_scheduled_hook( 'lab_daily_update' );
}

//$lab_active gets set to TRUE only is the theme was already activated
$lab_active = get_option('lab_active');
//run following commands when the theme is activated
if ( !$lab_active || $lab_active == 'FALSE' ) {
	if ( !is_page('contact') ) {
		//hard-delete all the posts with a slug of "contact"
		$ids = $wpdb->get_col("SELECT ID from $wpdb->posts WHERE post_name = 'contact'");
		foreach ( $ids as $id ) {
			wp_delete_post($id, TRUE);
		}
		$contact_post = wp_insert_post( array(
			'post_type' => 'page',
			'post_title' => 'Contact Information',
			'post_name' => 'contact',
			'post_status' => 'publish',
		));
		//hard-reset the post_name to 'contact'
		$wpdb->query("UPDATE $wpdb->posts SET post_name = 'contact' WHERE ID = $contact_post");
		add_post_meta( $contact_post, '_wp_page_template', 't_contact.php', TRUE );
		update_post_meta( $contact_post, '_wp_page_template', 't_contact.php' );
	}
	
	if ( !is_page('publications') ) {
		//hard-delete all the posts with a slug of "publications"
		$ids = $wpdb->get_col("SELECT ID from $wpdb->posts WHERE post_name = 'publications'");
		foreach ( $ids as $id ) {
			wp_delete_post($id, TRUE);
		}
		$pub_post = wp_insert_post( array(
			'post_type' => 'page',
			'post_title' => 'Publications',
			'post_name' => 'publications',
			'post_status' => 'publish',
		));
		//hard-reset the post_name to 'contact'
		$wpdb->query("UPDATE $wpdb->posts SET post_name = 'publications' WHERE ID = $pub_post");
		add_post_meta( $pub_post, '_wp_page_template', 't_publications.php', TRUE);
		update_post_meta( $pub_post, '_wp_page_template', 't_publications.php' );
	}
		
	//insert new taxonomy
	wp_insert_term( 'Blog', 'category', array('slug' => 'blog') );
	wp_insert_term( 'Protocol', 'category', array('slug' => 'protocols') );
	wp_insert_term( 'Lab member', 'category', array('slug' => 'people') );
	wp_insert_term( 'Project', 'category', array('slug' => 'projects') );
	
	//TODO:
	//go thru every post and assign appropriate category
	//should look into creating a new taxonomy entirely. The problem is the permalink
	//structure.
	//also, should remove the category meta box from the admin since it now won't
	//do anything
	
	//change options
	update_option( 'default_role', 'contributor');
	update_option( 'permalink_structure', '/%category%/%postname%/');
	
	//schedule cron
	wp_schedule_event( current_time( 'timestamp' ), 'daily', 'lab_daily_update' );
}
//will trigger EVERY TIME this script is used ...
//ie whenever the theme is active
update_option( 'lab_active', 'TRUE' );

/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
DAILY CRON:
update publications lists
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

add_action('lab_daily_update', 'lab_update_lists');

/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
hooked by lab_daily_update

Update the publications lists for all users
Pull out their pubsource, identifier, and IS key
from the metadata, create an impactpubs object, 
run the import, and write to db 
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
//THIS FUNCTION NEEDS TO BE TESTED SOMEHOW (CHECK ERROR LOGS!!!)
function lab_update_lists(){
	$users = get_users( array() );
	$ids = array( '0' => '0' );
	foreach ( $users as $user ){
		$id = $user->ID;
		$ids[] = $id;
	}
	foreach ( $ids as $id ){
		$pubsource = $id ? get_user_meta( $id, '_lab_pubsource', TRUE) : get_option('lab_pubsource');
		//if no entry in meta database (user has not set up their profile), 
		//then skip to the next one
		if ( $pubsource == '' ) continue;
		$identifier = $id ? get_user_meta( $id, '_lab_identifier', TRUE) : get_option('lab_identifier');
		$is_key = $id ? get_user_meta( $id, '_lab_is_key', TRUE) : get_option('lab_is_key');
		$impactpubs = new lab_publist( $id, $is_key );
		if ( $pubsource == 'pubmed' ) $impactpubs->import_from_pubmed( $identifier );
		if ( $pubsource == 'orcid' ) $impactpubs->import_from_orcid( $identifier );
		//only write to the database if data was retrieved (in case of problems in search)
		if ( count( $impactpubs->papers ) > 0 ) $impactpubs->write_to_db();	
	}
}

/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
ADMIN JS AND CSS
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

add_action( 'admin_enqueue_scripts', 'lab_admin_scripts' );

function lab_admin_scripts() {
	$parent_url = get_template_directory_uri();
	wp_enqueue_script( 'admin_script', $parent_url.'/js/lab_admin_script.js');
	wp_enqueue_style( 'admin_style', $parent_url.'/css/lab_admin_style.css');
}


/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
FLEXLAB SETTINGS MENU (Dashboard)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
add_action('admin_menu', 'lab_settings_menu');

//hooked by: admin_menu wp action
function lab_settings_menu() {
	//create top-level menu
	add_dashboard_page( 'FlexLab Settings', 'FlexLab Settings', 'edit_users',
	'lab-settings', 'lab_settings_page' );
	add_action('admin_init', 'lab_register_settings');
	wp_enqueue_script('editor.js');
}

//hooked by: lab_settings_menu
function lab_register_settings() {
	register_setting( 'lab_settings_group', 'lab_style' );
	register_setting( 'lab_settings_group', 'lab_blog');
	register_setting( 'lab_settings_group', 'lab_protocols');
	register_setting( 'lab_settings_group', 'lab_email');
	register_setting( 'lab_settings_group', 'lab_pubsource' );
	register_setting( 'lab_settings_group', 'lab_identifier' );
	register_setting( 'lab_settings_group', 'lab_impactstory_key' );
	register_setting( 'lab_settings_group', 'lab_footertext');
	//retrieve publication information
	$pubsource = get_option('lab_pubsource');
	$identifier = get_option('lab_identifier');
	$is_key = get_option('lab_impactstory_key');
	$impactpubs = new lab_publist(0, $is_key );
	if ( $pubsource == 'pubmed' ) {
		$impactpubs->import_from_pubmed( $identifier );	
	} else if ( $pubsource == 'orcid' ) {
		$impactpubs->import_from_orcid( $identifier );
	}
	$impactpubs->write_to_db();
}

/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Hooked by: lab_settings_menu
Output the HTML for the FlexLab settings menu (Dashboard)
Calls: is_checked(), is_selected()
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
function lab_settings_page() {
	?>
	<div class = "wrap">
		<h2>FlexLab Settings</h2>
		<form method = "POST" action = "options.php">
			<?php settings_fields( 'lab_settings_group' ); ?>
			<table>
				
				<tr>
				<td><label for = "lab_style">Styling</label></td>
				<td><select name = "lab_style">
					<option value = "light" 
						<?php echo is_selected( 'light', get_option('lab_style' ) ); ?> >
						Standard Light</option>
					<option value = "dark"
						<?php echo is_selected( 'dark', get_option('lab_style') ); ?> >
						Standard Dark</option>
				</select>
				</td>
				<td></td>
				</tr>
				
				<tr><td><label for = "lab_blog">Lab blog visible</label></td>
				<td><input type = "checkbox" name = "lab_blog" 
					<?php echo is_checked( get_option('lab_blog') ); ?> >
				</td>
				<td></td>
				</tr>
				
				<tr><td><label for = "lab_protocols">Lab protocols visible</label></td>
				<td><input type = "checkbox" name = "lab_protocols" 
					<?php echo is_checked( get_option('lab_protocols') ); ?> >
				</td>
				<td></td>
				</tr>
				
				<tr>
				<td><label for = "lab_email">Lab email</label></td>
				<td><input type = "text" name = "lab_email"
				value = "<?php echo esc_attr__( get_option( 'lab_email' ) ); ?>" /></td>
				<td><i>Email address where Contact Form emails will be sent</i></td>
				</tr>
				
				<tr><td colspan = "3">
				<h3>Lab publication information</h3>
				<p><i>Note: this is distinct from publication information for individual lab members, which can be viewed under the "Users" tab</i></p>
				</td></tr>
				
				<tr>
				<td><label for = "lab_pubsource">Publication source</label></td>
				<td><input type = "radio" name = "lab_pubsource" value = "pubmed" 
					<?php if ( get_option('lab_pubsource') == 'pubmed' ) echo "checked"; ?> />PubMed<br />
					<input type = "radio" name = "lab_pubsource" value = "orcid" 
					<?php if ( get_option('lab_pubsource') == 'orcid' ) echo "checked"; ?> />ORCiD</td>
				<td></td>
				</tr>
			
				<tr>
				<td><label for = "lab_identifier">Identifier</label></td>
				<td><input type = "text" name = "lab_identifier" 
				value = "<?php echo esc_attr__( get_option( 'lab_identifier' ) ); ?>"></td>
				<td><i>For ORCiD, this is a 16-digit number (e.g. 0000-0003-1419-2405).<br>
			For PubMed, enter a unique query string (e.g. Ydenberg CA AND (Brandeis[affiliation] OR Princeton[affiliation]))</i></td>
				</tr>
			
				<tr>
				<td><label for = "lab_impactstory_key">ImpactStory API key</label><br>
				<i>(Optional)</i></td>
				<td><input type = "text" name = "lab_impactstory_key" 
				value = "<?php echo esc_attr__( get_option( 'lab_impactstory_key' ) ); ?>"></td>
				<td><i>Email <a href = "mailto:team@impactstory.org">team@impactstory.org</a> to request your <strong>free</strong> API key</i></td>
				</tr>
			
				<tr><td colspan = "3">
					<h3>Footer Text</h3>
				</td></tr>
				
				<tr><td colspan = "3">	
					<?php wp_editor(get_option('lab_footertext'), 'footertext', 
						array('textarea_name' => 'lab_footertext',
					) ); ?>	
				</td></tr>
			
			</table>
			
			
			
			
			<p class = "submit">
				<input type = "submit" class = "button-primary"
				value = "Save changes">
			</p>			
				
		</form>

	</div>

		
<?php
}

/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
CHANGES TO USER META DATA (USERS TAB)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
//register a new user (no meta data can be entered at this point)
add_action( 'user_register', 'lab_new_user' );

//hooks to add the menu (covers edit screens for ALL users, including the current user)
add_action( 'show_user_profile', 'lab_user_meta' );
add_action( 'edit_user_profile', 'lab_user_meta' );

//hooks to add user infromation (covers ALL users, including the current user)
add_action( 'personal_options_update', 'lab_update_user_meta' );
add_action( 'edit_user_profile_update', 'lab_update_user_meta' );
/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Display the form elements for adding user meta 
The wrapping form and submit button is associated with 
NOTE: THE ARGUMENT PASSED TO THIS FUNCTION IS A USER OBJECT
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
function lab_user_meta( $user ) {
	$user_id = $user->ID;
	$position = get_user_meta( $user_id, '_lab_position', TRUE );
	$picture = get_user_meta( $user_id, '_lab_picture', TRUE);
	$url = get_template_directory_uri();
	if ( $picture == '' ) $picture = $url.'/img/notshown.jpg';
	$pubsource = get_user_meta( $user_id, '_lab_pubsource', TRUE );
	if ( $pubsource == '') $pubsource = 'pubmed';
	$identifier = get_user_meta( $user_id, '_lab_identifier', TRUE );
	$is_key = get_user_meta( $user_id, '_lab_is_key', TRUE );
	$identifier = stripslashes($identifier);
	
	//NOTE: Should I include server-side form validation? WP will
	//take care of sanitization, so is JS form validation enough?
	
	?>
	<div class = "wrap">
		<hr />
		<h2>FlexLab Settings</h2>
		<br />
		<table>
			<tr>
			<td><label for = "position">Position</label></td>
			<td><select name = "position">
				<option value = "8"
				<?php echo is_selected( 8, $position ); ?> >
				</option>
				<option value = "1"
				<?php echo is_selected( 1, $position ); ?> >
				Principle Investigator</option>
				<option value = "2"
				<?php echo is_selected( 2, $position); ?> >
				Postdoctoral Fellow</option>
				<option value = "3"
				<?php echo is_selected( 3, $position ); ?> >
				Graduate Student</option>
				<option value = "4"
				<?php echo is_selected( 4, $position ); ?> >
				Undergraduate Researcher</option>
				<option value = "5"
				<?php echo is_selected( 5, $position ); ?> >
				Technician</option>
				<option value = "6"
				<?php echo is_selected( 6, $position ); ?> >
				Lab Manager</option>
				<option value = "7"
				<?php echo is_selected( 7, $position ); ?> >
				Administrative Assistant</option>
			</select></td> 
			<td></td>
			</tr>
			
			<tr>
			<td><label for = "picture">Picture</label></td>
			<td><select name = "picture" id = "labAdminPicSelect">
				<option value = "<?php echo $url.'/img/notshown.jpg'?>"
				<?php echo is_selected($url.'/img/notshown.jpg', $picture)?>>No picture</option>
				<?php
					$images = get_posts( array( 
						'post_type' => 'attachment',
						'post_status' => 'any' ) );
					foreach ( $images as $image ) {
						if ( strpos($image->post_mime_type, 'image') !== FALSE ) {
							echo '<option value = "'.$image->guid;
							echo '"'.is_selected( $image->guid, $picture).'>';
							echo $image->post_name.'</option>';
						}
					}
				?>
			</select>
			<p id = "labAdminChangePic">Change pic for this user</p>
			<img id = "labAdminUserPic" src = "<?php echo $url.'/img/notshown.jpg' ?>" />
			</td>
			<td></td>
			</tr>
			
			<tr><td colspan = "3">
			<h3>Publication information</h3>
			</td></tr>
			
			<tr>
			<td><label for = "pubsource">Publication source</label></td>
			<td><input type = "radio" name = "pubsource" value = "pubmed" 
			<?php if ( $pubsource == 'pubmed' ) echo 'checked'; ?> />PubMed<br />			
			<input type = "radio" name = "pubsource" value = "orcid" 
			<?php if ( $pubsource == 'orcid' ) echo 'checked'; ?> />ORCiD
			</tr>
		
			<tr>
			<td><label for = "identifier">Identifier</label></td>
			<td><input type = "text" name = "identifier" 
			value = "<?php echo esc_attr__( $identifier ); ?>"></td>
			<td><i>For ORCiD, this is a 16-digit number (e.g. 0000-0003-1419-2405).<br>
		For PubMed, enter a unique query string (e.g. Ydenberg CA AND (Brandeis[affiliation] OR Princeton[affiliation]))</i></td>
			</tr>
		
			<tr>
			<td><label for = "impactstory_key">ImpactStory API key</label><br>
			<i>(Optional)</i></td>
			<td><input type = "text" name = "impactstory_key" 
			value = "<?php echo esc_attr__( $is_key ); ?>"></td>
			<td><i>Email <a href = "mailto:team@impactstory.org">team@impactstory.org</a> to request your <strong>free</strong> API key</i></td>
			</tr>
			
		</table>	
	</div>
	
	<!--background divs, may want to move to end of page is there is a appropriate hook-->
	<div id = "popupBckgd">	</div>
	<div id = "labAdminPics" class = "popup">
		<h2>Choose picture</h2>
		<div class = "wrap" id = "labAdminPicWrap"></div>
	</div>

	<?php
}

function lab_new_user( $user_id ) {
	$position = 8;
	$picture = get_template_directory_uri().'/img/notshown.jpg';
	add_user_meta( $user_id, '_lab_position', $position);
	add_user_meta( $user_id, '_lab_picture', $picture);
}


/*~~~~~~~~~~~~~~~~~~~~~~~~~~
Enter selected User meta into the database
NOTE: THE ARGUMENT PASSED TO THIS FUNCTION IS THE ID NUMBER FOR A USER
~~~~~~~~~~~~~~~~~~~~~~~~~~*/
function lab_update_user_meta( $user_id ) {
	$position = isset( $_POST['position'] ) ? $_POST['position'] : 'Researcher';
	$picture  = isset( $_POST['picture'] )  ? $_POST['picture'] : 'notshown';
	$pubsource = isset( $_POST['pubsource'] ) ? $_POST['pubsource'] : '';
	$identifier = isset( $_POST['identifier'] ) ? $_POST['identifier'] : '';
	$is_key = isset( $_POST['impactstory_key'] ) ? $_POST['impactstory_key'] : '';
	$current_user = wp_get_current_user();
	$current_id = $current_user->ID;
	if ( ($user_id == $current_id) || current_user_can( 'edit_user' ) ) {
		if ( !add_user_meta( $user_id, '_lab_position', $position, TRUE ) ) {
			update_user_meta( $user_id, '_lab_position', $position);
		}
		if ( !add_user_meta( $user_id, '_lab_picture', $picture, TRUE ) ) {
			update_user_meta( $user_id, '_lab_picture', $picture );
		}
		if ( !add_user_meta($user_id, '_lab_pubsource', $pubsource, TRUE ) ) {
				update_user_meta($user_id, '_lab_pubsource', $pubsource);
		}
		if ( !add_user_meta($user_id, '_lab_identifier', $identifier, TRUE ) ) {
			update_user_meta($user_id, '_lab_identifier', $identifier );
		}
		if ( !add_user_meta($user_id, '_lab_is_key', $is_key, TRUE ) ) {
			update_user_meta($user_id, '_lab_is_key', $is_key);
		}
		//retrieve publication information
		$impactpubs = new lab_publist($user_id, $is_key );
		if ( $pubsource == 'pubmed' ) {
			$impactpubs->import_from_pubmed( $identifier );	
		} else if ( $pubsource == 'orcid' ) {
			$impactpubs->import_from_orcid( $identifier );
		}
		$impactpubs->write_to_db();
	} else {
		die("<h1>Problem performing requested operation</h1>");
	}	
}

/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
ADD PROJECTS AND PROTOCOLS TABS, ADD META BOXES
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

add_action( 'init', 'lab_register_posts');

function lab_register_posts() {
	//add custom post type for projects
	register_post_type( 'project', array(
		'labels' => array(
			'name' => 'Projects',
			'singular_name' => 'Project',
			'edit_item' => 'Edit Project',
			'add_new_item' => 'New Project',
		),
		'public' => TRUE,
		'capability_type' => 'page',
	));
	register_post_type( 'protocol', array(
		'labels' => array(
			'name' => 'Protocols',
			'singular_name' => 'Protocol',
			'edit_item' => 'Edit Protocol',
			'add_new_item' => 'New Protocol',
		),
		'public' => TRUE,
		'capability_type' => 'page',
	));
}
//add custom meta boxes to ATTACHMENTS, PROJECTS, and PROTOCOLS
add_action( 'add_meta_boxes', 'lab_add_meta_boxes');
function lab_add_meta_boxes() {
	//attachment meta data (to add images to wmuSlider)
	add_meta_box('labAdminSliderMeta', 'FlexLab Settings', 'lab_slider_order_box',
	'attachment', 'side', 'core');
	
	//projects (and protocols) order meta box
	$screens = array('project', 'protocol');
	foreach( $screens as $screen ) {
		add_meta_box('labAdminProjectsMeta', 'FlexLab Settings', 'lab_post_order_box',
		$screen, 'side', 'core');
	}
}

/*~~~~~~~~~~~~~~~~~~~~~~~~~
Generate HTML for meta box in attachment screen.
~~~~~~~~~~~~~~~~~~~~~~~~~*/
function lab_slider_order_box( $post ) {
	wp_nonce_field( 'save_post', 'lab_meta_nonce' );
	$order = get_post_meta( $post->ID, '_lab_post_order', TRUE);
	if ( !$order ) $order = 0;
	else $order = esc_attr($order);
	?>
	<label for = "order">Slider Order</label>
	<input type = "text" class = "labAdminNumber" value = "<?php echo $order?>"
	name = "lab_post_order" />
	<p><i>This number is used to place images in the animated slider. For best results
	use images that are at least xxx pixels wide by xxx pixels high. If this image
	does NOT belong in the slider, leave this space blank or enter 0.</i></p>
	<?php
}

/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Generate HTML for meta box in Project and Post screen
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
function lab_post_order_box( $post ) {
	wp_nonce_field( 'save_post', 'lab_meta_nonce' );
	$order = get_post_meta( $post->ID, '_lab_post_order', TRUE);
	if (!$order) $order = 0;
	else $order = esc_attr($order);
	?>
	<label for = "order">Order<label>
	<input type = "text" class = "labAdminNumber" value = "<?php echo $order ?>" 
	name = "lab_post_order" />
	<p><i>This value is used to determine the order in which items are displayed. If left
	blank or in the case of a tie, newer items are displayed first.</i></p>
	<?php
}

//save Project, Protocol, and Attachment meta data
//(same function covers all 3)
add_action( 'save_post', 'lab_save_post' );
add_action( 'edit_attachment', 'lab_save_post' );
function lab_save_post( $post_id ){
	$type = isset( $_POST['post_type'] ) ? $_POST['post_type'] : 'post' ;
	$slug = '';
	switch ($type){
		case 'post':
			$slug = 'blog';
			break;
		case 'project':
			$slug = 'projects';
			break;
		case 'protocol':
			$slug = 'protocols';
			break;
	}
	if ( $category = get_category_by_slug( $slug ) ) {	
		wp_set_post_categories( $post_id, array($category->term_id) );
	}
	
	//attach custom post meta
	if ( !isset($_POST['lab_meta_nonce'] ) ) return $post_id;
	$nonce = $_POST['lab_meta_nonce'];
	if ( wp_verify_nonce($nonce, 'save_post') ) {
		$order = $_POST['lab_post_order'];
		if ( intval( $order ) !== FALSE ) {
			update_post_meta( $post_id, '_lab_post_order', $order );
		} else {
			$order = 0;
		}
	} else return $post_id;
}

/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
CLIENT-SIDE FUNCTIONS
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

add_action( 'wp_enqueue_scripts', 'lab_parent_scripts' );

function lab_parent_scripts() {
	//from impactpubs
	$parent_url = get_template_directory_uri();
	wp_enqueue_script('ip', $parent_url.'/js/ip_script.js');
	wp_enqueue_style('ip', $parent_url.'/css/ip_style.css');
}

function lab_display_pubs( $user_id ) {
	global $wpdb;
	if ( $user_id ) {
		$result = get_user_meta($user_id, '_lab_publication_html', TRUE);
		if ($result == '') $result = FALSE;
	} else {
		$result = get_option('lab_publication_html');
	}
	//NOTE: SHOULD RETURN FALSE IF PUBLICATIONS NOT FOUND
	return $result;
}

function lab_nav_list() {
	global $lab_hard_slug;
	
	$slugs = array(
		'projects', 'people', 'publications',
	);
	if ( get_option('lab_protocols') ) {
		$slugs[] = 'protocols';
	}
	if ( get_option('lab_blog') ) {
		$slugs[] = 'blog';
	}
	$slugs[] = 'contact';
	foreach ( $slugs as $slug ) {
		$name = strtoupper($slug);
		$url = home_url().'/'.$slug;
		if ( $slug == $lab_hard_slug ) {
			echo "<li class = \"active\"><a href = \"$url\">$name</a></li>";
		} else {
			echo "<li><a href = \"$url\">$name</a></li>";
		}
	}
}


/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
SUPPORTING CODE
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
object lab_publist(string $user, string $is_key)
Properties: 
user - the current user's name, 
papers - an array of papers belonging to that user, 
is_key - the impactstory key for that user (optional)

Methods:
import_from_pubmed(string $pubmed_query)
import_from_orcid(string $orcid_id)
make_html($key)

Declared by:
lab_update_user_meta, lab_register_settings, lab_update_lists
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

class lab_publist {
	public $usr, $papers = array(), $is_key;
	function __construct($usr, $is_key = ''){
		$this->usr = $usr;
		if ( $is_key != '' ) $this->is_key = $is_key;
	}
	/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	Retrieve paper properties from a publication list.
	import_from_pubmed(string $pubmed_query). 
	Because PubMed does not have unique identifiers for authors, this is usually
	a search string that will pull up pubs from the author in question. 
	eg: 
	* ydenberg
	* ydenberg CA[author] 
	* ydenberg CA[author] AND (princeton[affiliation] OR brandeis[affiliation])
	Assigns paper properties to the child objects of class paper.
	Called by: lab_update_user_meta, lab_register_settings, lab_update_lists
	Calls:
	lab_author_format()
	~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
	function import_from_pubmed($pubmed_query) {
		//format the author string with "%20" in place of a space to make a remote call
		$pubmed_query = preg_replace("/[\s\+]/", "%20", $pubmed_query);
		//build the url for the initial search
		$search = "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=pubmed&term=".$pubmed_query
		."&retmax=1000&retmode=xml";
		//build the url for subsequent call to esummary to retrieve the records
		$retrieve = "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi?db=pubmed&id=";
		//make a call to pubmeds esearch utility, to retrieve pmids associated with an authors name or
		//other search
		$result = wp_remote_retrieve_body( wp_remote_get($search) );
		//open a new DOM and dump the results from esearch
		$dom = new DOMDocument();
		$dom->loadXML($result);
		//pmid numbers are stored in a tag called "Id". Get all of these, then loop through them, adding each one
		//to the url that will be sent to esummary
		$ids = $dom->getElementsByTagName('Id');
		//check that publications have been found
		if ($ids) {
			foreach ($ids as $id){
				//build the URL to retrieve individual records
				$retrieve = $retrieve.$id->nodeValue.",";
				//the ending ",", if present, doesn't seem to have any adverse effects
			}
			//make a second call to pubmed's esummary utility
			$result = wp_remote_retrieve_body( wp_remote_get($retrieve) );
			if ( !$result ) die('There was a problem getting data from PubMed');
			//load the results into a DOM, then retrieve the DocSum tags, which represent each paper that was found
			$dom->loadXML($result);
			$papers = $dom->getElementsByTagName('DocSum');
			$paper_num = 0;
			foreach ($papers as $paper){
				$this->papers[$paper_num] = new lab_paper();
				//id_types will be assigned as pmid in each case 
				$this->papers[$paper_num]->id_type = 'pmid';
				//get the id number associated with the record
				$this->papers[$paper_num]->id = $paper->getElementsByTagName('Id')->item(0)->nodeValue; 
				//initialize values of the data we want to get from the XML
				//authors and year need further manipulation and are not immediately declared in the Object
				$authors = array();
				$year = 0;
		/*the relevent fields (except for the pmid) are stored in tags of the style <Item Name="Journal">Nature</Item>.
		Since PHP does not have a method for getting XML data by attribute name, we have to get all the nodes of type
		"Item", then pick out the ones with relevent name. We do this by calling "getAttribute("Name")" and then comparing
		it to the Names for the data we're interested in.*/
				$items = $paper->getElementsByTagName("Item");
				foreach ($items as $item){
					$datatype = $item->getAttribute("Name");
					switch ($datatype){
						case "Author": $authors[] = $item->nodeValue;
							break;
						case "PubDate": $year = $item->nodeValue;
							break;
						case "Title": $this->papers[$paper_num]->title = $item->nodeValue;
							break;
						case "Source": $this->papers[$paper_num]->journal = $item->nodeValue;
							break;
						case "Volume": $this->papers[$paper_num]->volume = $item->nodeValue;
							break;
						case "Issue": $this->papers[$paper_num]->issue = $item->nodeValue;
							break;
						case "Pages": $this->papers[$paper_num]->pages = $item->nodeValue;
							break;
					} //end switch
				} //end inner foreach
				//the date includes year and month. Strip them out. 
				$this->papers[$paper_num]->year = substr($year, 0, 4);
				//format the authors list
				$this->papers[$paper_num]->authors = lab_author_format($authors);
				$this->papers[$paper_num]->url = 'http://www.ncbi.nlm.nih.gov/pubmed/'.$this->papers[$paper_num]->id;
				$paper_num++;
			}
		}				
	}
	/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	Retrieve paper properties from a publication list.
	import_from_orcid(string $orcid_id). This is a 16 digit number linking to an ORCID user's profile.
	eg 0000-0003-1419-2405
	Assigns paper properties to the child objects of class paper.
	Called by: impactpub_settings_form()
	Calls:
	lab_author_format()
	~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
	function import_from_orcid($orcid_id){
		$search = 'http://feed.labs.orcid-eu.org/'.$orcid_id.'.json';
		$result = wp_remote_retrieve_body( wp_remote_get($search) );
		//if ( !$result ) die('There was a problem getting data from ORCiD');
		$works = json_decode($result);
		$paper_num = 0;
		foreach ($works as $work){
			$listing = new impactpubs_paper();
			//get the publication year (not essential)
			if ( isset($work->issued->{'date-parts'}[0][0]) ) {
				$listing->year = $work->issued->{'date-parts'}[0][0];
			} else {
				continue;
			}
			//get the title (essential)
			if ( isset($work->title) ) {
				$listing->title = $work->title;	
			} else {
				continue;
			}
			//get the journal/publisher/book series (not essential)
			if ( isset( $work->{'container-title'} ) ) {
				$listing->journal = $work->{'container-title'};
			} else if ( isset($work->publisher) ) {
				$listing->journal = $work->publisher;
			}
			//get the authors list (essential)
			if ( isset($work->author) ) {
				$authors_arr = array();
				foreach ($work->author as $author_ob) {
					$authors_arr[] = $author_ob->family.', '.$author_ob->given;
				}
				$listing->authors = impactpubs_author_format($authors_arr);
			} else {
				continue;
			}
			//get volume, issue, and pages (not essential)
			if ( isset($work->volume) ) $listing->volume = $work->volume;
			if ( isset($work->issue) )  $listing->issue =  $work->issue;
			if ( isset($work->page) )  $listing->pages =  $work->page;
			//get the unique identifier
			if ( isset($work->URL) && isset($work->DOI) ) {
				$listing->id_type = 'doi';
				$listing->id = $work->DOI;
				$listing->url = $work->URL;
			} elseif ( isset($work->DOI) ) {
				$listing->id_type = 'doi';
				$listing->id = $work->DOI;
				$listing->url = 'http://dx.doi.org/'.$work->DOI;
			} elseif ( isset($work->URL) ) {
				$listing->id_type = 'url';
				$listing->id = $work->URL;
				$listing->url = $work->URL;
			} else {
				$listing->id_type = '';
				$listing->id = '';
				$listing->url = '';
			}
			$this->papers[$paper_num] = new impactpubs_paper();
			$this->papers[$paper_num] = $listing;
			unset($listing);
			$paper_num++;
		} 
	}
	/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	string $html make_html()
	creates the HTML for a publication list.
	Called by lab_update_user_meta, lab_register_settings, lab_update_lists
	Calls lab_paper->make_html()
	~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
	function make_html(){
		if ( !count( $this->papers ) ) return FALSE;
		$html = '';
		if ($this->is_key) {
			$html .= '<script type="text/javascript" src="http://impactstory.org/embed/v1/impactstory.js"></script>';
		}
		foreach ($this->papers as $paper){
			$html .= $paper->make_html($this->is_key);
		}
		if ($this->is_key) {
			$html .= '<p class = "lab_footnote"><i>Badges provided by ImpactStory. <a href = "http://www.impactstory.org">Learn more about altmetrics</a></i></p>';
		}
		return $html;
	}
	/*~~~~~~~~~~~~~~~~~~~~~~~~~~
	write_to_db()
	Writes the html (only) of the retrieved search as metadata
	Called by lab_update_user_meta, lab_register_settings, lab_update_lists
	~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
	function write_to_db(){
		$user = $this->usr;
		if ( !$value = $this->make_html() ) {
			return FALSE;
		}
		if ($this->usr) {
			if ( !add_user_meta($user, '_lab_publication_html', $value, TRUE ) ) {
				update_user_meta($user, '_lab_publication_html', $value);
			}	
		} else {
			if ( !add_option('lab_publication_html', $value) ) {
				update_option('lab_publication_html', $value);
			}
		}	
	}
}

/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
object lab_paper(string $user, string $is_key)
Properties: self-explanatory

Methods:
make_html(string $key)
Key is the impactstory key, which is passed from the parent 
lab_publist->make_html method because it is associated
with a user, not a paper

Declared by:
lab_publist->import_from_pubmed()
lab_publist->import_from_orcid()
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
class lab_paper {
	public $id_type, $id, $authors, $year, $title, $volume, $issue, $pages, $url, $full_citation;
	/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	string html make_html(string $key) where $key is an impactstory key
	Creates an HTML formatted string based on the properties of a paper.
	Each paper is present as a <p>, with class "publication" and a unique id for CSS styling.
	Could use a list, but formatting looks better this way without doing any CSS 
	(easier for end-users, IMO).
	Each element of the publication (authors, year, title, etc.) is present as a seperate span with a distinct class.
	Called by:
	lab_publist->make_html()
	~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
	function make_html($key = 0){
		$html = '<p class = "impactpubs_publication" id = "'.$this->id.'">';
		if ( isset($this->full_citation) ){
			echo $this->full_citation;
		} else {
			
			//the authors
			if ( isset($this->authors) ) {
				$html .= '<span class = "ip-authors">'.$this->authors.'</span>, ';
			}
			
			//the date (required)
			$html .= '<span class = "ip-date">'.$this->year.'</span>. '; 	
			//the title (required)
			$html .= '<span class = "ip-title">';
			if ($this->url != '') {
				$html .= '<a href = "'.$this->url.'">'.$this->title.'</a>';
			} else {
				$html .= $this->title;
			}
			$html .= '</span> &nbsp';
			
			//the journal
			if ( isset($this->journal) ) {
				$html .= '<span class = "ip-journal">'.$this->journal.'</span>';
				//if both a volume and an issue are present, format as : 152(4):3572-1380
				if ($this->volume && $this->issue && $this->pages) {
					$html .= ' <span class = "ip-vol">'.$this->volume.'('.$this->issue.'):'.$this->pages.'</span>';
				} //if no issue is present, format as 152:3572-1380
				elseif ($this->volume && $this->pages) {
					$html .= ' <span class = "ip-vol">'.$this->volume.':'.$this->pages.'</span>';
				} elseif ($this->volume) {
					$html .= ' <span class = "ip-vol">'.$this->volume.'</span>.';
				} else { //if no volume or issue, assume online publication
					$html .= ".";
				}	
			}
		}
		
		//the impactstory key
		if ($key && $this->id_type != '' && $this->id != '') {
			$html .= '<span class = "impactstory-embed" data-show-logo = "false" data-id = "'.$this->id.'"';
			$html .= 'data-id-type = "'.$this->id_type.'" data-api-key="'.$key.'">';
		}
		$html .= "</p>";
		return $html;
	}
}


/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
string $authors lab_author_format(array $authors)

Called by: 
lab_publist->import_from_pubmed()
lab_publist->import_from_orcid()

Takes an array of author names and returns a nicely formatted string. 
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
function lab_author_format($authors){
	$output = "";
	foreach ($authors as $author){
		$author = trim($author); 
		$output = $output."; ".$author;
	}
	$output = trim($output, ';,');
	return $output;
}

/*~~~~~~~~~~~~~~~~~~~~~~~~~~~
CLIENT-SIDE FORM VALIDATION FUNCTIONS
~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
function validate_name($field){
	if ($field == "") return "You must enter your name<br />";
	return "";
}

function validate_email($field){
	if ($field == "") return "You must enter your email<br />";
	else if (!((strpos($field, ".") > 0) && strpos($field, "@") > 0) || preg_match("/[^a-zA-Z0-9.@_-]/", $field)) return "You must enter a valid email<br />"; //standard email validation line
	return "";
}

function validate_message($field){
	$strip_field = htmlspecialchars($field);
	if ($strip_field != $field){
		return 'Your message may contain HTML tags or other special characters<br />';
	}
	else return '';
}

function validate_honeypot($field){
	if ($field != ''){
		return 'Honeypot must be left blank<br />';
	} else {
		return '';
	}
}


//next 3 functions should be moved into javascript
/*~~~~~~~~~~~~~~~~~~~~~~~~~
string validation lab_validate_pubsource(string $value)
Called by: lab_settings_form()
~~~~~~~~~~~~~~~~~~~~~~~~~*/
function lab_validate_pubsource($value){
	$valid = FALSE;
	if ( $value == 'pubmed' ) $valid = TRUE;
	if ( $value == 'orcid') $valid = TRUE;
	if ( $valid ) return '';
	else return 'Invalid publication source supplied';
}

/*~~~~~~~~~~~~~~~~~~~~~~~~~
string validation lab_validate_identifier(string $value, string $pubsource)
Called by: lab_settings_form()
~~~~~~~~~~~~~~~~~~~~~~~~~*/
function lab_validate_identifier($value, $pubsource = 'orcid'){
	if ( $pubsource == 'orcid' ) {
		//allowed characters are numbers and the dash (-) symbol
		if ( preg_match('/[^0-9A-Za-z\-]/', $value) ) {
			return 'Invalid ORCiD key';
		} else {
			return '';
		}
	} else {
		//for pubmed, just excluding ;, quotes, escape char to prevent injection
		if ( preg_match('/[\;\"\'\\\]/', $value) ) {
			return 'Invalid Pubmed search string';
		} else {
			return '';		
		}
	}
}

/*~~~~~~~~~~~~~~~~~~~~~~~~~
string validation lab_validate_is_key(string $value)
Called by: lab_settings_form()
Letters, numbers, and the - are allowed
~~~~~~~~~~~~~~~~~~~~~~~~~*/
function lab_validate_is_key($value){
	//impactstory key contains only letters, numbers, and the dash (-) symbol
	if ( preg_match('/[^A-Za-z0-9\-]/', $value) ) {
		return 'Invalid ImpactStory API key';	
	} else {
		return '';
	}
}

/*~~~~~~~~~~~~~~~~~~~~~~~~
string is_checked(boolean $name)
Returns HTML to show a checked box if $name is TRUE
Only works for elements that are boolean (checkboxes)
~~~~~~~~~~~~~~~~~~~~~~~~*/
function is_checked($name) {
	if ($name) return ' checked = "checked" ';
	else return '';
}

/*~~~~~~~~~~~~~~~~~~~~~~~
string is_selected(string $select, string $set)
select - the value of the select option
set - the currently set value
If the two strings are equal, returns HTML to change 
the value shown as default in the dropdown
~~~~~~~~~~~~~~~~~~~~~~~*/
function is_selected($select, $set) {
	if ($select == $set) return ' selected = "selected" ';
	else return '';
} 


/*~~~~~~~~~~~~~~~~~~~~~~~~
FOOTLOGGER DEBUG CODE
This code is used to debug 
stuff hooking into the admin
panel.
~~~~~~~~~~~~~~~~~~~~~~~~~*/

$footlogger = array();

function footlogger($logged_data) {
	global $footlogger;
	$footlogger[] = 'test';
	$footlogger[] = $logged_data;
}

add_action('admin_footer', 'footlogger_output');
function footlogger_output() {
	global $footlogger;
	echo 'Log:<br />';
	print_r($footlogger);
}

?>