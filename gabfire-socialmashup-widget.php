<?php
/*
	Plugin Name: Gabfire Social Mashup Widget
	Plugin URI: http://www.gabfirethemes.com
	Description: Display a mashup of specified social platforms in a sidebar.
	Author: Kyle Benk
	Version: 1.0
	Author URI: http://www.kylebenk.com
	
	Copyright 2013 Gabfire Themes (email : info@gabfire.com)
	
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

/*
*
*	Email account info 
*		username: gabfireplugintwitter@gmail.com
*		password: gabfirePluginTwitter123
*	
*	Twitter account info 
*		username: gabfirePlugin
*		password: gabfirePluginTwitter123
*
*	Twitter Application info
*		access level: read only
*		customer key: ki8ODPfXa1esVIkJTGPi4g
*		customer secret: DEHYJkUyn9kLM5ThYg5Q6HeyPQecgwHJ12dZcq7338
*		request token url: https://api.twitter.com/oauth/request_token
*		authorize url: https://api.twitter.com/oauth/authorize
*		access token url: https://api.twitter.com/oauth/access_token
*		callback url: None
*		sign in with twitter: NO
*
*		access token: 1583775044-wWZJQiaVRB8X1GfcdskLJBy0axdNhjJmTJ7nHcC
*		access secret: UBtX3renAXlL4OcXrjUrJMhvEd3T5s9tXI2ocDj4pU
*		access level: Read-only
*
*
*	Facebook Data
*		App ID:	305376976273363
*		App Secret:	5288b840d992a0d1189b8f12b7403583
*/

/**
 * Library used to get twitter data
 */
require_once ('lib/codebird.php');
include_once('lib/src/facebook.php');

class Gabfire_SocialMashup_Widget extends WP_Widget {
	
	/**
	 *
	 * Register widget with WordPress.
	 *	
 	 */
	public function __construct() {
		parent::__construct(
			'gabfire_socialmashup_widget', // Base ID
			'Gabfire SocialMashup Widget', // Name
			array( 'description' => __( 'Display a mashup of specified social platforms in a sidebar.', 'text_domain' ), ) // Args
		);
	}
	
	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget($args, $instance){
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];
		
		//Twitter
		if ($instance['twitter']){
			if ($instance['twitter_search'] != ''){
				echo $this->gsmw_get_twitter_data($instance,'twitter_search');
			}else if ($instance['twitter_username'] != ''){
				echo $this->gsmw_get_twitter_data($instance,'twitter_username');
			}else if ($instance['twitter_search'] != '' && $instance['twitter_username'] != ''){
				echo 'Twitter: only have the search or username fields filled, not both.';
			}
		}
		
		//Facebook
		if ($instance['facebook']) {
			echo $this->gsmw_get_facebook_data($instance);
		}
		
		//Google
		if ($instance['google']) {
			echo $this->gsmw_get_google_data($instance);
		}
		
