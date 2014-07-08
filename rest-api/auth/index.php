<?php
require_once("../../header.php");
require_once("../header.php");
?>

<h1>Auth</h1>

<h2 id="oauth2-overview">OAuth 2 Overview</h2>

<p>The <a href="http://oauth.net/2/">OAuth 2 protocol</a> is used to authenticate and authorize every 
API request. OAuth makes it possible for you to act on behalf of WizeHive users without those users needing 
to share their credentials (username/password). This is made possible via a flow where the end user is 
redirected from your application to WizeHive and grants your application permission to an access 
token for a finite period of time. Additionally, </p>

<p>You do not necessarily need to act on behalf of a user; you can 
<span style="font-style: italic">be</span> the end user. For example, you may be creating a script to 
run every night and extract data from your workspace. In this scenario, you must authenticate via the 
OAuth flow, retrieve a token for your account, and hard code that token into your script.</p>

<h2 id="registering-an-application">Registering an Application</h2>

<p>You can retrieve any API key by <a href="../resources/#!/users/add_users_post_2">creating a user</a>, and then using the access token returned to you to <a href="../resources/#!/users-user.id-clients/add_clients_Clients_post_2">create an API client</a>. Take note of the key that is returned to you. It cannot be retrieved again.</p>

<h3 id="registering-an-application-redirect-uri">Redirect URI</h3>

<p>Your application must specify a redirect URI. This is the URI the end user will return to upon 
authenticating with WizeHive. This URL must be set up to look for an OAuth response and process it 
accordingly.</p>

<h2 id="public-vs-private-resources">Public vs. Private Resources</h2>

<?php require_once("__public_vs_private_resources.php"); ?>

<h2 id="passing-credentials">Passing Credentials</h2>

<?php require_once("__passing_credentials.php"); ?>

<h2 id="3-legged-flows">3-Legged Flows</h2>

<p>A 3-legged flow is require in a scenario where you want to access <strong>private</strong> data 
on behalf of an end user. You may want to make your API calls from a server-side script (Ruby, Python, 
PHP, etc) or from a web client (HTML + JS). Because server-side scripts are implicitly more secure than 
clients, these two scenarios require different flows.</p>

<h3 id="3-legged-flows-server">Server</h3>

<p>The following describes a typical server-side flow:</p>

<ol>
	<li>Your app needs to acquire a token for a user, and sends that user to: 
	<strong><?=$authDomain; ?>/oauth2/v1/authorize?client_id={your API key}&response_type=code&state={a session-based string you use to prevent CSRF}</strong></li>
	<li>The user authenticates with WizeHive and returns to the redirect URI you set up for your 
	application with the following query string appended: 
	<strong>?code={a code which lasts for 30 seconds}&state={the session-based string you passed to prevent CSRF}</strong></li>
	<li>The <strong>state</strong> parameter is checked to ensure it matches the one you passed.</li>
	<li>A <strong>GET</strong> request is made to: <strong><?=$authDomain; ?>/oauth2/v1/grant?client_id={your API key}&client_secret={your API secret}&redirect_uri={the redirect URI associated with your application}&grant_type=authorization_code&code={the code you just received back from WizeHive}</strong></li>
	<li>Assuming the code is valid, you will receive a JSON response back with an access token in it: <pre>{"access_token": "the token", "refresh_token": "a refresh token"}</pre></li>
	<li>A <strong>GET</strong> request is made to: <strong><?=$authDomain; ?>/oauth2/v1/token?access_token={the token}</strong></li>
	<li>Assuming the token is valid, you will receive a JSON response back with an API key in it: <pre>{"client_id": "your API key"}</pre></li>
	<li>The <strong>client_id</strong> (API key) is checked to ensure it is your own.</li>
</ol>

<p>The access token you have now lasts for 24 hours. You can use it to make API requests which require 
authorization.</p>

<p>Once your token expires, you may use your refresh token (provided in Step 5) to retrieve a new 
access token:</p>

<!-- TODO: Is the info on refresh tokens here accurate? -->
<p><pre>
GET <?=$authDomain; ?>/oauth2/v1/grant?client_id={your API key}&client_secret={your API secret}&redirect_uri={the redirect URI associated with your application}&grant_type=refresh_token&refresh_token={the refresh token you previously received from WizeHive}
</pre></p>

<p>A sample server-side script, written in PHP, is provided below:</p>

<p><pre>

require_once 'curl' . DIRECTORY_SEPARATOR . 'curl.php';

$Curl = new Curl();

