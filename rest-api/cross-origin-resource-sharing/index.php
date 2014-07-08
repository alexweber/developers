<?php
require_once("../../header.php");
require_once("../header.php");
?>

<h1>Cross-Origin Resource Sharing (CORS)</h1>

<!-- TODO: Is all the CORS information here accurate? -->

<p>The API supports Cross Origin Resource Sharing (CORS) for AJAX requests. You can read the 
<a href="http://www.w3.org/TR/cors">CORS W3C working draft</a>, or 
<a href="http://code.google.com/p/html5security/wiki/CrossOriginRequestSecurity">this intro</a> 
from the HTML 5 Security Guide.</p>

<p>Here's a sample request sent from a browser hitting <strong>http://example.org</strong>:</p>

<p><pre>
<?php echo $apiDomain; ?>/v1/workspaces -H "Origin: http://example.org"
Origin: http://example.org
Access-Control-Request-Header: X-Custom-Header
</pre></p>

<p>At present, all domains are currently accepted. <strong>OPTIONS</strong> preflight requests and 
their subsequent full requests respond with the following headers:</p>

<p><pre>
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: X-Custom-Header
</pre></p>

<p>Note that in the near future, the allowed origins will be limited to those registered with us as 
third-party applications.</p>

<?php
require_once("../footer.php");
require_once("../../footer.php");
?>