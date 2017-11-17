```php
<?php

/**
 * @version     1.0.0
 * @package     com_jw_findstore
 * @copyright   JELD-WEN © Copyright 2012. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Alex Crawford <alexc@jeld-wen.com> - JELD-WEN http://jeld-wen.com
 */
 
header('Content-Type: application/json');
defined('_JEXEC') or die;
require_once JPATH_COMPONENT.'/controller.php';

class Jw_findstoreControllerFindstore extends Jw_findstoreController
{

	public function search() {

		$mappings = $this->getModel()->getMappings();
		$collections = $this->getModel()->getCollections();

		$selZip = (empty($_GET['selZip']) ? null : $_GET['selZip']);
		$selFilters = (empty($_GET['selFilters']) ? array() : $_GET['selFilters']);
		$selRadius = (empty($_GET['selRadius']) ? 40 : $_GET['selRadius']);
		$selModel = (empty($_GET['selModel']) ? null : $_GET['selModel']);

		$url = 'https://ws2.jeld-wen.net/sales/GetDealersTest?zip='. $selZip .'&distance='. $selRadius .'&collectionId='. implode(',', $selFilters);

		$debug = false;
		if ($debug == true) {
			$url = 'tests/findstore.xml';
			$xml = simplexml_load_file($url);
		} else {
			$ch = curl_init($url);
			$options = array(
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSLCERT => JPATH_BASE.'/libraries/fluid/webservices/certs/fluid-cert.pem',
				CURLOPT_SSLCERTPASSWD => 'gw5yxjx96v',
				CURLOPT_SSLKEY => JPATH_BASE.'/libraries/fluid/webservices/certs/fluid-key.pem',
			);
			curl_setopt_array($ch, $options);
			$response = curl_exec($ch);
			$xml = new SimpleXMLElement($response);
		}

		$storeId = 0;
		$dealerGroups = array();
		foreach ($xml->DealerGroup as $dealerGroup) {

			// HOTFIX: Remove Madison from Home Depot / Lowe's
			if($selModel == 'madison') {
				$accum = (isset($dealerGroup->attributes()->accum) ? $dealerGroup->attributes()->accum : null);
				if ($accum == '509' || $accum == '612'){
                    continue;
                }
			}

			$dealers = array();
			foreach ($dealerGroup->Dealer as $dealer) {

				// HOTFIX: Remove Madison from Home Depot / Lowe's
				if($selModel == 'madison') {
					$name = (isset($dealer->attributes()->name) ? $dealer->attributes()->name : null);
					if (strpos($name, "LOWE'S") !== false || strpos($name, "HOME DEPOT") !== false){
                    	continue;
					}
				}

				$products = array();
				$matchAny = false;
				$categories = array();			

				foreach ($dealer->Product as $product) {
					$productMatch = false;

					foreach($collections as $key => $val) {
						if ( $val->col_id == $product->attributes()->number ) {

							// prepare the first two segments for comparison
							$productSegments = explode(".", $product->attributes()->number);
							if ( count($productSegments) > 1 ) {
								$productCategory = $productSegments[0].".".$productSegments[1];
							}

							// prepare the product for highlighting
							if (in_array($productCategory, $selFilters)) {
								$productMatch = true;
							}		

							// re-set the productCategory to integer so we can easily put it in the right column
							foreach ( $mappings as $mapping ) {
								// if no mapping has been set up in the back-end, the product will not be added
								if ($productCategory == $mapping->collections ) {
									$productName = $val->title;
									$productNumber = $val->col_id;
									$productCategory = $mapping->category;
									$products[] = array(
										'name' => $productName,
										'number' => $productNumber,
										'category' => $productCategory,
										'match' => $productMatch,
									);
									$categories[] = $mapping->collections;
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
				if ($matchAny == true || empty($_GET['selFilters'])) {
					// if a dealer has no products, do not return the dealer
					if ( count($products) > 0 ) {
						$dealers[] = array(
							'id' => $storeId,
							'name' => (string)$dealer->attributes()->name,
							'type' => (string)$dealer->attributes()->storeType,
							'address' => (string)$dealer->attributes()->addrline1,
							'city' => (string)$dealer->attributes()->city,
							'state' => (string)$dealer->attributes()->state,
							'zip' => (string)$dealer->attributes()->zip,
							'phone' => (string)$dealer->attributes()->phone,
							'distance' => (string)$dealer->attributes()->distance,
							'lat' => (string)$dealer->attributes()->lat,
							'lon' => (string)$dealer->attributes()->lon,
							'products' => $products,
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

		//$_GET['selZip'] = '97601';
		//$_GET['selFilters'] = array('1.1', '1.3');
		//$_GET['selRadius'] = '40';

		if (empty($_GET['selFilters'])) {
			$_GET['selFilters'] = array();
		}

		if (empty($_GET['selZip'])) {
			$_GET['selZip'] = null;
		}

		if (empty($_GET['selRadius'])) {
			$_GET['selRadius'] = '40';
		}

		$selZip = $_GET['selZip'];
		$selFilters = $_GET['selFilters'];
		$selRadius = $_GET['selRadius'];

		$url = 'https://ws2.jeld-wen.net/sales/GetDealersTest?zip='. $selZip .'&distance='. $selRadius .'&collectionId='. $selFilters;
		//$url = 'https://webappstest1.jw.local/atlas/GetDealers/report.xml?zip='. $selZip .'&distance=40&collectionId='. $selFilters;

		$timeBench01start = microtime(true);

		$ch = curl_init($url);
		$options = array(
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSLCERT => JPATH_BASE.'/libraries/fluid/webservices/certs/fluid-cert.pem',
			CURLOPT_SSLCERTPASSWD => 'gw5yxjx96v',
			CURLOPT_SSLKEY => JPATH_BASE.'/libraries/fluid/webservices/certs/fluid-key.pem',
		);
		curl_setopt_array($ch, $options);

		$response = curl_exec($ch);
		$xml = new SimpleXMLElement($response);

		$timeBench01end = microtime(true);
		$timeBench01 = ($timeBench01end - $timeBench01start);  

		$timeBench02start = microtime(true);

		$mappings = $this->getModel()->getMappings();
		$collections = $this->getModel()->getCollections();

		$storeId = 0;
		$dealerGroups = array();
		foreach ($xml->DealerGroup as $dealerGroup) {
			$dealers = array();
			// any product with a mapping and collection in our component's back end will be returned in the result
			foreach ($dealerGroup->Dealer as $dealer) {
				$products = array();
				$matchAny = false;
				$categories = array();			

				foreach ($dealer->Product as $product) {
					$productMatch = false;

					foreach($collections as $key => $val) {
						if ( $val->col_id == $product->attributes()->number ) {

							// prepare the first two segments for comparison
							$productSegments = explode(".", $product->attributes()->number);
							if ( count($productSegments) > 1 ) {
								$productCategory = $productSegments[0].".".$productSegments[1];
							}

							// prepare the product for highlighting
							if (in_array($productCategory, $selFilters)) {
								$productMatch = true;
							}		

							// re-set the productCategory to integer so we can easily put it in the right column
							foreach ( $mappings as $mapping ) {
								// if no mapping has been set up in the back-end, the product will not be added
								if ($productCategory == $mapping->collections ) {
									$productName = $val->title;
									$productNumber = $val->col_id;
									$productCategory = $mapping->category;
									$products[] = array(
										'name' => $productName,
										'number' => $productNumber,
										'category' => $productCategory,
										'match' => $productMatch,
									);
									$categories[] = $mapping->collections;
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
				if ($matchAny == true || empty($_GET['selFilters'])) {
					// if a dealer has no products, do not return the dealer
					if ( count($products) > 0 ) {
						$dealers[] = array(
							'id' => $storeId,
							'name' => (string)$dealer->attributes()->name,
							'type' => (string)$dealer->attributes()->storeType,
							'address' => (string)$dealer->attributes()->addrline1,
							'city' => (string)$dealer->attributes()->city,
							'state' => (string)$dealer->attributes()->state,
							'zip' => (string)$dealer->attributes()->zip,
							'phone' => (string)$dealer->attributes()->phone,
							'distance' => (string)$dealer->attributes()->distance,
							'lat' => (string)$dealer->attributes()->lat,
							'lon' => (string)$dealer->attributes()->lon,
							'products' => $products,
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

		if (empty($_GET['bench'])) {
			var_dump($url);
			var_dump($xml);
		} else if ($_GET['bench'] == '1'){
			var_dump('TIME: ' . $timeBench01 . ' seconds to return XML from service');
			var_dump($url);
			var_dump($xml);
		} else if ($_GET['bench'] == '2'){
			var_dump('TIME: ' . $timeBench02 . ' seconds to parse XML into JSON array');
			var_dump($dealerGroups);
		} else if ($_GET['bench'] == 'all'){
			var_dump('TIME: ' . $timeBench03 . ' seconds for script to execute');
			var_dump($dealerGroups);
		}

		die;

	}

}
```