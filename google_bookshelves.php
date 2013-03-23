<?php
/*
Plugin Name: Google Bookshelves
Plugin URI: http://www.adamwadeharris.com/google-bookshelves/
Description: This plugin allows you to put a widget on your site with the books from one of your Google Books Library. You pick the shelf and it automatically will show the most current books on that shelf.
Author: Adam Harris
Version: 2.2
Author URI: http://www.adamwadeharris.com

Copyright 2012  Adam Harris  (email : adam@adamwadeharris.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//This replaces __FILE__ so that it works with symlinks
$file = basename(dirname(__FILE__)).'/'.basename(__FILE__);
$volumeCount;

//register scripts
register_activation_hook($file, 'google_bookshelves_install');
register_deactivation_hook($file, 'google_bookshelves_uninstall');
add_action('admin_menu', 'google_bookshelves_admin_menu_create');
add_action('widgets_init', 'google_bookshelves_widget_init');
add_action('wp_head', 'addStyles');

class google_bookshelves extends WP_Widget{
	function __construct() {
		$widget_options = array(
			'classname' => 'google_bookshelves_widget'
		);
		parent::WP_Widget('google_bookshelves_widget', 'Google Bookshelves Widget', $widget_options);
	}
	
	function widget($args, $instance) {
		extract($args, EXTR_SKIP);
		$google_bookshelves_settings = get_option('google_bookshelves_settings');
		$idNumber = ($google_bookshelves_settings['library_id'])? $google_bookshelves_settings['library_id']: '113720634485746776434';
		$shelf = $instance['shelf'];
		$customShelf = ($instance['customShelf'])? $instance['customShelf']: '-1';
		if ($customShelf != "-1"){
			$shelf = $customShelf;
		}
		$shelfName = ($instance['shelfName'])? $instance['shelfName']: getShelfName($idNumber, $shelf);
		$title = ($instance['title'])? $instance['title']: $shelfName;
		$max = intval(($instance['maxResults'])? $instance['maxResults']: '100');
		$random = ($instance['random'])? $instance['random']: 'false';
		$page_curl = ($instance['page_curl'])? $instance['page_curl']: 'false';
		$image_size = ($instance['image_size'])? $instance['image_size']: 'thumb';
		
		echo $before_widget . $before_title . $title . $after_title;
		echo google_bookshelves($shelf, $max, 'column', $random, $page_curl, $image_size);
		echo $after_widget;
	}
	
	function form($instance) {
		?>
		<p> Make sure you set up your settings by going to Settings -> <a href='options-general.php?page=google-bookshelves/google_bookshelves.php'>Google Bookshelves</a></p>
        <br/>
		<label for="<?php echo $this->get_field_id('title'); ?>">
		Custom Widget Title:
        <br/>
		<input id="<?php echo $this->get_field_id('title'); ?>"
			name="<?php echo $this->get_field_name('title'); ?>"
			value="<?php echo esc_attr($instance['title']); ?>" />
		</label>
        <br/>
		<label for="<?php echo $this->get_field_id('shelf'); ?>">
		Shelf:
        <br/>
		<select id="<?php echo $this->get_field_id('shelf'); ?>"
			name="<?php echo $this->get_field_name('shelf'); ?>">
            <option value="3" <?php if(esc_attr($instance['shelf']) == 3) echo "selected"; ?>>Reading Now</option>
            <option value="2" <?php if(esc_attr($instance['shelf']) == 2) echo "selected"; ?>>To Read</option>
            <option value="4" <?php if(esc_attr($instance['shelf']) == 4) echo "selected"; ?>>Have Read</option>
            <option value="0" <?php if(esc_attr($instance['shelf']) == 0) echo "selected"; ?>>Favorites</option>
         </select>
		</label>
		<br/>
		<label for="<?php echo $this->get_field_id('customShelf'); ?>">
		Or Custom Shelf ID:
        <br/>
		<input id="<?php echo $this->get_field_id('customShelf'); ?>"
			name="<?php echo $this->get_field_name('customShelf'); ?>"
			value="<?php echo esc_attr($instance['customShelf']); ?>" />
		</label>
        <br/>
		<label for="<?php echo $this->get_field_id('maxResults'); ?>">
		Max Number of Books:
        <br/>
		<input id="<?php echo $this->get_field_id('maxResults'); ?>"
			name="<?php echo $this->get_field_name('maxResults'); ?>"
			value="<?php echo esc_attr($instance['maxResults']); ?>" />
		</label>
        <br/>
		<label for="<?php echo $this->get_field_id('random'); ?>">
		Random Order:
        <br/>
		<select id="<?php echo $this->get_field_id('random'); ?>"
			name="<?php echo $this->get_field_name('random'); ?>">
            <option value="false" <?php if(esc_attr($instance['random']) == 'false') echo "selected"; ?>>No</option>
            <option value="true" <?php if(esc_attr($instance['random']) == 'true') echo "selected"; ?>>Yes</option>
         </select>
		</label>
        <br/>
		<label for="<?php echo $this->get_field_id('page_curl'); ?>">
		Page Curl:
        <br/>
		<select id="<?php echo $this->get_field_id('page_curl'); ?>"
			name="<?php echo $this->get_field_name('page_curl'); ?>">
            <option value="false" <?php if(esc_attr($instance['page_curl']) == 'false') echo "selected"; ?>>No</option>
            <option value="true" <?php if(esc_attr($instance['page_curl']) == 'true') echo "selected"; ?>>Yes</option>
         </select>
		</label>
        <br/>
		<label for="<?php echo $this->get_field_id('image_size'); ?>">
		Image Size:
        <br/>
		<select id="<?php echo $this->get_field_id('image_size'); ?>"
			name="<?php echo $this->get_field_name('image_size'); ?>">
            <option value="thumb" <?php if(esc_attr($instance['image_size']) == 'thumb') echo "selected"; ?>>Thumbail</option>
            <option value="smallthumb" <?php if(esc_attr($instance['image_size']) == 'smallthumb') echo "selected"; ?>>Small Thumbnail</option>
         </select>
		</label>
		<?php
	}
}

function google_bookshelves_shortcode($atts) {
	extract( shortcode_atts( array(
		'title' => '',
		'shelf' => '4',
		'max' => '40',
		'layout' => 'grid',
		'random' => 'false',
		'page_curl' => 'false',
		'image_size' => 'smallthumb'
	), $atts ) );
	
	//not dry
	$idNumber = ($google_bookshelves_settings['library_id'])? $google_bookshelves_settings['library_id']: '113720634485746776434';
	$bookTitle = getShelfName($idNumber, $shelf);
	$title = ($title == '')? $bookTitle: $title;
	return "<div class='google_bookshelves'><h3><span>" . $title . "</span></h3>" .google_bookshelves($shelf, $max, $layout, $random, $page_curl, $image_size) . "</div>";;
}
add_shortcode( 'google_bookshelves', 'google_bookshelves_shortcode' );

function google_bookshelves($shelf = '4', $maxResults = '40', $layout = 'grid', $random = 'false', $page_curl = 'false', $image_size = 'smallthumb') {
	global $file;
	$google_bookshelves_settings = get_option('google_bookshelves_settings');
	//not dry
	$idNumber = ($google_bookshelves_settings['library_id'])? $google_bookshelves_settings['library_id']: '113720634485746776434';
	$maxResults = intval($maxResults);
	$output = "";
	
	$books = getBooks($idNumber, $shelf, $maxResults, $random);
	
	if(!empty($books->items)){
		foreach($books->items as $book){
			$image_title = "";
			if(!empty($book)){
				if(!empty($book->volumeInfo->imageLinks->smallThumbnail)) {
					if ($image_size == "smallthumb") {
						$image = $book->volumeInfo->imageLinks->smallThumbnail;
					} elseif($image_size == "thumb") {
						$image = $book->volumeInfo->imageLinks->thumbnail;
					}
					if($page_curl == "false") {
						$image = str_replace("&edge=curl", "&edge=nocurl", $image);
					}
				}else {
					if ($image_size == "smallthumb") {
						$image = plugin_dir_url($file).'images/no_cover_smallthumb.png';
					} elseif($image_size == "thumb") {
						$image = plugin_dir_url($file).'images/no_cover_thumb.png';
						$image_title = "<div class='google_bookshelves_book_title'>" . $book->volumeInfo->title . "</div>";
					}
				}
				
				if(!empty($book->volumeInfo->description)){
					$description = $book->volumeInfo->description;
				}else{
					$description = "No description available";
				}
									
				$results[] = array('title' => $book->volumeInfo->title,
				'image' => $image,
				'imageTitle' => $image_title,
				'infoLink' => $book->volumeInfo->infoLink,
				'authors' => $book->volumeInfo->authors[0],
				'description' => $description,
				);
			}
		}
		
		if($random == "true"){
			shuffle($results);
		}
		
		$counter = 0;
		if($layout == "description") {
			$output .= "<div class='google_bookshelves_shelf_description'>";
			foreach($results as $key => $value) {
				if($counter < $maxResults) {
					$output .= "<div class='google_bookshelves_book'>";
					$output .= "<div class='google_bookshelves_image'><a href='" . $value['infoLink'] . "' target='_blank'>" . $value['imageTitle'] . "<img src='" . $value['image'] . "'/></a></div>";
					$output .= "<div class='google_bookshelves_text'>";
					$output .= "<div class='google_bookshelves_title'>" . $value['title'] . "</div>";
					$output .= "<div class='google_bookshelves_authors'>by " . $value['authors'] . "</div>";
					$output .= "<div class='google_bookshelves_description'>" . $value['description'] . "</div>";
					$output .= "</div></div>";
				}
				$counter++;
			}
		}else if($layout == "grid"){
			$output .= "<div class='google_bookshelves_shelf_grid'>";
			foreach($results as $key=> $value) {
				if($counter < $maxResults) {
					$output .= "<div class='google_bookshelves_book'><a href='" . $value['infoLink'] . "' target='_blank'>" . $value['imageTitle'] . "<img src='" . $value['image'] . "'/></a></div>";
				}
				$counter++;
			}
		}else if ($layout == "column") {
			$output .= "<div class='google_bookshelves_shelf_column'>";
			foreach($results as $key=> $value) {
				if($counter < $maxResults) {
					$output .= "<div class='google_bookshelves_book'><a href='" . $value['infoLink'] . "' target='_blank'>" . $value['imageTitle'] . "<img src='" . $value['image'] . "'/></a></div>";
				}
				$counter++;
			}
		}
		$output .= "</div>";
		if($google_bookshelves_settings['visibility_settings']['show_powered_by']) {
			$output .= "<br/>Plugin by <a href='http://www.adamwadeharris.com' target='_blank'>Adam Harris</a>.<br/><br/>";
		}
	}
	return $output;
}

function google_bookshelves_install() {
	$plugin_options = array(
		'library_id' => '',
		'visibility_settings' => array(
			'show_powered_by' => false
		)
	);
	add_option('google_bookshelves_settings', $plugin_options);

}
function google_bookshelves_uninstall() {
	delete_option('google_bookshelves_settings');
}
function google_bookshelves_admin_menu_create() {
	global $file;
	add_options_page('Google Bookshelves Settings', 'Google Bookshelves', 'administrator', $file, 'google_bookshelves_settings');
}
	
// The plugin admin page
function google_bookshelves_settings() {
	$google_bookshelves_settings = get_option('google_bookshelves_settings');
	$message = '';
	
	if(isset($_POST['google_bookshelves_id'])) {
		$message = 'Settings updated.';
		$id = html_entity_decode($_POST['google_bookshelves_id']);
		// Get the show settings
		$show_powered_by = $_POST['google_bookshelves_show_powered_by'];
		
		$google_bookshelves_settings['visibility_settings']['show_powered_by'] = ($show_powered_by) ? true : false;
		
		$google_bookshelves_settings['library_id'] = $id;
		update_option('google_bookshelves_settings', $google_bookshelves_settings);
	}
	
	$google_bookshelves_settings = get_option('google_bookshelves_settings');
	?>
	
	<div id="icon-options-general" class="icon32"></div>
	<h2>Google Bookshelves Settings</h2>
	
	<?php
	if(strlen($message) > 0) {
	?>
		<div id="message" class="updated">
			<p><strong><?php echo $message; ?></strong></p>
		</div>
	<?php
	}
	?>
	<form method="post" action="">
		<table class="form-table">
			<tr>
				<td></td>
				<td>
					<p>Thank you for using this plugin.</p> 
					<p>In order to use this plugin you need to have a Google account and set up <a href="http://books.google.com">Google Books.</a>
					<br/>Used in collaboration with a mobile app like 
					<a href="https://play.google.com/store/apps/details?id=org.zezi.gb&feature=search_result#?t=W251bGwsMSwyLDEsIm9yZy56ZXppLmdiIl0.">My Library</a>
					you can just scan the barcode of a book<br/>you're reading and see how it appears on your website under e.g. Currently Read.</p>
				</td>
			</tr>		
			<tr>
				<th scope="row"><label for="google_bookshelves_id">Your Google Books ID</label></th>
				<td>
					<input type="text" name="google_bookshelves_id" value="<?php echo stripslashes(htmlentities($google_bookshelves_settings['library_id'])); ?>" />
					<br />
					<span class="description">You can find your Google Books user ID in the URL when you go to one of your shelves in Google Books.
				<br/>The id is displayed after ?uid= in the URL. In the example below it is 113720634485746776434.
				<br/>e.g. http://books.google.co.za/books?uid=<b>113720634485746776434</b>.</span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="google_bookshelves_shelfID">Custom Google Bookshelf ID</label></th>
				<td>
					<p>You can find the ID of your custom bookshelf in the URL when you go to one of your shelves in Google Books.
				<br/>The id is displayed after as_coll= in the URL. In the example below it is 1001.
				<br/>e.g. http://books.google.co.za/books?uid=113720634485746776434&amp;as_coll=<b>1001</b>&amp;source=gbs_lp_bookshelf_list.</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="google_bookshelves_defaultShelfID">Default Google Bookshelf ID's</label></th>
				<td>
					<p>Reading Now: 3
					<br/>To Read: 2
					<br/>Have Read: 4
					<br/>Favorites: 0
					</p>
					
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="google_bookshelves_defaultShelfID">Shortcode Options</label></th>
				<td>
					<p>title: text to show above the books (default is the shelf name)
					<br/>shelf: number of the shelf (default is "4", which is the Have Read shelf)
					<br/>max: maximum number of books to display (default is "40")
					<br/>layout: format for displaying books (default is "grid")
					<br/>random: whether or not it shows in random order (default is "false")
					<br/>page_curl: whether or not it curls the page on the book cover image (default is "false")
					</p>
					
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="google_bookshelves_defaultShelfID">Layout Options</label></th>
				<td>
					<p>description: small thumbnail on the left with title, author, and description on the right
					<br/>grid: grid of small thumbnails
					<br/>column: vertical column of thumbnails like in the widget
					</p>
					
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="google_bookshelves_shortcode">Shortcode</label></th>
				<td>
					<input type="text" name="google_bookshelves_shortcode" size="50" value="[google_bookshelves shelf=&quot;4&quot; max = &quot;5&quot; layout = &quot;description&quot;]" />
					<br />
					<span class="description">Copy and paste this shortcode on any page or post where you want to display your list of books.
					<br/>Remember to change the shelf (shelf ID) and max (maximum number of books to display) to suit your needs. The options for layout are "description" or "grid"</span>
				</td>
			</tr>					
			<tr>
				<th scope="row"><label for="google_bookshelves_show_powered_by">Show Support</label></th>
				<td>
					<input type="checkbox" name="google_bookshelves_show_powered_by" value="true" <?php if($google_bookshelves_settings['visibility_settings']['show_powered_by'] == true) { ?>checked="checked"<?php } ?> />
					<span>Check to show 'Plugin by Adam Harris' in output (if you decide to check it, thank you for your support).</span>
				</td>
			</tr>		
		</table>					
		<p><input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Update options') ?>" /></p>
	</form>
<?php
} //Google Bookshelves settings

function getShelfName($idNumber, $shelf) {
	global $volumeCount;
	$url = "https://www.googleapis.com/books/v1/users/".$idNumber."/bookshelves/".$shelf;
	$bookshelf = json_decode(getAPIRequest($idNumber, $shelf, $url));
	
	$volumeCount = $bookshelf->volumeCount;
	if(!empty($bookshelf)) {
		return $bookshelf->title;
	}
}
function getBooks($idNumber, $shelf, $maxResults, $random) {
	/*fix 40 book limit
    if random == "true" then randomize it and only returns the maxResults
    if maxResults > 40, make sure it combines multiple requests to get the maxResults
    if random == "true" then make sure it's randomizing ALL books on the shelf, not just the maxResults
    
    global $volumeCount;
	if($random == "true") {
		$count = $volumeCount;
	} else {
		$count = intval($maxResults);
	}
	do {
		$data = json_decode(getAPIRequest($idNumber, $shelf, $url));
		$count = $count - 40;
	} while($count > 40)
	if($random == "true"){
		shuffle($results);
	}
	return $data;*/
	$url = "https://www.googleapis.com/books/v1/users/".$idNumber."/bookshelves/".$shelf."/volumes?startIndex=" . 0 . "&maxResults=" . $maxResults;
	$results = getAPIRequest($idNumber, $shelf, $url);
	$results = json_decode($results);
	
	return $results;
}
function google_bookshelves_widget_init() {
	register_widget("google_bookshelves");
}
function addStyles() {
	echo '<link type="text/css" rel="stylesheet" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/google-bookshelves/google_bookshelves_styles.css" />' . "\n";
}
function getAPIRequest($idNumber, $shelf, $url) {
	// Set up cURL
	$ch = curl_init();
	// Set the URL
	curl_setopt($ch, CURLOPT_URL, $url);
	// don't verify SSL certificate
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	// Return the contents of the response as a string
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// Follow redirects
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	
	// Do the request
	$json = curl_exec($ch);
	curl_close($ch);
	return $json;
}

// Add settings link on plugin page
function your_plugin_settings_link($links) {
  $settings_link = '<a href="options-general.php?page=google-bookshelves/google_bookshelves.php">Settings</a>';
  array_unshift($links, $settings_link);
  return $links;
}
$plugin = plugin_basename($file);
add_filter("plugin_action_links_$plugin", 'your_plugin_settings_link' );
?>