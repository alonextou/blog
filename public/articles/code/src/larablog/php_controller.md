```php
<?php

class AdminArticleController extends BaseController {

	public function getIndex()
	{
		$articles = Article::all();
		return View::make('admin/articles/index')
			->with(['articles' => $articles]);
	}

	public function getCreate()
	{
		$tags = Tag::all();
		return View::make('admin/articles/create')
			->with(['tags' => $tags]);
	}

	public function postCreate()
	{
		$input = Input::all();

		$rules = [
			'title' => 'required'
		];

		$validator = Validator::make($input, $rules);
		if ($validator->fails()) {
			return Redirect::back()
				->withErrors($validator)
				->withInput(Input::all());
		}

		$article = new Article;
		$article->title = $input['title'];
		$article->alias = strtolower(str_replace(' ', '-', $article->title));
		$article->intro = $input['intro'];
		$article->content = $input['content'];
		if (Input::hasFile('image'))
		{
			$path = public_path() . '/img/articles/';
			$filename = str_random(10) . '.' . Input::file('image')->getClientOriginalExtension();
			Input::file('image')->move($path, $filename);
			$article->image = '/img/articles/' . $filename;
		}
		$article->save();
		if (Input::has('tags'))
		{
			foreach($input['tags'] as $id) {
				$tag = Tag::find($id);
				$article->tags()->attach($id);
			}
		}
		

		return Redirect::route('admin_articles')
			->with('alert', ['success', 'Article succesfully created.']);
	}

	public function getUpdate($id)
	{
		$article = Article::find($id)->toArray();
		return View::make('admin/articles/update')
			->with(['article' => $article]);
	}

}
```