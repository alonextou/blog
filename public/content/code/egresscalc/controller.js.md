<?php

/**
 * @version     1.0.0
 * @package     com_jw_egresscalc
 * @copyright   JELD-WEN © Copyright 2012. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Alex Crawford <alexc@jeld-wen.com> - JELD-WEN http://jeld-wen.com
 */

header('Content-Type: application/json');
defined('_JEXEC') or die;
require_once JPATH_COMPONENT.'/controller.php';

class Jw_egresscalcControllerEgresscalc extends Jw_egresscalcController
{
	public function getOptions(){
		
		$allOptions = array();
		$materialModel = $this->getModel('material');
		$productModel = $this->getModel('product');
		$styleModel = $this->getModel('style');

		$materials = $materialModel->getMaterials();
		
		foreach($materials as $material) {
			$materialProducts = array();
			$products = $productModel->getProductsByMaterialId($material->id);
			foreach($products as $product) {
				$styles = $styleModel->getStylesByProductId($product->id);
				$productStyles = array();
				foreach($styles as $style) {
					$productStyles[] = array(
						'id' => $style->id,
						'title' => $style->title
					);
				}
				$materialProducts[] = array(
					'id' => $product->id,
					'title' => $product->title,
					'styles' => $productStyles
				);
			}
			$allOptions[] = array(
				'id' => $material->id,
				'title' => $material->title,
				'products' => $materialProducts
			);
		}

		echo json_encode($allOptions);

	}

	public function postForm(){

		//http://jw.stage.dev/index.php/?option=com_jw_egresscalc&task=egresscalc.postForm&format=raw&styleId=1&frameWidth=20&frameHeight=20&formulas%5Bid%5D=1&formulas%5Bcow%5D=FW+-+3.281&formulas%5Bcoh%5D=(FH+-+9.625)+%2F+2&formulas%5Bcos%5D=COW+*+COH&formulas%5Bhas_leg%5D=1

		$styleId = (empty($_GET['styleId']) ? null : $_GET['styleId']);
		$formulas = (empty($_GET['formulas']) ? null : $_GET['formulas']);
		$frameWidth = (empty($_GET['frameWidth']) ? null : $_GET['frameWidth']);
		$frameHeight = (empty($_GET['frameHeight']) ? null : $_GET['frameHeight']);
		$legHeight = (empty($_GET['legHeight']) ? null : $_GET['legHeight']);

		$calcFormulas = array();
		$formulaAbbreviations = array('cow', 'coh', 'cos', 'vas', 'dos');
		foreach($formulaAbbreviations as $abbrv) {
			$jsonFormulas = json_decode($formulas[$abbrv]);
			if (!empty($jsonFormulas)) {
				$calcFormulas[$abbrv] = $jsonFormulas;
			}
		}

		$results = array();
		$variables = array('FrameWidth', 'FrameHeight');
		$values = array($frameWidth, $frameHeight);

		if($legHeight != null){
			array_push($variables, 'LegHeight');
			array_push($values, $legHeight);
		}

		foreach($calcFormulas as $abbrv => $formulaConditions){
			foreach($formulaConditions as $formulaCondition){
				$condition = str_replace($variables, $values, $formulaCondition->condition);
				$condition = (empty($condition) ? true : $condition);
				if($this->calc_string($condition)) {
					$formula = str_replace($variables, $values, $formulaCondition->formula);
					$result = $this->calc_string($formula);
					$results[$abbrv] = $result;
					array_push($variables, strtoupper($abbrv));
					array_push($values, $result);
					break;
				}

			}			
		}

		echo json_encode($results);

	}

	public function getFormula(){

		$styleId = (empty($_GET['styleId']) ? null : $_GET['styleId']);

		$formulaModel = $this->getModel('formula');
		$formulas = $formulaModel->getFormulaByStyleId($styleId);

		echo json_encode($formulas);

	}

	function calc_string($mathString)
	{
		$cf_DoCalc = create_function("", "return (".$mathString.");");
		$rounded = round($cf_DoCalc(), 3);
		return $rounded;
	}

}