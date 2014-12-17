<?php

ini_set('soap.wsdl_cache_enabled', '0'); 
ini_set('soap.wsdl_cache_ttl', '0'); 

defined('_JEXEC') or die;
require_once JPATH_COMPONENT.'/controller.php';

class Jw_lookupControllerStatus extends Jw_lookupController
{

	public function lookupOrder()
	{
		$debug = (isset($_GET["debug"]) && $_GET['debug'] == 'true' ) ? true : false;

		if ($debug) {
			ini_set("display_errors", 1);
			$input = $_GET;
			echo '<pre>';
		} else {
			header('Content-Type: application/json');
			$input = $_POST;
		}

		$ordernum = $input['ordernum'];

		$url = "https://ws2.jeld-wen.net/Jw_Thd_Wsi_GetOrderStatusWS?wsdl";
		$local_cert = "...";
		$passphrase = "...";
		$options = array(
			'local_cert' => $local_cert,
			'passphrase' => $passphrase,
			'trace' => 1,
			'cache_wsdl' => WSDL_CACHE_NONE 
		);
		
		$parameters = array(
			"OrderNumber" => $input['ordernum']
		);

		$output = array();

		try {
			$client = new SoapClient($url, $options);
			$client->__setLocation('https://ws2.jeld-wen.net/Jw_Thd_Wsi_GetOrderStatusWS?wsdl');
			$response = $client->getOrderStatus($parameters);

			if($debug){
				$reqXML = $client->__getLastRequest();
				$respXML = $client->__getLastResponse();
				echo '<h1>Request</h1>';
				echo $reqXML;
				echo '<h1>Response</h1>';
				echo $respXML;
				echo '<h1>Output</h1>';
				var_dump($response);
			}
			
			$output['ordernum'] = $ordernum;
			if($response->Result == "SUCCESS") {
				$output['result'] = 'success';
				$dt = new DateTime($response->OrderCreateDate);
				$output['recdate'] = $dt->format('m/d/Y');

				$startdate = $response->EstimatedDeliveryDateStart->_;
				$enddate = $response->EstimatedDeliveryDateEnd->_;

				if($startdate != 'NULL' && $enddate != 'NULL'){
					$startDt = new DateTime($response->EstimatedDeliveryDateStart->_);
					$endDt = new DateTime($response->EstimatedDeliveryDateEnd->_);
					if($startDt == $endDt){
						$output['estdate'] = $endDt->format('m/d/Y');
					} else {
						$output['estdate'] = $startDt->format('m/d/Y') . ' - ' . $endDt->format('m/d/Y');
					}
				} else {
					$output['estdate'] = '<b class="error">Contact Customer Service</b>';
					$output['extra'] = 'split';
				}

				$lastupdate = $response->LastUpdatedDateTime->_;
				if($lastupdate != 'NULL'){
					$lastupDt = new DateTime($response->LastUpdatedDateTime->_);
					$output['lastupdate'] = $lastupDt->format('m/d/Y');
				} else {
					$output['lastupdate'] = '<b>N/A.</b>';
				}

				$output['step'] = $this->getOrderStep($response);
			
				if ($response->OrderCanceled == true){
					$output['extra'] = 'cancel';
				} elseif ($response->PartialOrder){
					$output['extra'] = 'partial';
				} elseif ($response->SplitOrder){
					$output['extra'] = 'split';
				}

			} else {
				$output['result'] = 'error';
			}
		} catch(Exception $e) {
			if($debug){
				echo '<h1>Error</h1>';
				var_dump($e);
			}
			$output['result'] = 'fail';
		}

		if($debug){
			echo '<h1>JSON</h1>';
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
		if ($response->PreparingOrder == '300'){
			return '1';
		} elseif ($response->PreparingOrder == '200'){
			return '2';
		} elseif ($response->BuildingOrder == '200'){
			return '3';
		} elseif ($response->PackagingOrder == '200'){
			return '4';
		} elseif ($response->ShippedOrder == '100'){
			$endDt = new DateTime($response->EstimatedDeliveryDateEnd->_);
			$endDate = $endDt->format('m/d/Y');
			$nowDate = date('m/d/Y');
			if ($endDate >= $nowDate) {
				return '5';
			} else {
				return '6';
			}
		} else {
			return '0';
		}
	}

}
