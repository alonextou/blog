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
						<a href="#"><i class="fa fa-twitter fa-2x"></i></a>
					</li>
					<li>
						<a href="#"><i class="fa fa-github fa-2x"></i></a>
					</li>
					<li>
						<a href="#"><i class="fa fa-steam fa-2x fa-spin"></i></a>
					</li>
					<li>
						<a href="#"><i class="fa fa-linkedin fa-2x"></i></a>
					</li>
					<li>
						<a href="#"><i class="fa fa-facebook fa-2x"></i></a>
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
<p>/<em>*
 </em> @version     1.0.0
 <em> @package     com_jw_findstore
 </em> @copyright   JELD-WEN © Copyright 2012. All Rights Reserved.
 <em> @license     GNU General Public License version 2 or later; see LICENSE.txt
 </em> @author      Alex Crawford <a href="&#109;&#97;&#105;&#x6c;&#116;&#x6f;&#x3a;&#97;&#x6c;&#101;&#120;&#x63;&#64;&#x6a;&#x65;&#108;&#x64;&#45;&#119;&#101;&#110;&#46;&#99;&#x6f;&#109;">&#97;&#x6c;&#101;&#120;&#x63;&#64;&#x6a;&#x65;&#108;&#x64;&#45;&#119;&#101;&#110;&#46;&#99;&#x6f;&#109;</a> - JELD-WEN <a href="http://jeld-wen.com">http://jeld-wen.com</a>
 */</p>
