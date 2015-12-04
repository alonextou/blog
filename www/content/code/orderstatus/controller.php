<!doctype html>
<html class="no-js" lang="en">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Alex Crawford</title>
		<link rel="stylesheet" href="/assets/css/app.css" />
		<link rel="stylesheet" href="/assets/css/vendor/prism.css" />
		<link rel="stylesheet" href="/assets/css/vendor/prism-okaidia.css" />
		<link rel="stylesheet" href="/assets/css/override.css" />
	    <script src="/assets/js/vendor/modernizr.js"></script>
	</head>
	<body>
		<nav id="topnav" class="top-bar" data-topbar role="navigation">
			<ul class="title-area">
				<li class="name">
					<h1><a href="/">Alex Crawford</a></h1>
				</li>
				<li class="toggle-topbar menu-icon">
					<a href="#"><span>Menu</span></a>
				</li>
			</ul>

			<section class="top-bar-section">

				<ul class="right contact_icons hide-for-small">
					<li>
						<a href="http://twitter.com/awc737" target="_blank">
							<i class="fa fa-twitter fa-2x"></i>
						</a>
					</li>
					<li>
						<a href="https://github.com/awc737" target="_blank">
							<i class="fa fa-github fa-2x"></i>
						</a>
					</li>
					<li>
						<a href="http://steamcommunity.com/id/artificialex" target="_blank">
							<i class="fa fa-steam fa-2x fa-spin"></i>
						</a>
					</li>
					<li>
						<a href="https://www.linkedin.com/profile/view?id=104963465&trk=nav_responsive_tab_profile" target="_blank">
							<i class="fa fa-linkedin fa-2x"></i>
						</a>
					</li>
				</ul>

				<ul class="left">
					<li class="">
						<a href="/blog">Blog</a>
					</li>
					<li class="">
						<a href="/projects">Projects</a>
					</li>
					<li class="">
						<a href="/code">Code</a>
					</li>
				</ul>

				<ul class="right mobile_contact_icons show-for-small">
					<div class="icon-bar five-up">
						<a href="#" class="item"><i class="fa fa-twitter fa-2x"></i></a>
						<a href="#" class="item"><i class="fa fa-github fa-2x"></i></a>
						<a href="#" class="item"><i class="fa fa-steam fa-2x fa-spin"></i></a>
						<a href="#" class="item"><i class="fa fa-linkedin fa-2x"></i></a>
						<a href="#" class="item"><i class="fa fa-facebook fa-2x"></i></a>
					</div>
					<!-- That was ugly.
					<form>
						<div class="row">
						<br>
							<div class="large-12 columns">
								<textarea placeholder="Message"></textarea>
							</div>
						</div>
						<div class="row">
							<div class="small-8 columns">
								<div class="row">
									<div class="small-2 columns">
										<span class="label">From:</span>
									</div>
									<div class="small-10 columns">
										<input type="text" id="right-label" placeholder="Email address">
									</div>
								</div>
							</div>
							<div class="small-4 columns">
								<button class="button expand success"><i class="fa fa-send-o"></i> Contact Me!</button>
							</div>
						</div>
					</form>
					<hr>
					-->
				</ul>

			</section>
		</nav>

		<div id="page">
			
			
				<p>&lt;?php</p>
