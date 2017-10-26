<?php
/**
 * PostCalc Shipment plugin working with http://www.postcalc.ru API service
 *
 * @version $Id: postcalc.php$
 * @package VirtueMart
 * @subpackage Plugins - shipment
 * @copyright Copyright (C) 2004-2013 VirtueMart Team - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * @author Kamil Khadeyev aka esmark
 * @authorURL http://www.esmark.ru
 * @authorMail: esmark@mail.ru
 *
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

if (!class_exists('vmPSPlugin')) {
    require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
}



class plgVmShipmentPostcalc extends vmPSPlugin {

    // instance of class
    public static $_this = false;

    function __construct(& $subject, $config) {
		parent::__construct($subject, $config);
				
		//$this->api_url = 0;
		$this->api_response = 0;
		$this->zip_mode = 1;
		$this->show_error = array();
		
		$this->_loggable = TRUE;
		$this->_tablepkey = 'id';
		$this->_tableId = 'id';
		$this->tableFields = array_keys ($this->getTableSQLFields ());
		$varsToPush = $this->getVarsToPush ();
		$this->setConfigParameterable ($this->_configTableFieldName, $varsToPush);
		$this->onStoreInstallPluginTable($this->_psType);
    }

    /**
     * Create the table for this plugin if it does not yet exist.
     */
    protected function getVmPluginCreateTableSQL() {
		return $this->createTableSQL('PostCalc');
    }

    function getTableSQLFields() {
		$SQLfields = array(
		    'id' 							=> 'int(1) UNSIGNED NOT NULL AUTO_INCREMENT',
		    'virtuemart_order_id' 			=> 'int(11) UNSIGNED',
		    'order_number' 					=> 'char(32)',
		    'virtuemart_shipmentmethod_id' 	=> 'mediumint(1) UNSIGNED',
		    'shipment_name' 				=> 'varchar(5000)',
			'order_weight' 					=> 'decimal(10,4)',
			'shipment_weight_unit'         	=> 'char(3) DEFAULT \'G\'',
		    'shipment_cost' 				=> 'decimal(10,2)'//,
			//'shipment_package_fee' 		=> 'decimal(10,2)',
		    //'tax_id' 						=> 'smallint(1) DEFAULT NULL'
		);
		return $SQLfields;
    }

    /**
     * This method is fired when showing the order details in the frontend.
     * It displays the shipment-specific data.
     *
     * @param integer $order_number The order Number
     * @return mixed Null for shipments that aren't active, text (HTML) otherwise
     * @author Valérie Isaksen
     * @author Max Milbers
     */
    public function plgVmOnShowOrderFEShipment($virtuemart_order_id, $virtuemart_shipmentmethod_id, &$shipment_name) {
		$this->onShowOrderFE($virtuemart_order_id, $virtuemart_shipmentmethod_id, $shipment_name);
    }

    /**
     * This event is fired after the order has been stored; it gets the shipment method-
     * specific data.
     *
     * @param int $order_id The order_id being processed
     * @param object $cart  the cart
     * @param array $priceData Price information for this order
     * @return mixed Null when this method was not selected, otherwise true
     * @author Valerie Isaksen
     */
    function plgVmConfirmedOrder(VirtueMartCart $cart, $order) {
		if (!($method = $this->getVmPluginMethod($order['details']['BT']->virtuemart_shipmentmethod_id))) {
		    return null; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($method->shipment_element)) {
		    return false;
		}
	
		$values['virtuemart_order_id'] = $order['details']['BT']->virtuemart_order_id;
		$values['order_number'] = $order['details']['BT']->order_number;
		$values['virtuemart_shipmentmethod_id'] = $order['details']['BT']->virtuemart_shipmentmethod_id;
		$values['shipment_name'] = $this->renderPluginName($method);
		$values['order_weight'] = $this->getOrderWeight($cart, 'G');
		$values['shipment_weight_unit'] = 'G';
		$values['shipment_cost'] = $method->shipment_cost;//$method->cost*$this->getOrderAmount($cart);
		//$values['order_amount'] = $method->getOrderAmount($cart);
		//$values['shipment_package_fee'] = (int)$method->postcalc_extra;
		//$values['tax_id'] = 0;//$method->tax_id;
		
		$this->storePSPluginInternalData($values);
	
	return true;
    }

    /**
     * This method is fired when showing the order details in the backend.
     * It displays the shipment-specific data.
     * NOTE, this plugin should NOT be used to display form fields, since it's called outside
     * a form! Use plgVmOnUpdateOrderBE() instead!
     *
     * @param integer $virtuemart_order_id The order ID
     * @param integer $vendorId Vendor ID
     * @param object $_shipInfo Object with the properties 'shipment' and 'name'
     * @return mixed Null for shipments that aren't active, text (HTML) otherwise
     * @author Valerie Isaksen
     */
    public function plgVmOnShowOrderBEShipment($virtuemart_order_id, $virtuemart_shipmentmethod_id) {
		if (!($this->selectedThisByMethodId($virtuemart_shipmentmethod_id))) {
		    return null;
		}
		$html = $this->getOrderShipmentHtml($virtuemart_order_id);
		return $html;
    }

	/**
	 * @param $virtuemart_order_id
	 * @return string
	 */
    function getOrderShipmentHtml($virtuemart_order_id) {
		$db = JFactory::getDBO();
		$q = 'SELECT * FROM `' . $this->_tablename . '` '
			. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
		$db->setQuery($q);
		if (!($shipinfo = $db->loadObject())) {
		    vmWarn(500, $q . " " . $db->getErrorMsg());
		    return '';
		}
	
		if (!class_exists('CurrencyDisplay'))
		    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
	
		$currency = CurrencyDisplay::getInstance();
		/*echo "<pre>";
		//print_r( $method );
		echo "</pre>";*/
	
		$html = '<table class="adminlist table">' . "\n";
		$html .=$this->getHtmlHeaderBE();
		$html .= $this->getHtmlRowBE('POSTCALC_SHIPPING_NAME', $shipinfo->shipment_name);
		$html .= $this->getHtmlRowBE('POSTCALC_SHIPPING_WEIGHT', $shipinfo->order_weight." ".ShopFunctions::renderWeightUnit ($shipinfo->shipment_weight_unit));
		$html .= $this->getHtmlRowBE('POSTCALC_SHIPPING_COST', $currency->priceDisplay($shipinfo->shipment_cost));
		//$html .= $this->getHtmlRowBE('POSTCALC_SHIPPING_PACKAGE_FEE', $currency->priceDisplay($shipinfo->shipment_package_fee, '', false));
		$html .= '</table>' . "\n";
	
		return $html;
    }

    private function curl_get_contents($api_url) {
		// инициализация сеанса
		$ch = curl_init();
		
		// установка URL и других необходимых параметров
		curl_setopt($ch, CURLOPT_URL, $api_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		
		// загрузка страницы и выдача её браузеру
		$api_data = curl_exec($ch);
		
		// завершение сеанса и освобождение ресурсов
		curl_close($ch);
		
		return $api_data;
	}

	private function translit($st) {
		$char=array('а' => 'a','б' => 'b','в' => 'v','г' => 'g','д' => 'd','е' => 'e','ё' => 'e','ж' => 'zh','з' => 'z','и' => 'i','й' => 'y','к' => 'k','л' => 'l','м' => 'm','н' => 'n','о' => 'o','п' => 'p','р' => 'r','с' => 's','т' => 't','у' => 'u','ф' => 'f','х' => 'h','ц' => 'c','ч' => 'ch','ш' => 'sh','щ' => 'sch','ь' => '','ы' => 'y','ъ' => '','э' => 'e','ю' => 'yu','я' => 'ya','А' => 'A','Б' => 'B','В' => 'V','Г' => 'G','Д' => 'D','Е' => 'E','Ё' => 'E','Ж' => 'Zh','З' => 'Z','И' => 'I','Й' => 'Y','К' => 'K','Л' => 'L','М' => 'M','Н' => 'N','О' => 'O','П' => 'P','Р' => 'R','С' => 'S','Т' => 'T','У' => 'U','Ф' => 'F','Х' => 'H','Ц' => 'C','Ч' => 'Ch','Ш' => 'Sh','Щ' => 'Sch','Ь' => '','Ы' => 'Y','Ъ' => '','Э' => 'E','Ю' => 'Yu','Я' => 'Ya',' ' => '_');
		$st=strtr($st,$char);
		// удаляем начальные и конечные '_'
		$st = trim($st, "_");
		$st = strtolower($st);
		return $st;
	}
	
	private function rustrtolower($string)
	{
	    $large = array('А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й',
	                   'К','Л','М','Н','О','П','Р','С','Т','У','Ф',
	                   'Х','Ч','Ц','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я');
	    $small = array('а','б','в','г','д','е','ё','ж','з','и','й',
	                   'к','л','м','н','о','п','р','с','т','у','ф',
	                   'х','ч','ц','ш','щ','ъ','ы','ь','э','ю','я');
	    return str_replace($large, $small, $string); 
	}

	/**
	 * @param VirtueMartCart $cart
	 * @param                $method
	 * @param                $cart_prices
	 * @return int
	 */
    function getCosts(VirtueMartCart $cart, $method, $cart_prices) {
		
		/*echo "<pre>";
		print_r($cart);
		echo "</pre>";*/
			
		if ($method->postcalc_free_shipment && $cart_prices['salesPrice'] >= (float)$method->postcalc_free_shipment) {
			return 0;
		} 
		
		if (!isset($method->shipment_cost) || !$method->shipment_cost) {
			$app = JFactory::getApplication();
			
			$order_weight = $this->getOrderWeight($cart, 'G');
			vmdebug('Вес заказ: '.$method->shipment_name.' гр.');
			$mysite = JURI::base();
			// Извлекаем имя хоста из URL
			preg_match("/^(http[a-z]*:\/\/)?([^\/]+)/i", $mysite, $matches);
			$mysite = str_replace(array("www.", "/"), "", $matches[2]);
			
			$postcalc_email_admin = $method->postcalc_email_admin?$method->postcalc_email_admin:"esmark@mail.ru";
			
			$api_country=(isset($method->api_country))?'&c='.urlencode($method->api_country):'';
			//$test_url='http://test.postcalc.ru/?o=json';
			$api_url='http://api.postcalc.ru/?st='.urlencode($mysite)
			.'&ml='.$postcalc_email_admin.''
			.'&sw=plg_postcalc_1.2.0&o=json&e=0'
			.'&f='.urlencode($method->api_from).$api_country.'&t='.urlencode($method->api_to)
			.'&w='.urlencode($order_weight).'&v='.urlencode($cart_prices['salesPrice']);
			$api_error = JText::_('VMSHIPMENT_POSTCALC_API_ERROR')."<br />".JText::_('VMSHIPMENT_POSTCALC_API_ERROR2');
			
			if ($this->api_response) {
				$api_response = $this->api_response;
				//echo "CACHED<br />";
				vmdebug('Данные '.$method->shipment_name.' получены из кеша');
			}
			else {
				if (function_exists('curl_init')) {
				    //echo "CURL functions are available.<br />\n";			
					$api_response=$this->curl_get_contents($api_url);// or $app->enqueueMessage($api_error, 'error');
				} else {
				    //echo "CURL functions are not available.<br />\n";			
					$api_response=file_get_contents($api_url);// or $app->enqueueMessage($api_error, 'error');
				}
				$this->api_response = $api_response;
				//echo "URL: ".$api_url."<br />";
				vmdebug('Данные '.$method->shipment_name.' получены: '.$api_url);
			}
			if ( substr($api_response,0,3) == "\x1f\x8b\x08" ) {
				$api_response=gzinflate(substr($api_response,10,-8));
			}
			
			$api_data=json_decode($api_response, true);
			
			$api_status = $api_data['Status'];
			if ($api_status != 'OK') {
				if (!in_array("message", $this->show_error)) {
					$state_error = JText::_('VMSHIPMENT_POSTCALC_STATE_ERROR');
					$app->enqueueMessage($api_data['Message'], 'warning');
					$this->show_error[] = "message";
				}
			}
			$postcalc_delivery = $method->postcalc_delivery;

			if (isset($api_data['Отправления'][$postcalc_delivery]['НетРасчета']) && !$api_data['Отправления'][$postcalc_delivery]['Тариф']) {
				//echo "Ей, нет расчета<br />";
				if (!in_array("no_count", $this->show_error)) {
					$state_error = JText::_('VMSHIPMENT_POSTCALC_STATE_ERROR');
					$app->enqueueMessage($api_data['Отправления'][$postcalc_delivery]['НетРасчета'], 'error');
					$this->show_error[] = "no_count";
				}
				
				//if (!$this->zip_mode && !$api_data['Отправления'][$postcalc_delivery]['Тариф']) {
					//$method->shipment_cost = 0;
					return 0;
				//}
			} else {
				$method->shipment_cost = (isset($method->postcalc_valuation) && $method->postcalc_valuation)?
					$api_data['Отправления'][$postcalc_delivery]['Доставка']:
					$api_data['Отправления'][$postcalc_delivery]['Тариф'];
					
				//Наложенный платеж + к доставке
				$postcalc_payment_forward = (isset($api_data['Отправления'][$postcalc_delivery]['НаложенныйПлатеж']) && $method->postcalc_payment_forward)?
					$api_data['Отправления'][$postcalc_delivery]['НаложенныйПлатеж']:
					0;
				$method->shipment_cost += $postcalc_payment_forward;
				$method->shipment_cost += $method->postcalc_extra;
				//$method->package_fee = $method->postcalc_extra;			
			}
			
			if ($this->zip_mode && !$method->shipment_cost) {
				//echo "STATE-TO<br />";
				//$zip_error = JText::_('VMSHIPMENT_POSTCALC_ZIP_ERROR');
				//$app->enqueueMessage($zip_error, 'warning');
				$this->zip_mode = 0;
				$this->api_response = 0;
				$this->checkConditions($cart, $method, $cart_prices);
				$this->getCosts($cart, $method, $cart_prices);
			}
		}
		/*echo "<pre>";
		print_r( $order_weight );
		//echo $postcalc_delivery." -> ".$method->shipment_cost;
		print_r( $method );
		echo "</pre>";*/
		return $method->shipment_cost;
    }
	
	/**
	 * @param \VirtueMartCart $cart
	 * @param int             $method
	 * @param array           $cart_prices
	 * @return bool
	 */
    protected function checkConditions($cart, $method, $cart_prices) {
		$app = JFactory::getApplication();
		
		$postcalc_categoties = explode(",", str_replace(" ", "", $method->postcalc_categoties));
		if(count($cart->products)>0){
			foreach ($cart->products as $id => $product) {
		/*echo "<pre>";
		print_r($product);
		echo "</pre>";*/
				if (in_array($product->virtuemart_category_id, $postcalc_categoties)) {
					return false;
				}
				//BugFix product_weight_uom in VM 2.6
				if (empty($weight)) {
					$cart->products[$id]->product_weight_uom = str_replace(".", "", $product->product_weight_uom);
				}
			}
		}
		
		//Минимальная сумма заказа
		if(isset($cart_prices['salesPrice']) && $cart_prices['salesPrice'] < $method->postcalc_order_amount){
			return false;
		}

		$order_weight = $this->getOrderWeight($cart, 'G');

		$vendorId = ($method->virtuemart_vendor_id)?$method->virtuemart_vendor_id:1;
		$vendorModel = VmModel::getModel('vendor');
		$vendorAddress = $vendorModel->getVendorAdressBT($vendorId);
		$shopperAddress = (($cart->ST == 0) ? $cart->BT : $cart->ST);
		//$state = array();
		$states = $method->postcalc_states?explode(",", str_replace(" ", "", $method->postcalc_states)):array();

		$countries = array();
		if (!empty($method->postcalc_countries)) {
			if (!is_array ($method->postcalc_countries)) {
				$countries[0] = $method->postcalc_countries;
			} else {
				$countries = $method->postcalc_countries;
			}
		}
		
		if (!$this->zip_mode) {
			//echo "Отключаем индекс<br />";
		    $shopperAddress['zip'] = false;
		}

		// probably did not gave BT:ST address
		if (!is_array($shopperAddress)) {
		    $shopperAddress = array();
		    $shopperAddress['zip'] = 0;
		    $shopperAddress['virtuemart_state_id'] = 0;
		    $shopperAddress['virtuemart_country_id'] = 0;
		    $shopperAddress['state_code'] = '';
		}

		if (!is_object($vendorAddress)) {
		    $vendorAddress = new stdClass();
		    $vendorAddress->zip = 0;
		    $vendorAddress->virtuemart_state_id = 0;
		    $vendorAddress->virtuemart_country_id = 0;
		    //$vendorAddress->state_code = '';
		    $vendorAddress->state_name = '';
			return false;
		}
		
		if ($vendorAddress->virtuemart_country_id=='176') {
			$postcalc_delivery = $method->postcalc_delivery;
			$country_delivery = stripos($postcalc_delivery, 'Мжд');
			
			if (!class_exists('ShopFunctions')) {
				require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'shopfunctions.php');
			}
			$shopperAddress['state_code'] = shopFunctions::getStateByID($shopperAddress['virtuemart_state_id'], 'state_2_code');
			$vendorAddress->state_name = shopFunctions::getStateByID($vendorAddress->virtuemart_state_id);
			//$vendorAddress->state_code = shopFunctions::getStateByID($vendorAddress->virtuemart_state_id, 'state_2_code');
			
			if($vendorAddress->zip) {
				$method->api_from=$vendorAddress->zip;
			} else {
				$method->api_from=$vendorAddress->state_name;
			}
	
			if (in_array ($shopperAddress['virtuemart_country_id'], $countries) || count ($countries) == 0) {
				//vmdebug('postcalc '.$method->shipment_name.' = TRUE for variable virtuemart_country_id = '.implode($countries,', ').', Reason: Country in rule or none set');
				$country_cond = true;
			}
			else{
				vmdebug('postcalc '.$method->shipment_name.' = FALSE for variable virtuemart_country_id = '.implode($countries,', ').', Reason: Country does not fit');
				$country_cond = false;
			}

			//Доставка по России
			if ($shopperAddress['virtuemart_country_id']=='176') {
				//Бандероль
				$postpacket_weight_max = 2000;
				
				//Отправления 1 класса
				$firstclass_weight_max = 2500;
				
				//Посылки
				$package_weight_norm = 10000;
				$package_weight_max = 20000;
				
				//EMS-почта
				$emspost_weight_max = 31500;
				
				if (in_array ($shopperAddress['state_code'], $states) || !count($states)) {
					//vmdebug('postcalc '.$method->shipment_name.' = TRUE for variable state_code = '.implode($states,', ').', Reason: State in rule or none set');
					$state_cond = true;
				}
				else{
					vmdebug('postcalc '.$method->shipment_name.' = FALSE for variable state_code = '.implode($states,', ').', Reason: State does not fit');
					$state_cond = false;
				}
				
				/*echo "<pre>";
				print_r( $shopperAddress['state_code'] );
				echo " - ";
				print_r( $states );
				echo "</pre>";*/
	
				if ($country_delivery === false && $country_cond && $state_cond) {
					
					//отключаем доставку, превысевшую вес ограничения
					if ($method->postcalc_weight_limit) {
						if ($postcalc_delivery == 'EMS' && $order_weight > $emspost_weight_max) {
							return false;
						} elseif (stripos($postcalc_delivery, 'Посылка') !== false && $order_weight > $package_weight_max) {
							return false;
						} elseif (stripos($postcalc_delivery, '1Класс') !== false && $order_weight > $firstclass_weight_max) {
							return false;
						} elseif (stripos($postcalc_delivery, 'Бандероль') !== false && $order_weight > $postpacket_weight_max) {
							return false;
						}
					}
					//отключаем Авиа доставку по городу продавца (= город покупателя)
					$avia_delivery = stripos($postcalc_delivery, 'Авиа');
					if ($avia_delivery !== false && ($shopperAddress['virtuemart_state_id'] == $vendorAddress->virtuemart_state_id)) {
						return false;
					}

					//отключаем доставку по городу продавца (= город покупателя)
					if ($method->postcalc_except == "C" && $vendorAddress->city) {
						$vendor_city = (function_exists('mb_strtolower'))?mb_strtolower($vendorAddress->city, "UTF-8"):$this->rustrtolower($vendorAddress->city);
						$shopper_city = (function_exists('mb_strtolower'))?mb_strtolower($shopperAddress['city'], "UTF-8"):$this->rustrtolower($shopperAddress['city']);
						if ($shopper_city == $vendor_city) {
							vmdebug('postcalc '.$method->shipment_name.' = FALSE. Отключена доставка по городу продавца: variable shopper_city = vendor_city ('.$vendorAddress->city.').');
							return false; 
						}
					}
					
					//отключаем доставку по региону
					if (($method->postcalc_except == "S" && $vendorAddress->virtuemart_state_id) && ($shopperAddress['virtuemart_state_id'] == $vendorAddress->virtuemart_state_id)) {
						vmdebug('postcalc '.$method->shipment_name.' = FALSE. Отключена доставка по региону продавца: variable shopperAddress[virtuemart_state_id] = vendorAddress->virtuemart_state_id ('.$vendorAddress->virtuemart_state_id.').');
						return false;
					}
					
				    if ($this->zip_mode && $shopperAddress['zip'] && strlen($shopperAddress['zip']) == 6) {
						vmdebug('postcalc '.$method->shipment_name.' = TRUE. Доставка определяется по индексу покупателя: variable shopperAddress[zip] = '.$shopperAddress['zip'].'.');
						$method->api_to=$shopperAddress['zip'];			
						return true;
				    }
				    elseif ($shopperAddress['virtuemart_state_id']) {
						vmdebug('postcalc '.$method->shipment_name.' = TRUE. Доставка определяется по региону покупателя: variable shopperAddress[virtuemart_state_id] = '.$shopperAddress['virtuemart_state_id'].'.');
						$stateModel = VmModel::getModel('state');
						$stateModel->_id = $shopperAddress['virtuemart_state_id'];
						$shopperState = $stateModel->getSingleState();
						$method->api_to=$shopperState->state_name;
						return true;
					}
					else {
						if (!in_array("state_error", $this->show_error)) {
							$state_error = JText::_('VMSHIPMENT_POSTCALC_STATE_ERROR');
							$app->enqueueMessage($state_error, 'error');
							$this->show_error[] = "state_error";
						}
						return false;
					}
				}
			}
			//Международные отправления
			else {
				//МелкийПакет
				$smallpacket_weight_max = 2000;
				
				//Бандероль
				$postpacket_weight_max = 5000;
				
				//МешокМ
				$bagm_weight_max = 14500;
				
				//Посылка
				$package_weight_max = 20000;
				
				//EMS-почта
				$emspost_weight_max = 30000;
				
				if ($country_delivery !== false && $country_cond) {
					//отключаем доставку, превысевшую вес ограничения
					if ($method->postcalc_weight_limit) {
						if (stripos($postcalc_delivery, 'EMS') !== false && $order_weight > $emspost_weight_max) {
							return false;
						} elseif (stripos($postcalc_delivery, 'МешокМ') !== false && $order_weight > $bagm_weight_max) {
							return false;
						} elseif (stripos($postcalc_delivery, 'Посылка') !== false && $order_weight > $package_weight_max) {
							return false;
						} elseif (stripos($postcalc_delivery, 'Бандероль') !== false && $order_weight > $postpacket_weight_max) {
							return false;
						} elseif (stripos($postcalc_delivery, 'МелкийПакет') !== false && $order_weight > $smallpacket_weight_max) {
							return false;
						}
					}
					
					$db = JFactory::getDBO();
					$query = 'SELECT `country_2_code` FROM `#__virtuemart_countries`';
					$query .= ' WHERE `virtuemart_country_id` = ' . (int)$shopperAddress['virtuemart_country_id'];
					$db->setQuery($query);
					$shopperAddress['country_2_code'] = $db->loadResult();
					
					$method->api_country=($shopperAddress['country_2_code'])?$shopperAddress['country_2_code']:'RU';			
				    if ($shopperAddress['zip'] && (strlen($shopperAddress['zip']) == 5 || strlen($shopperAddress['zip']) == 6 )) {
						$method->api_to=$shopperAddress['zip'];			
						return true;
				    }
				    elseif ($shopperAddress['virtuemart_state_id']) {
						$stateModel = VmModel::getModel('state');
						$stateModel->_id = $shopperAddress['virtuemart_state_id'];
						$shopperState = $stateModel->getSingleState();
						$method->api_to=$shopperState->state_name;
						return true;
					}
				}
			}
		}
		/*echo "<pre>";
		//print_r( $shopperAddress );
		print_r( $method );
		echo "</pre>";
		//exit;*/

		return false;
    }

	function plgVmOnProductDisplayShipment($product, &$productDisplayShipments){

		$vendorId = 1;
		if ($this->getPluginMethods($vendorId) === 0) {
			return FALSE;
		}
		if (!class_exists('VirtueMartCart'))
			require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
		$cart = VirtueMartCart::getCart();
		$html = '';
		if (!class_exists('CurrencyDisplay'))
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
		$currency = CurrencyDisplay::getInstance();

		foreach ($this->methods as $this->_currentMethod) {
			if($this->_currentMethod->show_on_pdetails){
				if($this->checkConditions($cart,$this->_currentMethod,$cart->pricesUnformatted,$product)){

					$product->prices['shipmentPrice'] = $this->getCosts($cart,$this->_currentMethod,$cart->pricesUnformatted);

					if(isset($product->prices['VatTax']) and count($product->prices['VatTax'])>0){
						reset($product->prices['VatTax']);
						$rule = current($product->prices['VatTax']);
						if(isset($rule[1])){
							$product->prices['shipmentTax'] = $product->prices['shipmentPrice'] * $rule[1]/100.0;
							$product->prices['shipmentPrice'] = $product->prices['shipmentPrice'] * (1 + $rule[1]/100.0);
						}
					}

					$html = $this->renderByLayout( 'default', array("method" => $this->_currentMethod, "cart" => $cart,"product" => $product,"currency" => $currency) );
				}
			}

		}

		$productDisplayShipments[] = $html;

	}

    /**
     * Create the table for this plugin if it does not yet exist.
     * This functions checks if the called plugin is active one.
     * When yes it is calling the standard method to create the tables
     * @author Valérie Isaksen
     *
     */
    function plgVmOnStoreInstallShipmentPluginTable($jplugin_id) {
		return $this->onStoreInstallPluginTable($jplugin_id);
    }


	/**
	 * @param VirtueMartCart $cart
	 * @return null
	 */
	public function plgVmOnSelectCheckShipment (VirtueMartCart &$cart) {

		return $this->OnSelectCheck ($cart);
	}

    /**
     * plgVmDisplayListFE
     * This event is fired to display the pluginmethods in the cart (edit shipment/payment) for example
     *
     * @param object $cart Cart object
     * @param integer $selected ID of the method selected
     * @return boolean True on success, false on failures, null when this plugin was not selected.
     * On errors, JError::raiseWarning (or JError::raiseError) must be used to set a message.
     *
     * @author Valerie Isaksen
     * @author Max Milbers
     */
    public function plgVmDisplayListFEShipment(VirtueMartCart $cart, $selected = 0, &$htmlIn) {
		return $this->displayListFE($cart, $selected, $htmlIn);
    }

	/**
	 * @param VirtueMartCart $cart
	 * @param array          $cart_prices
	 * @param                $cart_prices_name
	 * @return bool|null
	 */
	public function plgVmOnSelectedCalculatePriceShipment (VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {
		return $this->onSelectedCalculatePrice ($cart, $cart_prices, $cart_prices_name);
	}

	/**
	 * plgVmOnCheckAutomaticSelected
	 * Checks how many plugins are available. If only one, the user will not have the choice. Enter edit_xxx page
	 * The plugin must check first if it is the correct type
	 *
	 * @author Valerie Isaksen
	 * @param VirtueMartCart cart: the cart object
	 * @return null if no plugin was found, 0 if more then one plugin was found,  virtuemart_xxx_id if only one plugin is found
	 *
	 */
	function plgVmOnCheckAutomaticSelectedShipment (VirtueMartCart $cart, array $cart_prices, &$shipCounter) {

		if ($shipCounter > 1) {
			return 0;
		}

		return $this->onCheckAutomaticSelected ($cart, $cart_prices, $shipCounter);
	}

	/**
	 * This method is fired when showing when priting an Order
	 * It displays the the payment method-specific data.
	 *
	 * @param integer $_virtuemart_order_id The order ID
	 * @param integer $method_id  method used for this order
	 * @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
	 * @author Valerie Isaksen
	 */
	function plgVmonShowOrderPrint ($order_number, $method_id) {
		return $this->onShowOrderPrint ($order_number, $method_id);
	}

	function plgVmDeclarePluginParamsShipment ($name, $id, &$dataOld) {
		return $this->declarePluginParams ('shipment', $name, $id, $dataOld);
	}

	function plgVmDeclarePluginParamsShipmentVM3 (&$data) {
		return $this->declarePluginParams ('shipment', $data);
	}

    function plgVmSetOnTablePluginParamsShipment($name, $id, &$table) {
		return $this->setOnTablePluginParams($name, $id, $table);
    }

	/**
	 * @author Max Milbers
	 * @param $data
	 * @param $table
	 * @return bool
	 */
	function plgVmSetOnTablePluginShipment(&$data,&$table){

		$name = $data['shipment_element'];
		$id = $data['shipment_jplugin_id'];

		if (!empty($this->_psType) and !$this->selectedThis ($this->_psType, $name, $id)) {
			return FALSE;
		} else {
			$toConvert = array('weight_start','weight_stop','orderamount_start','orderamount_stop');
			foreach($toConvert as $field){

				if(!empty($data[$field])){
					$data[$field] = str_replace(array(',',' '),array('.',''),$data[$field]);
				} else {
					unset($data[$field]);
				}
			}

			$data['nbproducts_start'] = (int) $data['nbproducts_start'];
			$data['nbproducts_stop'] = (int) $data['nbproducts_stop'];
			//I dont see a reason for it
			/*$toConvert = array('zip_start','zip_stop','nbproducts_start' , 'nbproducts_stop');
			foreach($toConvert as $field){
				if(!empty($data[$field])){
					$data[$field] = str_replace( ' ','',$data[$field]);
				} else {
					unset($data[$field]);
				}
				if (preg_match ("/[^0-9]/", $data[$field])) {
					vmWarn( JText::sprintf('VMSHIPMENT_WEIGHT_COUNTRIES_NUMERIC', JText::_('VMSHIPMENT_WEIGHT_COUNTRIES_'.$field) ) );
				}
			}*/
			//Reasonable tests:
			if(!empty($data['zip_start']) and !empty($data['zip_stop']) and (int)$data['zip_start']>=(int)$data['zip_stop']){
				vmWarn('VMSHIPMENT_WEIGHT_COUNTRIES_ZIP_CONDITION_WRONG');
			}
			if(!empty($data['weight_start']) and !empty($data['weight_stop']) and (float)$data['weight_start']>=(float)$data['weight_stop']){
				vmWarn('VMSHIPMENT_WEIGHT_COUNTRIES_WEIGHT_CONDITION_WRONG');
			}

			if(!empty($data['orderamount_start']) and !empty($data['orderamount_stop']) and (float)$data['orderamount_start']>=(float)$data['orderamount_stop']){
				vmWarn('VMSHIPMENT_WEIGHT_COUNTRIES_AMOUNT_CONDITION_WRONG');
			}

			if(!empty($data['nbproducts_start']) and !empty($data['nbproducts_stop']) and (float)$data['nbproducts_start']>=(float)$data['nbproducts_stop']){
				vmWarn('VMSHIPMENT_WEIGHT_COUNTRIES_NBPRODUCTS_CONDITION_WRONG');
			}

			return $this->setOnTablePluginParams ($name, $id, $table);
		}
	}
}

// No closing tag
