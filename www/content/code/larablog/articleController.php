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

			
				<p>&lt;?php</p>
<p>class AdminArticleController extends BaseController {</p>
<pre><code>public function getIndex()
{
    $articles = Article::all();
    return View::make(&#39;admin/articles/index&#39;)
        -&gt;with([&#39;articles&#39; =&gt; $articles]);
}

public function getCreate()
{
    $tags = Tag::all();
    return View::make(&#39;admin/articles/create&#39;)
        -&gt;with([&#39;tags&#39; =&gt; $tags]);
}

public function postCreate()
{
    $input = Input::all();

    $rules = [
        &#39;title&#39; =&gt; &#39;required&#39;
    ];

    $validator = Validator::make($input, $rules);
    if ($validator-&gt;fails()) {
        return Redirect::back()
            -&gt;withErrors($validator)
            -&gt;withInput(Input::all());
    }

    $article = new Article;
    $article-&gt;title = $input[&#39;title&#39;];
    $article-&gt;alias = strtolower(str_replace(&#39; &#39;, &#39;-&#39;, $article-&gt;title));
    $article-&gt;intro = $input[&#39;intro&#39;];
    $article-&gt;content = $input[&#39;content&#39;];
    if (Input::hasFile(&#39;image&#39;))
    {
        $path = public_path() . &#39;/img/articles/&#39;;
        $filename = str_random(10) . &#39;.&#39; . Input::file(&#39;image&#39;)-&gt;getClientOriginalExtension();
        Input::file(&#39;image&#39;)-&gt;move($path, $filename);
        $article-&gt;image = &#39;/img/articles/&#39; . $filename;
    }
    $article-&gt;save();
    if (Input::has(&#39;tags&#39;))
    {
        foreach($input[&#39;tags&#39;] as $id) {
            $tag = Tag::find($id);
            $article-&gt;tags()-&gt;attach($id);
        }
    }


    return Redirect::route(&#39;admin_articles&#39;)
        -&gt;with(&#39;alert&#39;, [&#39;success&#39;, &#39;Article succesfully created.&#39;]);
}

public function getUpdate($id)
{
    $article = Article::find($id)-&gt;toArray();
    return View::make(&#39;admin/articles/update&#39;)
        -&gt;with([&#39;article&#39; =&gt; $article]);
}
</code></pre><p>}</p>
			

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