		echo $args['after_widget'];
	}
	
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update($new_instance, $old_instance){
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['twitter'] = ( ! empty( $new_instance['twitter'] ) ) ? strip_tags( $new_instance['twitter'] ) : '';
		$instance['facebook'] = ( ! empty( $new_instance['facebook'] ) ) ? strip_tags( $new_instance['facebook'] ) : '';
		$instance['google'] = ( ! empty( $new_instance['google'] ) ) ? strip_tags( $new_instance['google'] ) : '';
		$instance['pinterest'] = ( ! empty( $new_instance['pinterest'] ) ) ? strip_tags( $new_instance['pinterest'] ) : '';
		$instance['linkedin'] = ( ! empty( $new_instance['linkedin'] ) ) ? strip_tags( $new_instance['linkedin'] ) : '';
		
		//Twitter
		$instance['twitter_key'] = ( ! empty( $new_instance['twitter_key'] ) ) ? strip_tags( $new_instance['twitter_key'] ) : '';
		$instance['twitter_secret'] = ( ! empty( $new_instance['twitter_secret'] ) ) ? strip_tags( $new_instance['twitter_secret'] ) : '';
		$instance['twitter_token_key'] = ( ! empty( $new_instance['twitter_token_key'] ) ) ? strip_tags( $new_instance['twitter_token_key'] ) : '';
		$instance['twitter_token_secret'] = ( ! empty( $new_instance['twitter_token_secret'] ) ) ? strip_tags( $new_instance['twitter_token_secret'] ) : '';
		$instance['twitter_search'] = ( ! empty( $new_instance['twitter_search'] ) ) ? strip_tags( $new_instance['twitter_search'] ) : '';
		$instance['twitter_username'] = ( ! empty( $new_instance['twitter_username'] ) ) ? strip_tags( $new_instance['twitter_username'] ) : '';

		//Facebook
		$instance['facebook_key'] = ( ! empty( $new_instance['facebook_key'] ) ) ? strip_tags( $new_instance['facebook_key'] ) : '';
		$instance['facebook_secret'] = ( ! empty( $new_instance['facebook_secret'] ) ) ? strip_tags( $new_instance['facebook_secret'] ) : '';
		$instance['facebook_username'] = ( ! empty( $new_instance['facebook_username'] ) ) ? strip_tags( $new_instance['facebook_username'] ) : '';
		
		return $instance;
	}
	
	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form($instance){
		isset( $instance[ 'title' ] ) ? $title = $instance[ 'title' ] : $title = __( 'Title', 'text_domain' );
		
		isset( $instance[ 'twitter' ] ) ? $twitter = $instance[ 'twitter' ] : $twitter = 0;
		isset( $instance[ 'facebook' ] ) ? $facebook = $instance[ 'facebook' ] : $facebook = 0;
		isset( $instance[ 'google' ] ) ? $google = $instance[ 'google' ] : $google = 0;
		isset( $instance[ 'pinterest' ] ) ? $pinterest = $instance[ 'pinterest' ] : $pinterest = 0;
		isset( $instance[ 'linkedin' ] ) ? $linkedin = $instance[ 'linkedin' ] : $linkedin = 0;
		
		//Twitter
		isset( $instance[ 'twitter_key' ] ) ? $twitter_key = $instance[ 'twitter_key' ] : $twitter_key = '';
		isset( $instance[ 'twitter_secret' ] ) ? $twitter_secret = $instance[ 'twitter_secret' ] : $twitter_secret = '';
		isset( $instance[ 'twitter_token_key' ] ) ? $twitter_token_key = $instance[ 'twitter_token_key' ] : $twitter_token_key = '';
		isset( $instance[ 'twitter_token_secret' ] ) ? $twitter_token_secret = $instance[ 'twitter_token_secret' ] : $twitter_token_secret = '';
		isset( $instance[ 'twitter_search' ] ) ? $twitter_search = $instance[ 'twitter_search' ] : $twitter_search = '';
		isset( $instance[ 'twitter_username' ] ) ? $twitter_username = $instance[ 'twitter_username' ] : $twitter_username = '';
		
		//Facebook
		isset( $instance[ 'facebook_key' ] ) ? $facebook_key = $instance[ 'facebook_key' ] : $facebook_key = '';
		isset( $instance[ 'facebook_secret' ] ) ? $facebook_secret = $instance[ 'facebook_secret' ] : $facebook_secret = '';
		isset( $instance[ 'facebook_username' ] ) ? $facebook_username = $instance[ 'facebook_username' ] : $facebook_username = '';
		
		?>
		<p>
			<h3>Title</h3> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		
		<p>
		<h3>MashUp</h3>
		<table>
			<tr>
				<td>
					<label>Twitter</label>
				</td>
				<td>
				<input id="<?php echo $this->get_field_id( 'twitter' ); ?>" name="<?php echo $this->get_field_name( 'twitter' ); ?>" type="checkbox" <?php if ($twitter) echo 'checked="checked"'; ?> />
				</td>
			</tr>
			
			<tr>
				<td>
					<label>Facebook</label>
				</td>
				<td>
				<input id="<?php echo $this->get_field_id( 'facebook' ); ?>" name="<?php echo $this->get_field_name( 'facebook' ); ?>" type="checkbox" <?php if ($facebook) echo 'checked="checked"'; ?> />
				</td>
			</tr>
			
			<tr>
				<td>
					<label>Google+</label>
				</td>
				<td>
				<input id="<?php echo $this->get_field_id( 'google' ); ?>" name="<?php echo $this->get_field_name( 'google' ); ?>" type="checkbox" <?php if ($google) echo 'checked="checked"'; ?> />
				</td>
			</tr>
			
			<tr>
				<td>
					<label>Pinterest</label>
				</td>
				<td>
				<input id="<?php echo $this->get_field_id( 'pinterest' ); ?>" name="<?php echo $this->get_field_name( 'pinterest' ); ?>" type="checkbox" <?php if ($pinterest) echo 'checked="checked"'; ?> />
				</td>
			</tr>
			
			<tr>
				<td>
					<label>LinkedIn</label>
				</td>
				<td>
				<input id="<?php echo $this->get_field_id( 'linkedin' ); ?>" name="<?php echo $this->get_field_name( 'linkedin' ); ?>" type="checkbox" <?php if ($linkedin) echo 'checked="checked"'; ?> />
				</td>
			</tr>
		</table>
		</p>
		
		<p>
			<h3>Twitter Developer Settings</h3>
			<label>Customer Key</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'twitter_key' ); ?>" name="<?php echo $this->get_field_name( 'twitter_key' ); ?>" type="text" value="<?php echo esc_attr( $twitter_key ); ?>" />
			
			<label>Customer Secret</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'twitter_secret' ); ?>" name="<?php echo $this->get_field_name( 'twitter_secret' ); ?>" type="text" value="<?php echo esc_attr( $twitter_secret ); ?>" />
			
			<label>Access Token</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'twitter_token_key' ); ?>" name="<?php echo $this->get_field_name( 'twitter_token_key' ); ?>" type="text" value="<?php echo esc_attr( $twitter_token_key ); ?>" />
			
			<label>Access Secret</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'twitter_token_secret' ); ?>" name="<?php echo $this->get_field_name( 'twitter_token_secret' ); ?>" type="text" value="<?php echo esc_attr( $twitter_token_secret ); ?>" />
			<label>Click <a href="https://dev.twitter.com/apps" target="_blank">here</a> for details to get this data.</label>
		</p>
		
		<p>
			<h3>Twitter Search Settings</h3>
			<label>Search</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'twitter_search' ); ?>" name="<?php echo $this->get_field_name( 'twitter_search' ); ?>" type="text" value="<?php echo esc_attr( $twitter_search ); ?>" />
			
			<label>Username</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'twitter_username' ); ?>" name="<?php echo $this->get_field_name( 'twitter_username' ); ?>" type="text" value="<?php echo esc_attr( $twitter_username ); ?>" />
		</p>
		
		<p>
			<h3>Facebook Developer Settings</h3>
			<label>Customer Key</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'facebook_key' ); ?>" name="<?php echo $this->get_field_name( 'facebook_key' ); ?>" type="text" value="<?php echo esc_attr( $facebook_key ); ?>" />
			
			<label>Customer Secret</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'facebook_secret' ); ?>" name="<?php echo $this->get_field_name( 'facebook_secret' ); ?>" type="text" value="<?php echo esc_attr( $facebook_secret ); ?>" />
			<label>Click <a href="https://developers.facebook.com/apps" target="_blank">here</a> for details to get this data.</label>
		</p>
		
		<p>
			<h3>Facebook Search Settings</h3>
			<label>User ID</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'facebook_username' ); ?>" name="<?php echo $this->get_field_name( 'facebook_username' ); ?>" type="text" value="<?php echo esc_attr( $facebook_username ); ?>" />
		</p>
		<?php 
		
	}
	
	/**
	 * Used to get twitter data using Codebird
	 *
	 * @param options the use set
	 * @param type of twitter serach (username or hashtag)
	 *
	 * @return JSON array of twitter data
	 */
	private function gsmw_get_twitter_data($options, $type){
		if ($options[$type] == '') {
			return __('Be sure to configure twitter username or search in admin panel','rstw');
		}
		if ($options['twitter_key'] == '' || $options['twitter_secret'] == '' || $options['twitter_token_key'] == '' || $options['twitter_token_secret'] == '') {
			return __('Twitter Authentication data is incomplete','rstw');
		}
		
		if (!class_exists('Codebird')) {
			require_once ('lib/codebird.php');
		}
		
		Codebird::setConsumerKey($options['twitter_key'], $options['twitter_secret']);
		$cb = Codebird::getInstance();
		$cb->setToken($options['twitter_token_key'], $options['twitter_token_secret']);
		$cb->setReturnFormat(CODEBIRD_RETURNFORMAT_ARRAY);
		
		if ($type == 'twitter_search'){
			//Get tweets by hashtag
			
			$reply = get_transient('gabfire_socialmashup_widget_twitter_search_transient');
			
			if (false === $reply){
				try {
					$reply = $cb->search_tweets(array(
								'q'=>'#'.$options['twitter_search'],
								'count'=> 5
						)); 
				} catch (Exception $e) {
					return __('Error retrieving tweets','rstw'); 
				}
				
				if (isset($reply['errors'])) {
					error_log($reply['errors']);
				}
				
				set_transient('gabfire_socialmashup_widget_twitter_transient',$reply,300);
			}
				
			if (empty($reply) or count($reply)<1) {
				return __('No public tweets with' . $reply . ' hashtag','rstw');
			}
		
			$out = '<ul>';
			$i = 0;
			$link_target = 'target="_blank"';
			foreach($reply['statuses'] as $message) {
				if ($i>=5) {
					break;
				}
	
				$msg = $message['text'];
			
				if ($msg=='') {
					continue;
				}
					
				$out .= '<li>';
				$out .= '<img src="'.$message['user']['profile_image_url_https'].'" />';
				
				// match name@address
				$msg = preg_replace('/\b([a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]*\@[a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]{2,6})\b/i',"<a href=\"mailto://$1\" class=\"twitter-link\" ".$link_target.">$1</a>", $msg);
				
				//NEWER mach #trendingtopics
				$msg = preg_replace('/(^|\s)#(\w*[a-zA-Z_]+\w*)/', '\1<a href="http://twitter.com/#!/search/%23\2" class="twitter-link" '.$link_target.'>#\2</a>', $msg);
				
				$msg = preg_replace('/([\.|\,|\:|\¡|\¿|\>|\{|\(]?)@{1}(\w*)([\.|\,|\:|\!|\?|\>|\}|\)]?)\s/i', "$1<a href=\"http://twitter.com/$2\" class=\"twitter-user\" ".$link_target.">@$2</a>$3 ", $msg);
				
				$out .= $msg;       
	                  
				$out .= '</li>';
				$i++;
			}
			$out .= '</ul>';
		}else if ($type == 'twitter_username'){
			//Get user tweets
			$reply = get_transient('gabfire_socialmashup_widget_twitter_username_transient');
			
			if (false === $reply){
				try {
					$twitter_data =  $cb->statuses_userTimeline(array(
								'screen_name'=>$options['twitter_username'], 
								'count'=> 5
						));
				} catch (Exception $e) {
					return __('Error retrieving tweets','rstw'); 
				}
				
				if (isset($reply['errors'])) {
					error_log($reply['errors']);
				}
				
				set_transient('gabfire_socialmashup_widget_twitter_username_transient',$reply,300);
			}
			
			if (empty($twitter_data) or count($twitter_data)<1) {
		    	return __('No public tweets','rstw');
			}
			
			$out = '<ul>';
			$i = 0;
			$link_target = 'target="_blank"';
			foreach($twitter_data as $message) {
	
				// CHECK THE NUMBER OF ITEMS SHOWN
				if ($i>=5) {
					break;
				}
	
				$msg = $message['text'];
			
				if ($msg=='') {
					continue;
				}
					
				$out .= '<li>';
				
				$out .= '<img src="'.$message['user']['profile_image_url_https'].'" />';
				
				// match name@address
				$msg = preg_replace('/\b([a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]*\@[a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]{2,6})\b/i',"<a href=\"mailto://$1\" class=\"twitter-link\" ".$link_target.">$1</a>", $msg);
				//NEW mach #trendingtopics
				$msg = preg_replace('/(^|\s)#(\w*[a-zA-Z_]+\w*)/', '\1<a href="http://twitter.com/#!/search/%23\2" class="twitter-link" '.$link_target.'>#\2</a>', $msg);
				$msg = preg_replace('/([\.|\,|\:|\¡|\¿|\>|\{|\(]?)@{1}(\w*)([\.|\,|\:|\!|\?|\>|\}|\)]?)\s/i', "$1<a href=\"http://twitter.com/$2\" class=\"twitter-user\" ".$link_target.">@$2</a>$3 ", $msg);
				
				$out .= $msg;
			     
				$out .= '</li>';
				$i++;
			}
			$out .= '</ul>';
		
		}
		
		
		return $out;
	}
	
	
	/**
	 * Used to get facebook data using Facebook SDK
	 *
	 * @param options the user set
	 *
	 * @return Activity feed
	 */
	 private function gsmw_get_facebook_data($options){
	 	$facebook = new Facebook(array(
		  	'appId'  => $options['facebook_key'],
		  	'secret' => $options['facebook_secret'],
		));
		
		$user_id = $facebook->getUser();
		
		if ($user_id) {
			try {
				$results = $facebook->api('/me/statuses', 'GET', array('limit' => '5'));
				error_log(serialize($results));
				
				// Give the user a logout link 
				echo '<br /><a href="' . $facebook->getLogoutUrl() . '">logout</a>';
			} catch(FacebookApiException $e) {
				$login_url = $facebook->getLoginUrl( array ('scope' => 'read_stream')); 
				echo 'Please <a href="' . $login_url . '">login.</a>';
				error_log($e->getType());
				error_log($e->getMessage());
			}   
	    }else{
	    	$login_url = $facebook->getLoginUrl( array( 'scope' => 'read_stream' ) );
			echo 'Please <a href="' . $login_url . '">login.</a>';
	    }
	    
	    ?>
	    <script src="//connect.facebook.net/en_US/all.js"></script>
	    <div id="fb-root"></div>
		<?php
		
		$facebook_app_id_array = array(
			'app_id'	=> $options['facebook_key'],
			'domain'	=> $options['facebook_username']
		);
		
		wp_enqueue_script('gabfire_social_mashup_js', pluginsdir(__FILE__) . 'lib/js/gabfire_social_mashup.js');
		wp_localize_script('gabfire_social_mashup_js', 'facebook_app_id_array', $facebook_app_id_array);
        		/*
$access_token = $facebook->getAccessToken();
		error_log($options['facebook_username'] . '/statuses/?access_token=' . $access_token);
        
        $results = $facebook->api($options['facebook_username'] . '/statuses/?access_token=' . $access_token, 'GET', array('limit' => '5'));
        
*/
      
	 }
	 
	 /**
	 * Used to get google+ data
	 *
	 * @param options the user set
	 *
	 * @return 
	 */
	 private function gsmw_get_google_data($options) {
		 
	 }
}

add_action( 'widgets_init', function(){
     register_widget( 'Gabfire_SocialMashup_Widget' );
});

?>