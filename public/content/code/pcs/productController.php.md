```php
<?php

class ProductController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$data['products'] = Product::all();
		return View::make('products/index')->with($data);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		$data['product'] = Product::find($id);

		$glassAttribtype = Attribtype::where('title', '=', 'Glasstype')->first();
		$data['glassAttributes'] = Attribute::select(DB::raw('
			id,
			attribtype_id,
			properties->\'title\' as title,
			properties->\'thumbnail\' as thumbnail
		'))
		->where('attribtype_id', '=', $glassAttribtype->id)
		->get();

		$colorAttribtype = Attribtype::where('title', '=', 'Color')->first();
		$data['colorAttributes'] = Attribute::select(DB::raw('
			id,
			attribtype_id,
			properties->\'title\' as title,
			properties->\'thumbnail\' as thumbnail
		'))
		->where('attribtype_id', '=', $colorAttribtype->id)
		->get();

		return View::make('products/show')->with($data);
	}

	public function process()
	{
		// delete old products
		$products = Product::all();
		foreach ($products as $product)
		{
			$product->delete();
		}
		
		$doors = Door::all();

		$glassAttribtype = Attribtype::where('title', '=', 'Glasstype')->first();
		$glassAttributes = Attribute::select(DB::raw('
			id
		'))
		->where('attribtype_id', '=', $glassAttribtype->id)
		->lists('id');

		$colorAttribtype = Attribtype::where('title', '=', 'Color')->first();
		$colorAttributes = Attribute::select(DB::raw('
			id
		'))
		->where('attribtype_id', '=', $colorAttribtype->id)
		->lists('id');

		foreach($doors as $door) {
			foreach($door->configurations as $configuration) {

			$product = new Product;
			$product->title = $door->title . ' ' . $configuration->title;
			$product->save();

			$sidelight = $door->sidelights->first();

			// process each color $attribute->id
			foreach($door->attributes as $colorAttribute) {
				if(in_array($colorAttribute->id, $colorAttributes)) {
					$doorColorImages = PHPG_Utils::hstoreToPhp($door->color_image);
					$sidelightColorImages = PHPG_Utils::hstoreToPhp($sidelight->color_image);
					//foreach($doorColorImages as $id => $path){
						//var_dump($id);die;
						//echo $doorColorImages[$colorAttribute->id];
						
						$imgk_door_original = new Imagick(public_path() . $doorColorImages[$colorAttribute->id]);
						$imgk_door_original->scaleImage(0, 600);
						$imgk_sidelight_original = new Imagick(public_path() . $sidelightColorImages[$colorAttribute->id]);
						$imgk_sidelight_original->scaleImage(0, 600);

						//process each glass $attribute->id
						foreach($door->attributes as $glassAttribute) {
							if(in_array($glassAttribute->id, $glassAttributes)) {
								
								$doorOverlay = Overlay::where('shape_id', '=', $door->shape_id)
									->where('attribute_id', '=', $glassAttribute->id)->first();
								$sidelightOverlay = Overlay::where('shape_id', '=', $sidelight->shape_id)
									->where('attribute_id', '=', $glassAttribute->id)->first();
								
								$imgk_door_glass_original = new Imagick(public_path() . $doorOverlay->image);
								$imgk_door_glass_original->scaleImage(0, 600);
								$imgk_door_sidelight_original = new Imagick(public_path() . $sidelightOverlay->image);
								$imgk_door_sidelight_original->scaleImage(0, 600);

								// create door for each color with glass
								$imgk_door = new Imagick();
								$width = $imgk_door_original->getImageWidth();
								$height = $imgk_door_original->getImageHeight();
								$imgk_door->newImage($width, $height, new ImagickPixel("white"));
								$imgk_door->setImageFormat('png');
								$imgk_door->compositeImage($imgk_door_original, imagick::COMPOSITE_OVER, 0, 0);
								$imgk_door->compositeImage($imgk_door_glass_original, imagick::COMPOSITE_OVER, 0, 0);

								// create sidelight for each color with glass
								$imgk_side = new Imagick();
								$width = $imgk_sidelight_original->getImageWidth();
								$height = $imgk_sidelight_original->getImageHeight();
								$imgk_side->newImage($width, $height, new ImagickPixel("white"));
								$imgk_side->setImageFormat('png');
								$imgk_side->compositeImage($imgk_sidelight_original, imagick::COMPOSITE_OVER, 0, 0);
								$imgk_side->compositeImage($imgk_door_sidelight_original, imagick::COMPOSITE_OVER, 0, 0);

								// TODO: change this to foreach model, not door or window
								//foreach($door->configurations as $configuration) {							

									switch ($configuration->id)
									{
										case 1: // Single Door
											$path = public_path().'/images/products/';
											$filename = $product->id . '_' . $colorAttribute->id . '_' . $glassAttribute->id . '.jpg';
											$imgk_door2 = clone $imgk_door;
											$imgk_door2->setImageFormat('jpg');
											$imgk_door2->setCompression(Imagick::COMPRESSION_JPEG);
											$imgk_door2->setCompressionQuality(80);
											$imgk_door2->stripImage();
											$imgk_door2->writeImage($path . $filename);
											$product->defaultimage = '/images/products/' . $filename;
											$product->save();
											break;
										case 2: // Double Door
											$imgk_door2 = clone $imgk_door;
											$imgk_door2->flopImage();

											$canvas = new Imagick();
											$width = $imgk_door->getImageWidth() * 2;
											$height = $imgk_door->getImageHeight();
											$canvas->newImage($width, $height, new ImagickPixel("transparent"));
											$canvas->setImageFormat('png');
											$canvas->compositeImage($imgk_door2, imagick::COMPOSITE_OVER, 0, 0);
											$canvas->compositeImage($imgk_door, imagick::COMPOSITE_OVER, $imgk_door->getImageWidth(), 0);

											$canvas->setImageFormat('jpg');
											$canvas->setCompression(Imagick::COMPRESSION_JPEG);
											$canvas->setCompressionQuality(80);
											$canvas->stripImage();

											$path = public_path().'/images/products/';
											$filename = $product->id . '_' . $colorAttribute->id . '_' . $glassAttribute->id . '.jpg';
											$canvas->writeImage($path . $filename);
											$product->defaultimage = '/images/products/' . $filename;
											$product->save();
											break;
										case 3: // Single Door with Left Sidelight
											$canvas = new Imagick();
											$width = $imgk_door->getImageWidth() + $imgk_side->getImageWidth();
											$height = $imgk_door->getImageHeight();
											$canvas->newImage($width, $height, new ImagickPixel("transparent"));
											$canvas->setImageFormat('png');
											$canvas->compositeImage($imgk_side, imagick::COMPOSITE_OVER, 0, 0);
											$canvas->compositeImage($imgk_door, imagick::COMPOSITE_OVER, $imgk_side->getImageWidth(), 0);

											$canvas->setImageFormat('jpg');
											$canvas->setCompression(Imagick::COMPRESSION_JPEG);
											$canvas->setCompressionQuality(80);
											$canvas->stripImage();

											$path = public_path().'/images/products/';
											$filename = $product->id . '_' . $colorAttribute->id . '_' . $glassAttribute->id . '.jpg';
											$canvas->writeImage($path . $filename);
											$product->defaultimage = '/images/products/' . $filename;
											$product->save();
											break;
										case 4: // Single Door with Right Sidelight
											$canvas = new Imagick();
											$width = $imgk_door->getImageWidth() + $imgk_side->getImageWidth();
											$height = $imgk_door->getImageHeight();
											$canvas->newImage($width, $height, new ImagickPixel("transparent"));
											$canvas->setImageFormat('png');
											$canvas->compositeImage($imgk_door, imagick::COMPOSITE_OVER, 0, 0);
											$canvas->compositeImage($imgk_side, imagick::COMPOSITE_OVER, $imgk_door->getImageWidth(), 0);

											$canvas->setImageFormat('jpg');
											$canvas->setCompression(Imagick::COMPRESSION_JPEG);
											$canvas->setCompressionQuality(80);
											$canvas->stripImage();

											$path = public_path().'/images/products/';
											$filename = $product->id . '_' . $colorAttribute->id . '_' . $glassAttribute->id . '.jpg';
											$canvas->writeImage($path . $filename);
											$product->defaultimage = '/images/products/' . $filename;
											$product->save();
											break;
										case 5: // Single Door with Left and Right Sidelight
											$canvas = new Imagick();
											$width = $imgk_door->getImageWidth() + ($imgk_side->getImageWidth() * 2);
											$height = $imgk_door->getImageHeight();
											$canvas->newImage($width, $height, new ImagickPixel("transparent"));
											$canvas->setImageFormat('png');
											$canvas->compositeImage($imgk_side, imagick::COMPOSITE_OVER, 0, 0);
											$canvas->compositeImage($imgk_door, imagick::COMPOSITE_OVER, $imgk_side->getImageWidth(), 0);
											$canvas->compositeImage($imgk_side, imagick::COMPOSITE_OVER, $imgk_side->getImageWidth() + $imgk_door->getImageWidth(), 0);

											$canvas->setImageFormat('jpg');
											$canvas->setCompression(Imagick::COMPRESSION_JPEG);
											$canvas->setCompressionQuality(80);
											$canvas->stripImage();

											$path = public_path().'/images/products/';
											$filename = $product->id . '_' . $colorAttribute->id . '_' . $glassAttribute->id . '.jpg';
											$canvas->writeImage($path . $filename);
											$product->defaultimage = '/images/products/' . $filename;
											$product->save();
											break;
										case 6: // Double Door with Left Sidelight
											$imgk_door2 = clone $imgk_door;
											$imgk_door2->flopImage();

											$canvas = new Imagick();
											$width = ($imgk_door->getImageWidth() * 2) + $imgk_side->getImageWidth();
											$height = $imgk_door->getImageHeight();
											$canvas->newImage($width, $height, new ImagickPixel("transparent"));
											$canvas->setImageFormat('png');
											$canvas->compositeImage($imgk_side, imagick::COMPOSITE_OVER, 0, 0);
											$canvas->compositeImage($imgk_door2, imagick::COMPOSITE_OVER, $imgk_side->getImageWidth(), 0);
											$canvas->compositeImage($imgk_door, imagick::COMPOSITE_OVER, $imgk_side->getImageWidth() + $imgk_door->getImageWidth(), 0);

											$canvas->setImageFormat('jpg');
											$canvas->setCompression(Imagick::COMPRESSION_JPEG);
											$canvas->setCompressionQuality(80);
											$canvas->stripImage();

											$path = public_path().'/images/products/';
											$filename = $product->id . '_' . $colorAttribute->id . '_' . $glassAttribute->id . '.jpg';
											$canvas->writeImage($path . $filename);
											$product->defaultimage = '/images/products/' . $filename;
											$product->save();
											break;
										case 7: // Double Door with Right Sidelight
											$imgk_door2 = clone $imgk_door;
											$imgk_door2->flopImage();

											$canvas = new Imagick();
											$width = ($imgk_door->getImageWidth() * 2) + $imgk_side->getImageWidth();
											$height = $imgk_door->getImageHeight();
											$canvas->newImage($width, $height, new ImagickPixel("transparent"));
											$canvas->setImageFormat('png');
											$canvas->compositeImage($imgk_door2, imagick::COMPOSITE_OVER, 0, 0);
											$canvas->compositeImage($imgk_door, imagick::COMPOSITE_OVER, $imgk_door->getImageWidth(), 0);
											$canvas->compositeImage($imgk_side, imagick::COMPOSITE_OVER, $imgk_door->getImageWidth() * 2, 0);

											$canvas->setImageFormat('jpg');
											$canvas->setCompression(Imagick::COMPRESSION_JPEG);
											$canvas->setCompressionQuality(80);
											$canvas->stripImage();

											$path = public_path().'/images/products/';
											$filename = $product->id . '_' . $colorAttribute->id . '_' . $glassAttribute->id . '.jpg';
											$canvas->writeImage($path . $filename);
											$product->defaultimage = '/images/products/' . $filename;
											$product->save();
											break;
										case 8: // Double Door with Left and Right Sidelight
											$imgk_door2 = clone $imgk_door;
											$imgk_door2->flopImage();

											$canvas = new Imagick();
											$width = ($imgk_door->getImageWidth() * 2) + ($imgk_side->getImageWidth() * 2);
											$height = $imgk_door->getImageHeight();
											$canvas->newImage($width, $height, new ImagickPixel("transparent"));
											$canvas->setImageFormat('png');
											$canvas->compositeImage($imgk_side, imagick::COMPOSITE_OVER, 0, 0);
											$canvas->compositeImage($imgk_door2, imagick::COMPOSITE_OVER, $imgk_side->getImageWidth(), 0);
											$canvas->compositeImage($imgk_door, imagick::COMPOSITE_OVER, $imgk_side->getImageWidth() + $imgk_door->getImageWidth(), 0);
											$canvas->compositeImage($imgk_side, imagick::COMPOSITE_OVER, $imgk_side->getImageWidth() + ($imgk_door->getImageWidth() * 2), 0);

											$canvas->setImageFormat('jpg');
											$canvas->setCompression(Imagick::COMPRESSION_JPEG);
											$canvas->setCompressionQuality(80);
											$canvas->stripImage();

											$path = public_path().'/images/products/';
											$filename = $product->id . '_' . $colorAttribute->id . '_' . $glassAttribute->id . '.jpg';
											$canvas->writeImage($path . $filename);
											$product->defaultimage = '/images/products/' . $filename;
											$product->save();
											break;
									}
								//}
							}
						}
					//}
				}
			}}
		}
		return Redirect::action('DoorController@index');
	}

	public function clearDatabase()
	{
		DB::table('attribute_door')->delete();
		DB::table('attribute_sidelight')->delete();
		DB::table('doors')->delete();
		DB::table('sidelights')->delete();
		DB::table('attributes')->delete();
		DB::table('attribute_product')->delete();
		DB::table('configuration_door')->delete();
		DB::table('overlays')->delete();
		DB::table('door_sidelight')->delete();
		DB::table('products')->delete();
		
		return Redirect::action('DoorController@index');
	}

}
```