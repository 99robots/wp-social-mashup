$(document).ready(function($){
	window.fbAsyncInit = function() {
	FB.init({
		appId      : facebook_app_id_array.app_id, // App ID
		channelUrl : '//' + facebook_app_id_array.domain + '/channel.html', // Channel File
		status     : true, // check login status
		cookie     : true, // enable cookies to allow the server to access the session
		xfbml      : true  // parse XFBML
	});
	
	// Additional init code here
	
	};
	
	// Load the SDK asynchronously
	(function(d){
	 var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
	 if (d.getElementById(id)) {return;}
	 js = d.createElement('script'); js.id = id; js.async = true;
	 js.src = "//connect.facebook.net/en_US/all.js";
	 ref.parentNode.insertBefore(js, ref);
	}(document));
});