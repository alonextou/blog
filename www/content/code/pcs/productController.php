<!doctype html>
<html class="no-js" lang="en">
	<head>
		<link href="https://fonts.googleapis.com/css?family=Lato:400,700" rel="stylesheet">
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
						<a href="https://www.linkedin.com/in/awc737" target="_blank">
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
						<a href="http://twitter.com/awc737" class="item"><i class="fa fa-twitter fa-2x"></i></a>
						<a href="https://github.com/awc737" class="item"><i class="fa fa-github fa-2x"></i></a>
						<a href="https://www.linkedin.com/in/awc737" class="item"><i class="fa fa-linkedin fa-2x"></i></a>
					</div>
				</ul>

			</section>
		</nav>

		<div id="page">

			
				<pre><code class="language-php">&lt;?php

class ProductController extends \BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $data[&#39;products&#39;] = Product::all();
        return View::make(&#39;products/index&#39;)-&gt;with($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $data[&#39;product&#39;] = Product::find($id);

        $glassAttribtype = Attribtype::where(&#39;title&#39;, &#39;=&#39;, &#39;Glasstype&#39;)-&gt;first();
        $data[&#39;glassAttributes&#39;] = Attribute::select(DB::raw(&#39;
            id,
            attribtype_id,
            properties-&gt;\&#39;title\&#39; as title,
            properties-&gt;\&#39;thumbnail\&#39; as thumbnail
        &#39;))
        -&gt;where(&#39;attribtype_id&#39;, &#39;=&#39;, $glassAttribtype-&gt;id)
        -&gt;get();

        $colorAttribtype = Attribtype::where(&#39;title&#39;, &#39;=&#39;, &#39;Color&#39;)-&gt;first();
        $data[&#39;colorAttributes&#39;] = Attribute::select(DB::raw(&#39;
            id,
            attribtype_id,
            properties-&gt;\&#39;title\&#39; as title,
            properties-&gt;\&#39;thumbnail\&#39; as thumbnail
        &#39;))
        -&gt;where(&#39;attribtype_id&#39;, &#39;=&#39;, $colorAttribtype-&gt;id)
        -&gt;get();

        return View::make(&#39;products/show&#39;)-&gt;with($data);
    }

    public function process()
    {
        // delete old products
        $products = Product::all();
        foreach ($products as $product)
        {
            $product-&gt;delete();
        }

        $doors = Door::all();

        $glassAttribtype = Attribtype::where(&#39;title&#39;, &#39;=&#39;, &#39;Glasstype&#39;)-&gt;first();
        $glassAttributes = Attribute::select(DB::raw(&#39;
            id
        &#39;))
        -&gt;where(&#39;attribtype_id&#39;, &#39;=&#39;, $glassAttribtype-&gt;id)
        -&gt;lists(&#39;id&#39;);

        $colorAttribtype = Attribtype::where(&#39;title&#39;, &#39;=&#39;, &#39;Color&#39;)-&gt;first();
        $colorAttributes = Attribute::select(DB::raw(&#39;
            id
        &#39;))
        -&gt;where(&#39;attribtype_id&#39;, &#39;=&#39;, $colorAttribtype-&gt;id)
        -&gt;lists(&#39;id&#39;);

        foreach($doors as $door) {
            foreach($door-&gt;configurations as $configuration) {

            $product = new Product;
            $product-&gt;title = $door-&gt;title . &#39; &#39; . $configuration-&gt;title;
            $product-&gt;save();

            $sidelight = $door-&gt;sidelights-&gt;first();

            // process each color $attribute-&gt;id
            foreach($door-&gt;attributes as $colorAttribute) {
                if(in_array($colorAttribute-&gt;id, $colorAttributes)) {
                    $doorColorImages = PHPG_Utils::hstoreToPhp($door-&gt;color_image);
                    $sidelightColorImages = PHPG_Utils::hstoreToPhp($sidelight-&gt;color_image);
                    //foreach($doorColorImages as $id =&gt; $path){
                        //var_dump($id);die;
                        //echo $doorColorImages[$colorAttribute-&gt;id];

                        $imgk_door_original = new Imagick(public_path() . $doorColorImages[$colorAttribute-&gt;id]);
                        $imgk_door_original-&gt;scaleImage(0, 600);
                        $imgk_sidelight_original = new Imagick(public_path() . $sidelightColorImages[$colorAttribute-&gt;id]);
                        $imgk_sidelight_original-&gt;scaleImage(0, 600);

                        //process each glass $attribute-&gt;id
                        foreach($door-&gt;attributes as $glassAttribute) {
                            if(in_array($glassAttribute-&gt;id, $glassAttributes)) {

                                $doorOverlay = Overlay::where(&#39;shape_id&#39;, &#39;=&#39;, $door-&gt;shape_id)
                                    -&gt;where(&#39;attribute_id&#39;, &#39;=&#39;, $glassAttribute-&gt;id)-&gt;first();
                                $sidelightOverlay = Overlay::where(&#39;shape_id&#39;, &#39;=&#39;, $sidelight-&gt;shape_id)
                                    -&gt;where(&#39;attribute_id&#39;, &#39;=&#39;, $glassAttribute-&gt;id)-&gt;first();

                                $imgk_door_glass_original = new Imagick(public_path() . $doorOverlay-&gt;image);
                                $imgk_door_glass_original-&gt;scaleImage(0, 600);
                                $imgk_door_sidelight_original = new Imagick(public_path() . $sidelightOverlay-&gt;image);
                                $imgk_door_sidelight_original-&gt;scaleImage(0, 600);

                                // create door for each color with glass
                                $imgk_door = new Imagick();
                                $width = $imgk_door_original-&gt;getImageWidth();
                                $height = $imgk_door_original-&gt;getImageHeight();
                                $imgk_door-&gt;newImage($width, $height, new ImagickPixel(&quot;white&quot;));
                                $imgk_door-&gt;setImageFormat(&#39;png&#39;);
                                $imgk_door-&gt;compositeImage($imgk_door_original, imagick::COMPOSITE_OVER, 0, 0);
                                $imgk_door-&gt;compositeImage($imgk_door_glass_original, imagick::COMPOSITE_OVER, 0, 0);

                                // create sidelight for each color with glass
                                $imgk_side = new Imagick();
                                $width = $imgk_sidelight_original-&gt;getImageWidth();
                                $height = $imgk_sidelight_original-&gt;getImageHeight();
                                $imgk_side-&gt;newImage($width, $height, new ImagickPixel(&quot;white&quot;));
                                $imgk_side-&gt;setImageFormat(&#39;png&#39;);
                                $imgk_side-&gt;compositeImage($imgk_sidelight_original, imagick::COMPOSITE_OVER, 0, 0);
                                $imgk_side-&gt;compositeImage($imgk_door_sidelight_original, imagick::COMPOSITE_OVER, 0, 0);

                                // TODO: change this to foreach model, not door or window
                                //foreach($door-&gt;configurations as $configuration) {                            

                                    switch ($configuration-&gt;id)
                                    {
                                        case 1: // Single Door
                                            $path = public_path().&#39;/images/products/&#39;;
                                            $filename = $product-&gt;id . &#39;_&#39; . $colorAttribute-&gt;id . &#39;_&#39; . $glassAttribute-&gt;id . &#39;.jpg&#39;;
                                            $imgk_door2 = clone $imgk_door;
                                            $imgk_door2-&gt;setImageFormat(&#39;jpg&#39;);
                                            $imgk_door2-&gt;setCompression(Imagick::COMPRESSION_JPEG);
                                            $imgk_door2-&gt;setCompressionQuality(80);
                                            $imgk_door2-&gt;stripImage();
                                            $imgk_door2-&gt;writeImage($path . $filename);
                                            $product-&gt;defaultimage = &#39;/images/products/&#39; . $filename;
                                            $product-&gt;save();
                                            break;
                                        case 2: // Double Door
                                            $imgk_door2 = clone $imgk_door;
                                            $imgk_door2-&gt;flopImage();

                                            $canvas = new Imagick();
                                            $width = $imgk_door-&gt;getImageWidth() * 2;
                                            $height = $imgk_door-&gt;getImageHeight();
                                            $canvas-&gt;newImage($width, $height, new ImagickPixel(&quot;transparent&quot;));
                                            $canvas-&gt;setImageFormat(&#39;png&#39;);
                                            $canvas-&gt;compositeImage($imgk_door2, imagick::COMPOSITE_OVER, 0, 0);
                                            $canvas-&gt;compositeImage($imgk_door, imagick::COMPOSITE_OVER, $imgk_door-&gt;getImageWidth(), 0);

                                            $canvas-&gt;setImageFormat(&#39;jpg&#39;);
                                            $canvas-&gt;setCompression(Imagick::COMPRESSION_JPEG);
                                            $canvas-&gt;setCompressionQuality(80);
                                            $canvas-&gt;stripImage();

                                            $path = public_path().&#39;/images/products/&#39;;
                                            $filename = $product-&gt;id . &#39;_&#39; . $colorAttribute-&gt;id . &#39;_&#39; . $glassAttribute-&gt;id . &#39;.jpg&#39;;
                                            $canvas-&gt;writeImage($path . $filename);
                                            $product-&gt;defaultimage = &#39;/images/products/&#39; . $filename;
                                            $product-&gt;save();
                                            break;
                                        case 3: // Single Door with Left Sidelight
                                            $canvas = new Imagick();
                                            $width = $imgk_door-&gt;getImageWidth() + $imgk_side-&gt;getImageWidth();
                                            $height = $imgk_door-&gt;getImageHeight();
                                            $canvas-&gt;newImage($width, $height, new ImagickPixel(&quot;transparent&quot;));
                                            $canvas-&gt;setImageFormat(&#39;png&#39;);
                                            $canvas-&gt;compositeImage($imgk_side, imagick::COMPOSITE_OVER, 0, 0);
                                            $canvas-&gt;compositeImage($imgk_door, imagick::COMPOSITE_OVER, $imgk_side-&gt;getImageWidth(), 0);

                                            $canvas-&gt;setImageFormat(&#39;jpg&#39;);
                                            $canvas-&gt;setCompression(Imagick::COMPRESSION_JPEG);
                                            $canvas-&gt;setCompressionQuality(80);
                                            $canvas-&gt;stripImage();

                                            $path = public_path().&#39;/images/products/&#39;;
                                            $filename = $product-&gt;id . &#39;_&#39; . $colorAttribute-&gt;id . &#39;_&#39; . $glassAttribute-&gt;id . &#39;.jpg&#39;;
                                            $canvas-&gt;writeImage($path . $filename);
                                            $product-&gt;defaultimage = &#39;/images/products/&#39; . $filename;
                                            $product-&gt;save();
                                            break;
                                        case 4: // Single Door with Right Sidelight
                                            $canvas = new Imagick();
                                            $width = $imgk_door-&gt;getImageWidth() + $imgk_side-&gt;getImageWidth();
                                            $height = $imgk_door-&gt;getImageHeight();
                                            $canvas-&gt;newImage($width, $height, new ImagickPixel(&quot;transparent&quot;));
                                            $canvas-&gt;setImageFormat(&#39;png&#39;);
                                            $canvas-&gt;compositeImage($imgk_door, imagick::COMPOSITE_OVER, 0, 0);
                                            $canvas-&gt;compositeImage($imgk_side, imagick::COMPOSITE_OVER, $imgk_door-&gt;getImageWidth(), 0);

                                            $canvas-&gt;setImageFormat(&#39;jpg&#39;);
                                            $canvas-&gt;setCompression(Imagick::COMPRESSION_JPEG);
                                            $canvas-&gt;setCompressionQuality(80);
                                            $canvas-&gt;stripImage();

                                            $path = public_path().&#39;/images/products/&#39;;
                                            $filename = $product-&gt;id . &#39;_&#39; . $colorAttribute-&gt;id . &#39;_&#39; . $glassAttribute-&gt;id . &#39;.jpg&#39;;
                                            $canvas-&gt;writeImage($path . $filename);
                                            $product-&gt;defaultimage = &#39;/images/products/&#39; . $filename;
                                            $product-&gt;save();
                                            break;
                                        case 5: // Single Door with Left and Right Sidelight
                                            $canvas = new Imagick();
                                            $width = $imgk_door-&gt;getImageWidth() + ($imgk_side-&gt;getImageWidth() * 2);
                                            $height = $imgk_door-&gt;getImageHeight();
                                            $canvas-&gt;newImage($width, $height, new ImagickPixel(&quot;transparent&quot;));
                                            $canvas-&gt;setImageFormat(&#39;png&#39;);
                                            $canvas-&gt;compositeImage($imgk_side, imagick::COMPOSITE_OVER, 0, 0);
                                            $canvas-&gt;compositeImage($imgk_door, imagick::COMPOSITE_OVER, $imgk_side-&gt;getImageWidth(), 0);
                                            $canvas-&gt;compositeImage($imgk_side, imagick::COMPOSITE_OVER, $imgk_side-&gt;getImageWidth() + $imgk_door-&gt;getImageWidth(), 0);

                                            $canvas-&gt;setImageFormat(&#39;jpg&#39;);
                                            $canvas-&gt;setCompression(Imagick::COMPRESSION_JPEG);
                                            $canvas-&gt;setCompressionQuality(80);
                                            $canvas-&gt;stripImage();

                                            $path = public_path().&#39;/images/products/&#39;;
                                            $filename = $product-&gt;id . &#39;_&#39; . $colorAttribute-&gt;id . &#39;_&#39; . $glassAttribute-&gt;id . &#39;.jpg&#39;;
                                            $canvas-&gt;writeImage($path . $filename);
                                            $product-&gt;defaultimage = &#39;/images/products/&#39; . $filename;
                                            $product-&gt;save();
                                            break;
                                        case 6: // Double Door with Left Sidelight
                                            $imgk_door2 = clone $imgk_door;
                                            $imgk_door2-&gt;flopImage();

                                            $canvas = new Imagick();
                                            $width = ($imgk_door-&gt;getImageWidth() * 2) + $imgk_side-&gt;getImageWidth();
                                            $height = $imgk_door-&gt;getImageHeight();
                                            $canvas-&gt;newImage($width, $height, new ImagickPixel(&quot;transparent&quot;));
                                            $canvas-&gt;setImageFormat(&#39;png&#39;);
                                            $canvas-&gt;compositeImage($imgk_side, imagick::COMPOSITE_OVER, 0, 0);
                                            $canvas-&gt;compositeImage($imgk_door2, imagick::COMPOSITE_OVER, $imgk_side-&gt;getImageWidth(), 0);
                                            $canvas-&gt;compositeImage($imgk_door, imagick::COMPOSITE_OVER, $imgk_side-&gt;getImageWidth() + $imgk_door-&gt;getImageWidth(), 0);

                                            $canvas-&gt;setImageFormat(&#39;jpg&#39;);
                                            $canvas-&gt;setCompression(Imagick::COMPRESSION_JPEG);
                                            $canvas-&gt;setCompressionQuality(80);
                                            $canvas-&gt;stripImage();

                                            $path = public_path().&#39;/images/products/&#39;;
                                            $filename = $product-&gt;id . &#39;_&#39; . $colorAttribute-&gt;id . &#39;_&#39; . $glassAttribute-&gt;id . &#39;.jpg&#39;;
                                            $canvas-&gt;writeImage($path . $filename);
                                            $product-&gt;defaultimage = &#39;/images/products/&#39; . $filename;
                                            $product-&gt;save();
                                            break;
                                        case 7: // Double Door with Right Sidelight
                                            $imgk_door2 = clone $imgk_door;
                                            $imgk_door2-&gt;flopImage();

                                            $canvas = new Imagick();
                                            $width = ($imgk_door-&gt;getImageWidth() * 2) + $imgk_side-&gt;getImageWidth();
                                            $height = $imgk_door-&gt;getImageHeight();
                                            $canvas-&gt;newImage($width, $height, new ImagickPixel(&quot;transparent&quot;));
                                            $canvas-&gt;setImageFormat(&#39;png&#39;);
                                            $canvas-&gt;compositeImage($imgk_door2, imagick::COMPOSITE_OVER, 0, 0);
                                            $canvas-&gt;compositeImage($imgk_door, imagick::COMPOSITE_OVER, $imgk_door-&gt;getImageWidth(), 0);
                                            $canvas-&gt;compositeImage($imgk_side, imagick::COMPOSITE_OVER, $imgk_door-&gt;getImageWidth() * 2, 0);

                                            $canvas-&gt;setImageFormat(&#39;jpg&#39;);
                                            $canvas-&gt;setCompression(Imagick::COMPRESSION_JPEG);
                                            $canvas-&gt;setCompressionQuality(80);
                                            $canvas-&gt;stripImage();

                                            $path = public_path().&#39;/images/products/&#39;;
                                            $filename = $product-&gt;id . &#39;_&#39; . $colorAttribute-&gt;id . &#39;_&#39; . $glassAttribute-&gt;id . &#39;.jpg&#39;;
                                            $canvas-&gt;writeImage($path . $filename);
                                            $product-&gt;defaultimage = &#39;/images/products/&#39; . $filename;
                                            $product-&gt;save();
                                            break;
                                        case 8: // Double Door with Left and Right Sidelight
                                            $imgk_door2 = clone $imgk_door;
                                            $imgk_door2-&gt;flopImage();

                                            $canvas = new Imagick();
                                            $width = ($imgk_door-&gt;getImageWidth() * 2) + ($imgk_side-&gt;getImageWidth() * 2);
                                            $height = $imgk_door-&gt;getImageHeight();
                                            $canvas-&gt;newImage($width, $height, new ImagickPixel(&quot;transparent&quot;));
                                            $canvas-&gt;setImageFormat(&#39;png&#39;);
                                            $canvas-&gt;compositeImage($imgk_side, imagick::COMPOSITE_OVER, 0, 0);
                                            $canvas-&gt;compositeImage($imgk_door2, imagick::COMPOSITE_OVER, $imgk_side-&gt;getImageWidth(), 0);
                                            $canvas-&gt;compositeImage($imgk_door, imagick::COMPOSITE_OVER, $imgk_side-&gt;getImageWidth() + $imgk_door-&gt;getImageWidth(), 0);
                                            $canvas-&gt;compositeImage($imgk_side, imagick::COMPOSITE_OVER, $imgk_side-&gt;getImageWidth() + ($imgk_door-&gt;getImageWidth() * 2), 0);

                                            $canvas-&gt;setImageFormat(&#39;jpg&#39;);
                                            $canvas-&gt;setCompression(Imagick::COMPRESSION_JPEG);
                                            $canvas-&gt;setCompressionQuality(80);
                                            $canvas-&gt;stripImage();

                                            $path = public_path().&#39;/images/products/&#39;;
                                            $filename = $product-&gt;id . &#39;_&#39; . $colorAttribute-&gt;id . &#39;_&#39; . $glassAttribute-&gt;id . &#39;.jpg&#39;;
                                            $canvas-&gt;writeImage($path . $filename);
                                            $product-&gt;defaultimage = &#39;/images/products/&#39; . $filename;
                                            $product-&gt;save();
                                            break;
                                    }
                                //}
                            }
                        }
                    //}
                }
            }}
        }
        return Redirect::action(&#39;DoorController@index&#39;);
    }

    public function clearDatabase()
    {
        DB::table(&#39;attribute_door&#39;)-&gt;delete();
        DB::table(&#39;attribute_sidelight&#39;)-&gt;delete();
        DB::table(&#39;doors&#39;)-&gt;delete();
        DB::table(&#39;sidelights&#39;)-&gt;delete();
        DB::table(&#39;attributes&#39;)-&gt;delete();
        DB::table(&#39;attribute_product&#39;)-&gt;delete();
        DB::table(&#39;configuration_door&#39;)-&gt;delete();
        DB::table(&#39;overlays&#39;)-&gt;delete();
        DB::table(&#39;door_sidelight&#39;)-&gt;delete();
        DB::table(&#39;products&#39;)-&gt;delete();

        return Redirect::action(&#39;DoorController@index&#39;);
    }

}
</code></pre>
			

		</div>

		<div id="footer">
			<div class="row">
				<div id="built_with_title" class="medium-8 columns text-right hide-for-small">
					This site is built with:
				</div>
				<div id="built_with_title_small" class="large-12 columns text-right show-for-small">
					This site is built with:
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
