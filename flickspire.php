<?php
/*
Plugin Name: flickspire
Plugin URI: http://wordpress.org/extend/plugins/flickspire/
Description: flickspire movie plugin
Version: 1.0.4
Author: flickspire
Author URI: http://www.flickspire.com

*/


//*********************************
//Global vars
//*********************************
	$movie = 'LifeIsLikeCoffee';
	//$movie = 'Test';
	$custcode = 'LifeSecrets';
	//$custcode = 'Test500';
	$movieTitle = $movie;
	$fbComments = 'on';
	$optinForm = '';


	if (strtolower($_GET["fsid"]) <> ''){
		//hash because it is probably an email address
		$uniqueID = md5(strtolower($_GET["fsid"]));
	}
	elseif (strtolower($_GET["Id"]) <> ''){
		//infusionsoft D&D support
		$uniqueID = strtolower($_GET["Id"]);
	} 
	else {
		//Do Nothing
	}

	$userHostAddress = $_SERVER['REMOTE_ADDR'];

	$shortcode_found = false; // use this flag to see if the shortcode is used in the post
	$cookies;

	//$posttype;



//*********************************
//Create a custom post type for use with movies if desired
//requires single-fs_movie.php to work
//*********************************
//add_action( 'init', 'fs_create_post_type' );
//function fs_create_post_type() {
//	register_post_type( 'fs_movie',
//		array(
//			'labels' => array(
//				'name' => __( 'flickspire movies' ),
//				'singular_name' => __( 'fsmovies' )
//			),
//		'public' => true,
//		'has_archive' => true,
//		)
//	);
//}



//*********************************
//Process the shortcode
//*********************************
    //'movie' => 'LifeIsLikeCoffee',
    //'custcode' => 'LifeSecrets',
//extract(shortcode_atts(array(
//    'movie',
//    'custcode',
//
//), $atts));

function flickspire_movie_shortcode( $atts ) {
    //$GLOBALS[movie] = $atts['movie'];
    //$GLOBALS[custcode] = $atts['custcode'];

    //$fsmovie_page_content = "movie: " . $GLOBALS[movie] . "<br><br>" . "custcode: " . $GLOBALS[custcode] . "<br><br>" . "userhostaddress: " . $GLOBALS[userHostAddress]  . "<br><br>" . "id: " . $GLOBALS[uniqueID];


    $fsmovie_page_content = fsmovie_getContent("");


    //if ($GLOBALS[posttype] == 'fs_movie'){
    //    $fsmovie_page_content = '<table><tr><td>' . $fsmovie_page_content . '</td><td valign="top" style="background-color:#ffffff;">' . fsmovie_getoptinform($fsmovie_page_content) . '</td></tr></table>';
    //}
    $GLOBALS[optinForm] = fsmovie_getoptinform($fsmovie_page_content);

    return $fsmovie_page_content;
}

add_shortcode( 'fsmovie', 'flickspire_movie_shortcode' );









//*********************************
// init: See if the shortcode is used in the post
//*********************************
add_filter('the_posts', 'fsmovie_conditionally_process_functions_if_shortcode_exists'); // the_posts gets triggered before wp_head
function fsmovie_conditionally_process_functions_if_shortcode_exists($posts){



	if (empty($posts)) return $posts;

	if (is_single() Or is_page()){ 


		//$shortcode_found = false; // use this flag to see if styles and scripts need to be enqueued
		foreach ($posts as $post) {
			if (stripos($post->post_content, '[fsmovie') !== false) {
				//get the post type
				//$GLOBALS[posttype] = get_post_type($post);

				//$shortcode_found = true; // bingo!
				$GLOBALS[shortcode_found] = true; // bingo!

				//parsing a querystring, may need to use this instead of shortcode attrs
				//may also need to use content in the shortcode vs. shortcode attr to facilitate
				$shortcodeContent = $post->post_content;
				//extract the shortcode parameters
				$shortcodeContent = substr($shortcodeContent, strpos($shortcodeContent, "[fsmovie]") + 9, strpos($shortcodeContent, "[/fsmovie]") - strpos($shortcodeContent, "[fsmovie]") - 9);
				$shortcodeContent = htmlspecialchars_decode($shortcodeContent);

				parse_str($shortcodeContent, $shortcodeparams);
				$GLOBALS[movie] = $shortcodeparams["movie"];
				$GLOBALS[movieTitle] = $shortcodeparams["movie"];
				$GLOBALS[custcode] = strtolower($shortcodeparams["custcode"]);
				$GLOBALS[fbComments] = $shortcodeparams["fbc"];
				//print_r($shortcodeparams);
	
				break;
			}
		}
 
		if ($GLOBALS[shortcode_found]) {
			// enqueue here
			//head hooks
    			fs_head_hooks();
		}
 
		return $posts;
	}
	else{
		return $posts;

	}

}