// This is called once, after we get a code back
if (!empty($_GET['code'])) {

	// Make sure there wasn't an error authenticating
	if (!empty($_GET['error'])) {
		die('Error: ' . $_GET['error']);
	}
	
	// CSRF check
	if ($_GET['state'] !== 'something') {
		die('Naughty, naughty');
	}
	
	// Exchange code for token
	$url = '<?=$authDomain; ?>/oauth2/v1/grant';
	$result = $Curl->post($url, array(
		'code' => $_GET['code'],
		'grant_type' => 'authorization_code',
		'client_id' => 'demo-server-flow-key',
		'client_secret' => 'secret',
		'redirect_uri' => 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
	));
	
	$body = $result->body;
	$data = json_decode($body, true);
	
	if (empty($data)) {
		die('Error retrieving token data');
	}
	
	$access_token = $data['access_token'];
	
	echo 'Token data from auth endpoint ' . $url . ':&lt;br /&gt;';
	echo '&lt;pre&gt;' . print_r($data, true) . '&lt;/pre&gt;';
	
	echo '&lt;br /&gt;&lt;br /&gt;';
	
	// Verify token
	$url = '<?=$authDomain; ?>/oauth2/v1/token';
	$result = $Curl->get($url, array(
		'access_token' => $access_token
	));
	
	$body = $result->body;
	$data = json_decode($body, true);
	
	if (empty($data)) {
		die('Error verifying token');
	}
	
	if ($data['client_id'] !== 'demo-server-flow-key') {
		die('Naughty, naughty');
	}
	
	echo 'Token verification from auth endpoint ' . $url . ':&lt;br /&gt;';
	echo '&lt;pre&gt;' . print_r($data, true) . '&lt;/pre&gt;';
	
	// Access a resource which gives back data about the authorized User ID
	$url = '<?php echo $apiDomain; ?>/v1/users/me';
	$result = $Curl->get($url, array(
		'access_token' => $access_token
	));
	
	$body = $result->body;
	$data = json_decode($body, true);
	
	if (empty($data)) {
		die('Error accessing resource');
	}
	
	echo 'User data from API resource ' . $url . ':&lt;br /&gt;';
	echo '&lt;pre&gt;' . print_r($data, true) . '&lt;/pre&gt;';
	
	die();
	
	echo '&lt;a href="<?=$authDomain; ?>/oauth2/v1/authorize?client_id=demo-server-flow-key&response_type=code&state=something"&gt;Log in&lt;/a&gt;';

}

</pre></p>

<h3 id="3-legged-flows-client">Client</h3>

<p>The following describes a typical client-side flow:</p>

<ol>
	<li>Your app needs to acquire a token for a user, and sends that user to: 
	<strong><?=$authDomain; ?>/oauth2/v1/authorize?client_id={your API key}&response_type=token&state={a session-based string you use to prevent CSRF}</strong></li>
	<li>The user authenticates with WizeHive and returns to the redirect URI you set up for your 
	application with the following string appended after a # hashbang: 
	<strong>?access_token={the token}&state={the session-based string you passed to prevent CSRF}</strong></li>
	<li>JavaScript is used to parse the parameters after the # hashbang.</li>
	<li>The <strong>state</strong> parameter is checked to ensure it matches the one you passed.</li>
	<li>A <strong>GET</strong> request is made to: <strong><?=$authDomain; ?>/oauth2/v1/token?access_token={the token}</strong></li>
	<li>Assuming the token is valid, you will receive a JSON response back with an API key in it: <pre>{"client_id": "your API key"}</pre></li>
	<li>The <strong>client_id</strong> (API key) is checked to ensure it is your own.</li>
</ol>

<p>The access token you have now lasts for 1 hour. You can use it to make API requests which require 
authorization.</p>

<p>Once your token expires, you must send the user back to WizeHive to retrieve a new token.</p>

<p>A sample client-side script is provided below:</p>

<p><pre>

&lt;!DOCTYPE html&gt;
&lt;html&gt;
	&lt;head&gt;
		&lt;title&gt;&lt;/title&gt;
		&lt;meta http-equiv="Content-Type" content="text/html; charset=UTF-8"&gt;
		&lt;script type="text/javascript"&gt;
			
			var params = {}, queryString = location.hash.substring(1),
				regex = /([^&=]+)=([^&]*)/g, m;
			while (m = regex.exec(queryString)) {
				params[decodeURIComponent(m[1])] = decodeURIComponent(m[2]);
			}
			
			if (Object.keys(params).length > 0) { // doesn't work in all browsers. just for demo purposes.
				
				if (params['state'] != 'something') {
					
					console.log('state does not match what we sent. request was probably forged.');
					
				} else {
				
					var req = new XMLHttpRequest();
					
					req.open('GET', '<?=$authDomain; ?>/oauth2/v1/token?access_token=' + params['access_token']);
					
					req.onreadystatechange = function (e) {
						if (req.readyState == 4) {
							if(req.status == 200){
								
								console.log(req.response);
								
//								store the value of "access_token" in local storage. access tokens expire
//								every hour. 
								
							} else if(req.status == 400) {
								
								console.log('error');
								
							} else {
								
								console.log('who knows...');
								
							}
						}
					};
					req.send(null);
				
				}
				
			}
			
		&lt;/script&gt;
	&lt;/head&gt;
	&lt;body&gt;
		
		&lt;a href="<?=$authDomain; ?>/oauth2/v1/authorize?client_id=demo-client-flow-key&response_type=token&state=something"&gt;Log in&lt;/a&gt;
		
	&lt;/body&gt;
&lt;/html&gt;

</pre></p>

<h2 id="authorizations-api">Authorizations API</h2>

<p>An endpoint for <a href="/rest-api/resources/#!/authorizations">Authorizations</a> 
is made available in the API. This makes it possible for you to handle a user's third party application 
authorizations on behalf of that user. This is primarily provided as a way to allow your own application 
to remove itself from the user's authorizations list. This comes in handy if the user asks your 
application to disconnect from WizeHive.</p>

<?php
require_once("../footer.php");
require_once("../../footer.php");
?>
