<?php
require_once("config.php");
require_once("../../header.php");
require_once("../header.php");
?>

<link href='css/screen.css' media='screen' rel='stylesheet' type='text/css'/>
<link href='css/smoothness/jquery-ui-1.10.3.custom.min.css' media='screen' rel='stylesheet' type='text/css'/>
<script src='lib/jquery-1.8.0.min.js' type='text/javascript'></script>
<script src='lib/jquery-ui-1.10.3.custom.min.js' type='text/javascript'></script>
<script src='lib/jquery-ui-timepicker-addon.js' type='text/javascript'></script>
<script src='lib/jquery.slideto.min.js' type='text/javascript'></script>
<script src='lib/jquery.wiggle.min.js' type='text/javascript'></script>
<script src='lib/jquery.ba-bbq.min.js' type='text/javascript'></script>
<script src='lib/handlebars-1.0.rc.1.js' type='text/javascript'></script>
<script src='lib/underscore-min.js' type='text/javascript'></script>
<script src='lib/highlight.7.3.pack.js' type='text/javascript'></script>
<script src='lib/backbone-min.js' type='text/javascript'></script>
<script src='lib/swagger.js' type='text/javascript'></script>
<script src='swagger-ui.js' type='text/javascript'></script>

<script type="text/javascript">
	$(function () {
	    
	    $('.datetime').live('focus', function(e) {
            $(this).datetimepicker({
                dateFormat: 'yy-mm-dd',
                timeFormat: 'HH:mm:ssz',
                separator: 'T'
            });
        });
        
        $('.date').live('focus', function(e) {
            $(this).datepicker({
                dateFormat: 'yy-mm-dd'
            });
        });
        
        
        $('input.customField').live('change', function() {
            
            $(this).removeClass('error');
            
            var elem = $(this).parent().next().find('input.parameter');
            elem.attr('name', '');
            
            var fieldName = $(this).val();
            
            if (/\s/g.test(fieldName)) {
                $(this).addClass('error');
                return false;
            }
            
            elem.attr('name', fieldName);
            
        });
        
        $('input.customValue').live('change', function() {
            
            var elem = $(this).parent().prev().find('input.parameter');
            
            elem.attr('required', false);
            
            if ($(this).val() != '' && $(this).attr('name') == '') {
                elem.attr('required', true);
                return false;
            }
            
        });
        
        $('.addCustomParam').live('click', function(e) {
            
            var elem = $(this).parent().parent();
            
            var $clone = elem.clone();
            $clone.find(':text').val('');
            $clone.find(':text').attr('name', '');
            $clone.find(':text').removeClass('required');
            $clone.find(':text').removeClass('error');
            elem.after($clone);
            
            e.preventDefault();
            
        });
        
        $('.removeCustomParam').live('click', function(e) {
            
            var elem = $(this).parent().parent();
            
            elem.remove();
            
            e.preventDefault();
            
        });
        
		window.swaggerUi = new SwaggerUi({
			discoveryUrl:"<?php echo $discoveryUrl; ?>",
			apiKey:"<?php echo (empty($apiKey)) ? 'access_token' : $apiKey;?>",
			apiKeyName:"access_token",
			dom_id:"swagger-ui-container",
			supportHeaderParams: false,
			supportedSubmitMethods: ['get', 'post', 'put', 'delete'],
			onComplete: function(swaggerApi, swaggerUi){
				if(console) {
					console.log("Loaded SwaggerUI")
					console.log(swaggerApi);
					console.log(swaggerUi);
				}
			},
			onFailure: function(data) {
				if(console) {
					console.log("Unable to Load SwaggerUI");
					console.log(data);
				}
			},
			docExpansion: "none"
		});

		window.swaggerUi.load();
	});

</script>

<div id="header" class="row">
	<div class="swagger-ui-wrap">
		<form id="api_selector">
			<div class="input"><input placeholder="http://example.com/api" id="input_baseUrl" name="baseUrl" type="text"/></div>
			<div class="input"><input placeholder="access_token" id="input_apiKey" name="access_token" type="text"/></div>
			<div class="input"><a id="explore" href="#">Explore</a></div>
		</form>
	</div>
</div>

<div id="message-bar" class="swagger-ui-wrap">
	&nbsp;
</div>

<div id="swagger-ui-container" class="swagger-ui-wrap">

</div>

<?php
require_once("../footer.php");
require_once("../../footer.php");
?>