//*********************************
//Head hooks
//*********************************
function fs_head_hooks(){
    //main head hooks    
    //echo "fs_head_hooks<br><br>";
    add_filter( 'wp_title', 'fs_custom_title', 0 );
    fs_cookies();
    add_action('wp_enqueue_scripts', 'flickspire_enqueue_scripts');
}





function fs_custom_title( $title ) {
    $title = $GLOBALS[movieTitle];
    return $title;
}


function flickspire_enqueue_scripts() {

	wp_deregister_script('jquery');
	wp_deregister_script('jquerylibs'); 
	wp_register_script('jquerylibs', 'http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js');
	wp_enqueue_script('jquerylibs', 'http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js');

	//deregister scripts from known plugins with conflicts
	//just in case there is something else using this naming convention
	wp_deregister_script('jquery.fancybox');
	wp_deregister_script('jquery.easing');
	wp_deregister_script('jquery.mousewheel');
	//wp-lightbox-ultimate
	wp_deregister_script('fancybox');
	wp_deregister_script('fancybox-easing');
	wp_deregister_script('fancybox-mousewheel');

	wp_register_script('jquery.fancybox', 'http://www.flickspire.com/js/fancybox-1.3.4/jquery.fancybox-1.3.4.js');
	wp_enqueue_script('jquery.fancybox');

	wp_register_script('jquery.easing', 'http://www.flickspire.com/js/fancybox-1.3.4/jquery.easing-1.3.pack.js');
	wp_enqueue_script('jquery.easing');

	wp_register_script('jquery.mousewheel', 'http://www.flickspire.com/js/fancybox-1.3.4/jquery.mousewheel-3.0.4.pack.js');
	wp_enqueue_script('jquery.mousewheel');

}    
 


function fs_cookies(){

	//**************************************************
	// check if is subscriber (and get the cookies)
	//**************************************************

	//
	//NOTE: if no id is set and no cookie is on blog yet viewer will see optin page
	//so blog cookie can only be set by sending the viewer to post with an id
	//once blog cookie is set then viewer will see share on all movies
	//new optins will see optin pop until they revisit from a mailing and pass in an id
	//
	$url = "http://www.flickspire.com/moviesegments/IsSubscriber.aspx?t=" . $GLOBALS[custcode] . "&m=" . $GLOBALS[movie] . "&ip=" . $GLOBALS[userHostAddress] . "&id=" . $GLOBALS[uniqueID];
	//echo ("<!-- " . $url . " -->");

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
	curl_setopt($ch, CURLOPT_HEADER, true);  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 

	//If the cookie is set on the blog, pass it to IsSubscriber.aspx
	//echo $GLOBALS[custcode] . "Subscriber";
	if (isset($_COOKIE[$GLOBALS[custcode] . "Subscriber"])){
	   //echo "cookied" . "<br>";
	   $strCookie = $GLOBALS[custcode] . "Subscriber=" . $_COOKIE[$GLOBALS[custcode] . "Subscriber"] . ";";
	   //echo $strCookie . "<br><br>";
	   curl_setopt($ch, CURLOPT_COOKIE, $strCookie);
	}

	$data = curl_exec($ch);
	//echo $data;
	curl_close($ch);

	preg_match_all('|Set-Cookie: (.*);|U', $data, $cookieCollection);   
	$GLOBALS[cookies] = implode('; ', $cookieCollection[1]);

	//**************************************************
	// /end check if is subscriber
	//**************************************************


	//**************************************************
	// set the same cookies on the blog that we got back from flickspire
	//**************************************************
	foreach ($cookieCollection[1] as $cookie) {
	    //echo "xxxxx";
	    //echo "<br>" . $cookie . "<br>";
	    //echo "xxxxx";
	    $cookiePair = explode('=', $cookie);
	    setcookie($cookiePair[0], $cookiePair[1], time()+60*60*24*365, "/");
	}


	//**************************************************
	// /end set the same cookies we got back from flickspire on the blog
	//**************************************************


}