<p>ini_set(&#39;soap.wsdl_cache_enabled&#39;, &#39;0&#39;); 
ini_set(&#39;soap.wsdl_cache_ttl&#39;, &#39;0&#39;); </p>
<p>defined(&#39;_JEXEC&#39;) or die;
require_once JPATH_COMPONENT.&#39;/controller.php&#39;;</p>
<p>class Jw_lookupControllerStatus extends Jw_lookupController
{</p>
<pre><code>public function lookupOrder()
{
    $debug = (isset($_GET[&quot;debug&quot;]) &amp;&amp; $_GET[&#39;debug&#39;] == &#39;true&#39; ) ? true : false;

    if ($debug) {
        ini_set(&quot;display_errors&quot;, 1);
        $input = $_GET;
        echo &#39;&lt;pre&gt;&#39;;
    } else {
        header(&#39;Content-Type: application/json&#39;);
        $input = $_POST;
    }

    $ordernum = $input[&#39;ordernum&#39;];

    $url = &quot;https://ws2.jeld-wen.net/Jw_Thd_Wsi_GetOrderStatusWS?wsdl&quot;;
    $local_cert = &quot;...&quot;;
    $passphrase = &quot;...&quot;;
    $options = array(
        &#39;local_cert&#39; =&gt; $local_cert,
        &#39;passphrase&#39; =&gt; $passphrase,
        &#39;trace&#39; =&gt; 1,
        &#39;cache_wsdl&#39; =&gt; WSDL_CACHE_NONE 
    );

    $parameters = array(
        &quot;OrderNumber&quot; =&gt; $input[&#39;ordernum&#39;]
    );

    $output = array();

    try {
        $client = new SoapClient($url, $options);
        $client-&gt;__setLocation(&#39;https://ws2.jeld-wen.net/Jw_Thd_Wsi_GetOrderStatusWS?wsdl&#39;);
        $response = $client-&gt;getOrderStatus($parameters);

        if($debug){
            $reqXML = $client-&gt;__getLastRequest();
            $respXML = $client-&gt;__getLastResponse();
            echo &#39;&lt;h1&gt;Request&lt;/h1&gt;&#39;;
            echo $reqXML;
            echo &#39;&lt;h1&gt;Response&lt;/h1&gt;&#39;;
            echo $respXML;
            echo &#39;&lt;h1&gt;Output&lt;/h1&gt;&#39;;
            var_dump($response);
        }

        $output[&#39;ordernum&#39;] = $ordernum;
        if($response-&gt;Result == &quot;SUCCESS&quot;) {
            $output[&#39;result&#39;] = &#39;success&#39;;
            $dt = new DateTime($response-&gt;OrderCreateDate);
            $output[&#39;recdate&#39;] = $dt-&gt;format(&#39;m/d/Y&#39;);

            $startdate = $response-&gt;EstimatedDeliveryDateStart-&gt;_;
            $enddate = $response-&gt;EstimatedDeliveryDateEnd-&gt;_;

            if($startdate != &#39;NULL&#39; &amp;&amp; $enddate != &#39;NULL&#39;){
                $startDt = new DateTime($response-&gt;EstimatedDeliveryDateStart-&gt;_);
                $endDt = new DateTime($response-&gt;EstimatedDeliveryDateEnd-&gt;_);
                if($startDt == $endDt){
                    $output[&#39;estdate&#39;] = $endDt-&gt;format(&#39;m/d/Y&#39;);
                } else {
                    $output[&#39;estdate&#39;] = $startDt-&gt;format(&#39;m/d/Y&#39;) . &#39; - &#39; . $endDt-&gt;format(&#39;m/d/Y&#39;);
                }
            } else {
                $output[&#39;estdate&#39;] = &#39;&lt;b class=&quot;error&quot;&gt;Contact Customer Service&lt;/b&gt;&#39;;
                $output[&#39;extra&#39;] = &#39;split&#39;;
            }

            $lastupdate = $response-&gt;LastUpdatedDateTime-&gt;_;
            if($lastupdate != &#39;NULL&#39;){
                $lastupDt = new DateTime($response-&gt;LastUpdatedDateTime-&gt;_);
                $output[&#39;lastupdate&#39;] = $lastupDt-&gt;format(&#39;m/d/Y&#39;);
            } else {
                $output[&#39;lastupdate&#39;] = &#39;&lt;b&gt;N/A.&lt;/b&gt;&#39;;
            }

            $output[&#39;step&#39;] = $this-&gt;getOrderStep($response);

            if ($response-&gt;OrderCanceled == true){
                $output[&#39;extra&#39;] = &#39;cancel&#39;;
            } elseif ($response-&gt;PartialOrder){
                $output[&#39;extra&#39;] = &#39;partial&#39;;
            } elseif ($response-&gt;SplitOrder){
                $output[&#39;extra&#39;] = &#39;split&#39;;
            }

        } else {
            $output[&#39;result&#39;] = &#39;error&#39;;
        }
    } catch(Exception $e) {
        if($debug){
            echo &#39;&lt;h1&gt;Error&lt;/h1&gt;&#39;;
            var_dump($e);
        }
        $output[&#39;result&#39;] = &#39;fail&#39;;
    }

    if($debug){
        echo &#39;&lt;h1&gt;JSON&lt;/h1&gt;&#39;;
        die(json_encode($output));
    } else {
        die(json_encode($output));
    }
}

public function lookupCustomer()
{
    die();
}

public function getOrderStep($response) {
    if ($response-&gt;PreparingOrder == &#39;300&#39;){
        return &#39;1&#39;;
    } elseif ($response-&gt;PreparingOrder == &#39;200&#39;){
        return &#39;2&#39;;
    } elseif ($response-&gt;BuildingOrder == &#39;200&#39;){
        return &#39;3&#39;;
    } elseif ($response-&gt;PackagingOrder == &#39;200&#39;){
        return &#39;4&#39;;
    } elseif ($response-&gt;ShippedOrder == &#39;100&#39;){
        $endDt = new DateTime($response-&gt;EstimatedDeliveryDateEnd-&gt;_);
        $endDate = $endDt-&gt;format(&#39;m/d/Y&#39;);
        $nowDate = date(&#39;m/d/Y&#39;);
        if ($endDate &gt;= $nowDate) {
            return &#39;5&#39;;
        } else {
            return &#39;6&#39;;
        }
    } else {
        return &#39;0&#39;;
    }
}
</code></pre><p>}</p>
			

		</div>

		<div id="footer">
			<div class="row">
				<div id="built_with_title" class="medium-8 columns text-right hide-for-small">
					This site is proudly built using:
				</div>
				<div id="built_with_title_small" class="large-12 columns text-right show-for-small">
					This site is proudly built using:
				</div>
				<div id="built_with" class="medium-4 columns pull-right">
					<div class="icon-bar four-up">
						<a href="http://harpjs.com/" target="_blank" class="item has-tip tip-top" data-tooltip aria-haspopup="true" title="Harp Web Server">
							<img src="/assets/img/vendor/harp.svg">
						</a>
						<a href="http://foundation.zurb.com/" target="_blank" class="item has-tip tip-top" data-tooltip aria-haspopup="true" title="Foundation Zurb 5">
							<img src="/assets/img/vendor/yeti.svg">
						</a>
						<a href="http://bower.io/" target="_blank" class="item has-tip tip-top" data-tooltip aria-haspopup="true" title="Bower Package Manager">
							<img src="/assets/img/vendor/bower.svg">
						</a>
						<a href="http://gruntjs.com/" target="_blank" class="item has-tip tip-top" data-tooltip aria-haspopup="true" title="Grunt Task Runner">
							<img src="/assets/img/vendor/grunt.png">
						</a>
					</div>
				</div>
			</div>
		</div>

		<script src="/assets/js/vendor/jquery.min.js"></script>
		<script src="/assets/js/vendor/foundation.min.js"></script>
		<script src="/assets/js/vendor/slick.min.js"></script>
		<script src="/assets/js/vendor/prism/prism-core.min.js"></script>
		<script src="/assets/js/vendor/prism/prism-php.min.js"></script>
		<script src="/assets/js/vendor/prism/prism-javascript.min.js"></script>
		<script src="/assets/js/app.js"></script>
	</body>
</html>