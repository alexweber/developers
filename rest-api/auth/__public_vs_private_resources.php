<p>There are two types of requests you may want to make, both requiring different sets of credentials:</p>

<ol>
	<li><strong>Public:</strong> A request made by your application for publicly accessible data. 
	Often referred to as 2-legged auth because there are 2 parties involved: the API and your application.</li>
	<li><strong>Private:</strong> A request made by your application on behalf of a third-party user.
	Often referred to as 3-legged auth because there are 3 parties involved: the API, your application, and 
	the user your application is acting on behalf of.</li>
</ol>

<p>Most <a href="/rest-api/resources">resources</a> in the WizeHive API are <strong>private</strong>.</p>