//*********************************
// replace all content of the post
//*********************************
//function flickspire_content($content) {
//
//  if (strpos($content, '[fsmovie]') > 0){
//    $content = 'put the movie here';
//    return $content;
//  }
//
//}

//add_filter('the_content', 'flickspire');




function fsmovie_getContent($content){
    //$content .= "movie: " . $GLOBALS[movie] . "<br><br>" . "custcode: " . $GLOBALS[custcode] . "<br><br>" . "userhostaddress: " . $GLOBALS[userHostAddress]  . "<br><br>" . "id: " . $GLOBALS[uniqueID];


	//**************************************************
	// movie-player
	//**************************************************

	//don't pass the unique id here.  the ip address inserted into ViralOrNot will be the server and everyone will be treated as subscribers
	//when the movie page attempts to insert the subscriber view it will break out if the unique id is empty
	//the subscriber view was recorded above when we set the cookie
	//we will rely on the cookies to determine subscriber or not
	$url = "http://www.flickspire.com/moviesegments/moviecopy.aspx?t=" . $GLOBALS[custcode] . "&m=" . $GLOBALS[movie] . "&fbc=" . $GLOBALS[fbComments] . "&blog=1";
	$url = $url . "&blogseturl=" . urlencode(plugins_url("", "flickspire") . "/flickspire/setcookie.php?custcode=" . $GLOBALS[custcode]);
	$url = $url . "&permalink=" . urlencode(get_permalink());

	//echo("<!-- ". $url . " -->");




	$ch = curl_init($url);


	curl_setopt($ch,CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	//** Pass the cookies in from IsSubscriber.aspx
	curl_setopt($ch, CURLOPT_COOKIE, $GLOBALS[cookies]);

	$result = curl_exec($ch);

	curl_close($ch);

	//remove doctype declaration and starting head tag
	$result = str_replace( substr($result, 0, strpos($result, "<head") + 17) , "", $result);

	//remove closing head tag and opening body tag
	$result = str_replace( substr($result, strpos($result, "</head>"), strpos($result, "<body>") + 7 - strpos($result, "</body>")) , "", $result);

	//remove closing body and html tag
	$result = str_replace( substr($result, strpos($result, "</body>"), strpos($result, "</html>") + 7 - strpos($result, "</body>")) , "", $result);

	$content = $result;


	return $content;
}


function fsmovie_getoptinform($vsContent){
	//$vsContent = strpos($vsContent, "<!--fsmovie optin form-->") . '???' . strpos($vsContent, "<!--/fsmovie optin form-->");
	$vsContent = substr($vsContent, strpos($vsContent, "<!--fsmovie optin form-->"), strpos($vsContent, "<!--/fsmovie optin form-->") - strpos($vsContent, "<!--fsmovie optin form-->"));
	//$vsContent = substr($vsContent, strpos($vsContent, "<form"), strpos($vsContent, "</form>") - strpos($vsContent, "<form"));
	return $vsContent;
}



?>