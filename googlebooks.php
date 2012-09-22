<?php
/*
Plugin Name: Google Bookshelves
Plugin URI: http://www.adamwadeharris.com/wordpress/plugins/googlebooks
Description: This plugin allows you to put a widget on your site with the books from one of your Google Books Library. You pick the shelf and it automatically will show the most current books on that shelf.
Author: Adam Harris
Version: 1.0
Author URLI: http://www.adamwadeharris.com

Copyright YEAR  PLUGIN_AUTHOR_NAME  (email : PLUGIN AUTHOR EMAIL)

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

class googleBooksWidget extends WP_Widget{
	function googleBooksWidget(){
		$widget_options = array(
			'classname' => 'googleBooks-widget'
		);
		parent::WP_Widget('googleBooks-widget', 'Google Books Widget', $widget_options);
	}
	
	function widget($args, $instance){
		extract($args, EXTR_SKIP);
		$idNumber = ($instance['idNumber'])? $instance['idNumber']: '113720634485746776434';
		$shelf = ($instance['shelf'])? $instance['shelf']: '0';
		$shelfName = ($instance['shelfName'])? $instance['shelfName']: getShelfName($idNumber, $shelf);
		$title = ($instance['title'])? $instance['title']: $shelfName;
		$maxResults = ($instance['maxResults'])? $instance['maxResults']: '10';
		?>
		<aside id="googleBooksWidget" class="widget widget_googleBooks">
        <h1 class="widget-title"><?php echo $title; ?></h1>
        <?php
		$url = "https://www.googleapis.com/books/v1/users/".$idNumber."/bookshelves/".$shelf."/volumes?maxResults=" . $maxResults;
		
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
		
		$books = json_decode($json);
		$element_array = array();
		
		if(!empty($books->items)){
			foreach($books->items as $book){
				if(!empty($book)){
					if(!empty($book->volumeInfo->imageLinks->thumbnail)){
						$imageLink = $book->volumeInfo->imageLinks->thumbnail;
						$results[] = array('title' => $book->volumeInfo->title,
						'imageLink' => $imageLink,
						'infoLink' => $book->volumeInfo->infoLink);
					}else{
						$imageLink = "http://books.google.com/googlebooks/images/no_cover_thumb.gif";
					}
				}
			}
			
			foreach($results as $key => $value){
				echo "<a href='" . $value['infoLink'] . "' target='_blank'><img src='" . $value['imageLink'] . "'/></a> " . "<br/>";
			}
		}
		?>
		</aside>
		<?php
	}
	
	function form($instance){
		?>
		<label for="<?php echo $this->get_field_id('idNumber'); ?>">
		ID Number:
        <br/>
		<input id="<?php echo $this->get_field_id('idNumber'); ?>"
			name="<?php echo $this->get_field_name('idNumber'); ?>"
			value="<?php echo esc_attr($instance['idNumber']); ?>" />
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
		<label for="<?php echo $this->get_field_id('maxResults'); ?>">
		Max Number of Books:
        <br/>
		<input id="<?php echo $this->get_field_id('maxResults'); ?>"
			name="<?php echo $this->get_field_name('maxResults'); ?>"
			value="<?php echo esc_attr($instance['maxResults']); ?>" />
		</label>
		<?php
	}
}

function getShelfName($idNumber, $shelf){
	$url = "https://www.googleapis.com/books/v1/users/".$idNumber."/bookshelves/".$shelf;
	
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
	
	$bookshelf = json_decode($json);
	
	if(!empty($bookshelf)){
		return $bookshelf->title;
	}
}

function googleBooks_widget_init(){
	register_widget("googleBooksWidget");
}
add_action('widgets_init', 'googleBooks_widget_init');
?>