<p>header(&#39;Content-Type: application/json&#39;);
defined(&#39;_JEXEC&#39;) or die;
require_once JPATH_COMPONENT.&#39;/controller.php&#39;;</p>
<p>class Jw_findstoreControllerFindstore extends Jw_findstoreController
{</p>
<pre><code>public function search() {

    $mappings = $this-&gt;getModel()-&gt;getMappings();
    $collections = $this-&gt;getModel()-&gt;getCollections();

    $selZip = (empty($_GET[&#39;selZip&#39;]) ? null : $_GET[&#39;selZip&#39;]);
    $selFilters = (empty($_GET[&#39;selFilters&#39;]) ? array() : $_GET[&#39;selFilters&#39;]);
    $selRadius = (empty($_GET[&#39;selRadius&#39;]) ? 40 : $_GET[&#39;selRadius&#39;]);
    $selModel = (empty($_GET[&#39;selModel&#39;]) ? null : $_GET[&#39;selModel&#39;]);

    $url = &#39;https://ws2.jeld-wen.net/sales/GetDealersTest?zip=&#39;. $selZip .&#39;&amp;distance=&#39;. $selRadius .&#39;&amp;collectionId=&#39;. implode(&#39;,&#39;, $selFilters);

    $debug = false;
    if ($debug == true) {
        $url = &#39;tests/findstore.xml&#39;;
        $xml = simplexml_load_file($url);
    } else {
        $ch = curl_init($url);
        $options = array(
            CURLOPT_FOLLOWLOCATION =&gt; true,
            CURLOPT_RETURNTRANSFER =&gt; true,
            CURLOPT_SSLCERT =&gt; JPATH_BASE.&#39;/libraries/fluid/webservices/certs/fluid-cert.pem&#39;,
            CURLOPT_SSLCERTPASSWD =&gt; &#39;gw5yxjx96v&#39;,
            CURLOPT_SSLKEY =&gt; JPATH_BASE.&#39;/libraries/fluid/webservices/certs/fluid-key.pem&#39;,
        );
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $xml = new SimpleXMLElement($response);
    }

    $storeId = 0;
    $dealerGroups = array();
    foreach ($xml-&gt;DealerGroup as $dealerGroup) {

        // HOTFIX: Remove Madison from Home Depot / Lowe&#39;s
        if($selModel == &#39;madison&#39;) {
            $accum = (isset($dealerGroup-&gt;attributes()-&gt;accum) ? $dealerGroup-&gt;attributes()-&gt;accum : null);
            if ($accum == &#39;509&#39; || $accum == &#39;612&#39;){
                continue;
            }
        }

        $dealers = array();
        foreach ($dealerGroup-&gt;Dealer as $dealer) {

            // HOTFIX: Remove Madison from Home Depot / Lowe&#39;s
            if($selModel == &#39;madison&#39;) {
                $name = (isset($dealer-&gt;attributes()-&gt;name) ? $dealer-&gt;attributes()-&gt;name : null);
                if (strpos($name, &quot;LOWE&#39;S&quot;) !== false || strpos($name, &quot;HOME DEPOT&quot;) !== false){
                    continue;
                }
            }

            $products = array();
            $matchAny = false;
            $categories = array();            

            foreach ($dealer-&gt;Product as $product) {
                $productMatch = false;

                foreach($collections as $key =&gt; $val) {
                    if ( $val-&gt;col_id == $product-&gt;attributes()-&gt;number ) {

                        // prepare the first two segments for comparison
                        $productSegments = explode(&quot;.&quot;, $product-&gt;attributes()-&gt;number);
                        if ( count($productSegments) &gt; 1 ) {
                            $productCategory = $productSegments[0].&quot;.&quot;.$productSegments[1];
                        }

                        // prepare the product for highlighting
                        if (in_array($productCategory, $selFilters)) {
                            $productMatch = true;
                        }        

                        // re-set the productCategory to integer so we can easily put it in the right column
                        foreach ( $mappings as $mapping ) {
                            // if no mapping has been set up in the back-end, the product will not be added
                            if ($productCategory == $mapping-&gt;collections ) {
                                $productName = $val-&gt;title;
                                $productNumber = $val-&gt;col_id;
                                $productCategory = $mapping-&gt;category;
                                $products[] = array(
                                    &#39;name&#39; =&gt; $productName,
                                    &#39;number&#39; =&gt; $productNumber,
                                    &#39;category&#39; =&gt; $productCategory,
                                    &#39;match&#39; =&gt; $productMatch,
                                );
                                $categories[] = $mapping-&gt;collections;
                            }
                        }
                    }
                }
            }

            // Make sure the dealer has a match
            foreach ($selFilters as $selFilter) {
                if (in_array($selFilter, $categories)) {
                    $matchAny = true;
                }
            }

            // only return dealers with at least one match, unless search is empty
            if ($matchAny == true || empty($_GET[&#39;selFilters&#39;])) {
                // if a dealer has no products, do not return the dealer
                if ( count($products) &gt; 0 ) {
                    $dealers[] = array(
                        &#39;id&#39; =&gt; $storeId,
                        &#39;name&#39; =&gt; (string)$dealer-&gt;attributes()-&gt;name,
                        &#39;type&#39; =&gt; (string)$dealer-&gt;attributes()-&gt;storeType,
                        &#39;address&#39; =&gt; (string)$dealer-&gt;attributes()-&gt;addrline1,
                        &#39;city&#39; =&gt; (string)$dealer-&gt;attributes()-&gt;city,
                        &#39;state&#39; =&gt; (string)$dealer-&gt;attributes()-&gt;state,
                        &#39;zip&#39; =&gt; (string)$dealer-&gt;attributes()-&gt;zip,
                        &#39;phone&#39; =&gt; (string)$dealer-&gt;attributes()-&gt;phone,
                        &#39;distance&#39; =&gt; (string)$dealer-&gt;attributes()-&gt;distance,
                        &#39;lat&#39; =&gt; (string)$dealer-&gt;attributes()-&gt;lat,
                        &#39;lon&#39; =&gt; (string)$dealer-&gt;attributes()-&gt;lon,
                        &#39;products&#39; =&gt; $products,
                    );
                $storeId = $storeId + 1;
                }
            }
        }

        if (!empty($dealers)) {
            $dealerGroups[] = $dealers;
        }

        if (count($dealerGroups) === 15) {
            break;
        }

    }

    $dealerGroups = json_encode($dealerGroups);
    die($dealerGroups);

}

public function debug() {

    $timeBench03start = microtime(true);

    //$_GET[&#39;selZip&#39;] = &#39;97601&#39;;
    //$_GET[&#39;selFilters&#39;] = array(&#39;1.1&#39;, &#39;1.3&#39;);
    //$_GET[&#39;selRadius&#39;] = &#39;40&#39;;

    if (empty($_GET[&#39;selFilters&#39;])) {
        $_GET[&#39;selFilters&#39;] = array();
    }

    if (empty($_GET[&#39;selZip&#39;])) {
        $_GET[&#39;selZip&#39;] = null;
    }

    if (empty($_GET[&#39;selRadius&#39;])) {
        $_GET[&#39;selRadius&#39;] = &#39;40&#39;;
    }

    $selZip = $_GET[&#39;selZip&#39;];
    $selFilters = $_GET[&#39;selFilters&#39;];
    $selRadius = $_GET[&#39;selRadius&#39;];

    $url = &#39;https://ws2.jeld-wen.net/sales/GetDealersTest?zip=&#39;. $selZip .&#39;&amp;distance=&#39;. $selRadius .&#39;&amp;collectionId=&#39;. $selFilters;
    //$url = &#39;https://webappstest1.jw.local/atlas/GetDealers/report.xml?zip=&#39;. $selZip .&#39;&amp;distance=40&amp;collectionId=&#39;. $selFilters;

    $timeBench01start = microtime(true);

    $ch = curl_init($url);
    $options = array(
        CURLOPT_FOLLOWLOCATION =&gt; true,
        CURLOPT_RETURNTRANSFER =&gt; true,
        CURLOPT_SSLCERT =&gt; JPATH_BASE.&#39;/libraries/fluid/webservices/certs/fluid-cert.pem&#39;,
        CURLOPT_SSLCERTPASSWD =&gt; &#39;gw5yxjx96v&#39;,
        CURLOPT_SSLKEY =&gt; JPATH_BASE.&#39;/libraries/fluid/webservices/certs/fluid-key.pem&#39;,
    );
    curl_setopt_array($ch, $options);

    $response = curl_exec($ch);
    $xml = new SimpleXMLElement($response);

    $timeBench01end = microtime(true);
    $timeBench01 = ($timeBench01end - $timeBench01start);  

    $timeBench02start = microtime(true);

    $mappings = $this-&gt;getModel()-&gt;getMappings();
    $collections = $this-&gt;getModel()-&gt;getCollections();

    $storeId = 0;
    $dealerGroups = array();
    foreach ($xml-&gt;DealerGroup as $dealerGroup) {
        $dealers = array();
        // any product with a mapping and collection in our component&#39;s back end will be returned in the result
        foreach ($dealerGroup-&gt;Dealer as $dealer) {
            $products = array();
            $matchAny = false;
            $categories = array();            

            foreach ($dealer-&gt;Product as $product) {
                $productMatch = false;

                foreach($collections as $key =&gt; $val) {
                    if ( $val-&gt;col_id == $product-&gt;attributes()-&gt;number ) {

                        // prepare the first two segments for comparison
                        $productSegments = explode(&quot;.&quot;, $product-&gt;attributes()-&gt;number);
                        if ( count($productSegments) &gt; 1 ) {
                            $productCategory = $productSegments[0].&quot;.&quot;.$productSegments[1];
                        }

                        // prepare the product for highlighting
                        if (in_array($productCategory, $selFilters)) {
                            $productMatch = true;
                        }        

                        // re-set the productCategory to integer so we can easily put it in the right column
                        foreach ( $mappings as $mapping ) {
                            // if no mapping has been set up in the back-end, the product will not be added
                            if ($productCategory == $mapping-&gt;collections ) {
                                $productName = $val-&gt;title;
                                $productNumber = $val-&gt;col_id;
                                $productCategory = $mapping-&gt;category;
                                $products[] = array(
                                    &#39;name&#39; =&gt; $productName,
                                    &#39;number&#39; =&gt; $productNumber,
                                    &#39;category&#39; =&gt; $productCategory,
                                    &#39;match&#39; =&gt; $productMatch,
                                );
                                $categories[] = $mapping-&gt;collections;
                            }
                        }
                    }
                }
            }

            // Make sure the dealer has a match
            foreach ($selFilters as $selFilter) {
                if (in_array($selFilter, $categories)) {
                    $matchAny = true;
                }
            }

            // only return dealers with at least one match, unless search is empty
            if ($matchAny == true || empty($_GET[&#39;selFilters&#39;])) {
                // if a dealer has no products, do not return the dealer
                if ( count($products) &gt; 0 ) {
                    $dealers[] = array(
                        &#39;id&#39; =&gt; $storeId,
                        &#39;name&#39; =&gt; (string)$dealer-&gt;attributes()-&gt;name,
                        &#39;type&#39; =&gt; (string)$dealer-&gt;attributes()-&gt;storeType,
                        &#39;address&#39; =&gt; (string)$dealer-&gt;attributes()-&gt;addrline1,
                        &#39;city&#39; =&gt; (string)$dealer-&gt;attributes()-&gt;city,
                        &#39;state&#39; =&gt; (string)$dealer-&gt;attributes()-&gt;state,
                        &#39;zip&#39; =&gt; (string)$dealer-&gt;attributes()-&gt;zip,
                        &#39;phone&#39; =&gt; (string)$dealer-&gt;attributes()-&gt;phone,
                        &#39;distance&#39; =&gt; (string)$dealer-&gt;attributes()-&gt;distance,
                        &#39;lat&#39; =&gt; (string)$dealer-&gt;attributes()-&gt;lat,
                        &#39;lon&#39; =&gt; (string)$dealer-&gt;attributes()-&gt;lon,
                        &#39;products&#39; =&gt; $products,
                    );
                $storeId = $storeId + 1;
                }
            }
        }

        if (!empty($dealers)) {
            $dealerGroups[] = $dealers;
        }

    }

    $dealerGroups = json_encode($dealerGroups);

    $timeBench02end = microtime(true);
    $timeBench02 = ($timeBench02end - $timeBench02start);

    $timeBench03end = microtime(true);
    $timeBench03 = ($timeBench03end - $timeBench03start);

    if (empty($_GET[&#39;bench&#39;])) {
        var_dump($url);
        var_dump($xml);
    } else if ($_GET[&#39;bench&#39;] == &#39;1&#39;){
        var_dump(&#39;TIME: &#39; . $timeBench01 . &#39; seconds to return XML from service&#39;);
        var_dump($url);
        var_dump($xml);
    } else if ($_GET[&#39;bench&#39;] == &#39;2&#39;){
        var_dump(&#39;TIME: &#39; . $timeBench02 . &#39; seconds to parse XML into JSON array&#39;);
        var_dump($dealerGroups);
    } else if ($_GET[&#39;bench&#39;] == &#39;all&#39;){
        var_dump(&#39;TIME: &#39; . $timeBench03 . &#39; seconds for script to execute&#39;);
        var_dump($dealerGroups);
    }

    die;

}</code></pre>
<p>}</p>

			

		</div>

		<div id="footer">
			<div class="row">
				<div id="built_with_title" class="medium-8 columns text-right hide-for-small">
					This site is proudly built with:
				</div>
				<div id="built_with_title_small" class="large-12 columns text-right show-for-small">
					This site is proudly built with::
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