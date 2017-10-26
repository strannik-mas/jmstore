<?php
/**
 * PostCalc Shipment plugin working with http://www.postcalc.ru API service
 * @author Kamil Khadeyev aka esmark
 * @authorURL http://www.esmark.ru
 * @authorMail: esmark@mail.ru
 *
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

class plgVmshipmentPostcalcInstallerScript {
	function postflight ($parent) {
		$err = array();
		$db = JFactory::getDBO();
		
		//Updates from 1.0.8
		$query = $db->getQuery(true);
		$query = "SHOW COLUMNS FROM #__virtuemart_shipment_plg_postcalc";
		
	    $db->setQuery((string)$query);
	    $res = $db->loadResultArray();
		$showerr = $db->getErrorMsg();
		
		if (is_array($res) && !in_array('shipment_weight_unit', $res) && !$showerr) {			
			
			$query = "ALTER TABLE `#__virtuemart_shipment_plg_postcalc` ADD COLUMN `shipment_weight_unit` char(3) DEFAULT 'G' AFTER `order_weight`";
			$db->setQuery((string)$query);
		    $res = $db->loadResult();
			$err[] = $db->getErrorMsg();
			/*echo (string)$query."<pre>RES: ";
			print_r($res);
			echo "<br />ERR: ";
			print_r($err);
			echo "</pre>";
			exit;
			*/
			$release = "1.0.8";
		} else {
			$release = "1.0.9";
		}
		//End Updates
		
		//$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('virtuemart_country_id');
		$query->from('#__virtuemart_states');
	    $db->setQuery((string)$query);
	    $res = $db->loadResult();
		$err[] = $db->getErrorMsg();
		if (!isset($res) || !$res) {
			// Create a new query object.
			$query = $db->getQuery(true);		 
			// Insert columns.
			$columns = array('virtuemart_country_id', 'state_name', 'state_3_code', 'state_2_code');
			// Insert values.
			//Usage: $query->values('1,2,3')->values('4,5,6'); $query->values(array('1,2,3', '4,5,6'));
			//$values = array(1001, $db->quote('custom.message'), $db->quote('Inserting a record using insert()'), 1);
			 
			// Prepare the insert query.
			$query
			    ->insert($db->quoteName('#__virtuemart_states'))
			    ->columns($db->quoteName($columns))
			    ->values('176,' . $db->quote('Адыгея Республика') . ',' . $db->quote('AD') . ',' . $db->quote('01'))
			    ->values('176,' . $db->quote('Алтай Республика') . ',' . $db->quote('AL') . ',' . $db->quote('04'))
			    ->values('176,' . $db->quote('Алтайский край') . ',' . $db->quote('ALT') . ',' . $db->quote('22'))
			    ->values('176,' . $db->quote('Амурская область') . ',' . $db->quote('AMU') . ',' . $db->quote('28'))
			    ->values('176,' . $db->quote('Архангельская область') . ',' . $db->quote('ARK') . ',' . $db->quote('29'))
			    ->values('176,' . $db->quote('Астраханская область') . ',' . $db->quote('AST') . ',' . $db->quote('30'))
			    ->values('176,' . $db->quote('Башкортостан Республика') . ',' . $db->quote('BA') . ',' . $db->quote('02'))
			    ->values('176,' . $db->quote('Белгородская область') . ',' . $db->quote('BEL') . ',' . $db->quote('31'))
			    ->values('176,' . $db->quote('Брянская область') . ',' . $db->quote('BRY') . ',' . $db->quote('32'))
			    ->values('176,' . $db->quote('Бурятия Республика') . ',' . $db->quote('BU') . ',' . $db->quote('03'))
			    ->values('176,' . $db->quote('Владимирская область') . ',' . $db->quote('VLA') . ',' . $db->quote('33'))
			    ->values('176,' . $db->quote('Волгоградская область') . ',' . $db->quote('VGG') . ',' . $db->quote('34'))
			    ->values('176,' . $db->quote('Вологодская область') . ',' . $db->quote('VLG') . ',' . $db->quote('35'))
			    ->values('176,' . $db->quote('Воронежская область') . ',' . $db->quote('VOR') . ',' . $db->quote('36'))
			    ->values('176,' . $db->quote('Дагестан Республика') . ',' . $db->quote('DA') . ',' . $db->quote('05'))
			    ->values('176,' . $db->quote('Еврейская автономная область') . ',' . $db->quote('YEV') . ',' . $db->quote('79'))
			    ->values('176,' . $db->quote('Забайкальский край') . ',' . $db->quote('ZAB') . ',' . $db->quote('75'))
			    ->values('176,' . $db->quote('Ивановская область') . ',' . $db->quote('IVA') . ',' . $db->quote('37'))
			    ->values('176,' . $db->quote('Ингушетия Республика') . ',' . $db->quote('IN') . ',' . $db->quote('06'))
			    ->values('176,' . $db->quote('Иркутская область') . ',' . $db->quote('IRK') . ',' . $db->quote('38'))
			    ->values('176,' . $db->quote('Кабардино-Балкарская Республика') . ',' . $db->quote('KB') . ',' . $db->quote('07'))
			    ->values('176,' . $db->quote('Калининградская область') . ',' . $db->quote('KGD') . ',' . $db->quote('39'))
			    ->values('176,' . $db->quote('Калмыкия Республика') . ',' . $db->quote('KL') . ',' . $db->quote('08'))
			    ->values('176,' . $db->quote('Калужская область') . ',' . $db->quote('KLU') . ',' . $db->quote('40'))
			    ->values('176,' . $db->quote('Камчатский край') . ',' . $db->quote('KAM') . ',' . $db->quote('41'))
			    ->values('176,' . $db->quote('Карачаево-Черкесская Республика') . ',' . $db->quote('KC') . ',' . $db->quote('09'))
			    ->values('176,' . $db->quote('Карелия Республика') . ',' . $db->quote('KR') . ',' . $db->quote('10'))
			    ->values('176,' . $db->quote('Кемеровская область') . ',' . $db->quote('KEM') . ',' . $db->quote('42'))
			    ->values('176,' . $db->quote('Кировская область') . ',' . $db->quote('KIR') . ',' . $db->quote('43'))
			    ->values('176,' . $db->quote('Коми Республика') . ',' . $db->quote('KO') . ',' . $db->quote('11'))
			    ->values('176,' . $db->quote('Костромская область') . ',' . $db->quote('KOS') . ',' . $db->quote('44'))
			    ->values('176,' . $db->quote('Краснодарский край') . ',' . $db->quote('KDA') . ',' . $db->quote('23'))
			    ->values('176,' . $db->quote('Красноярский край') . ',' . $db->quote('KIA') . ',' . $db->quote('24'))
			    ->values('176,' . $db->quote('Курганская область') . ',' . $db->quote('KGN') . ',' . $db->quote('45'))
			    ->values('176,' . $db->quote('Курская область') . ',' . $db->quote('KRS') . ',' . $db->quote('46'))
			    ->values('176,' . $db->quote('Ленинградская область') . ',' . $db->quote('LEN') . ',' . $db->quote('47'))
			    ->values('176,' . $db->quote('Липецкая область') . ',' . $db->quote('LIP') . ',' . $db->quote('48'))
			    ->values('176,' . $db->quote('Магаданская область') . ',' . $db->quote('MAG') . ',' . $db->quote('49'))
			    ->values('176,' . $db->quote('Марий Эл Республика') . ',' . $db->quote('ME') . ',' . $db->quote('12'))
			    ->values('176,' . $db->quote('Мордовия Республика') . ',' . $db->quote('MO') . ',' . $db->quote('13'))
			    ->values('176,' . $db->quote('Москва') . ',' . $db->quote('MOW') . ',' . $db->quote('77'))
			    ->values('176,' . $db->quote('Московская область') . ',' . $db->quote('MOS') . ',' . $db->quote('50'))
			    ->values('176,' . $db->quote('Мурманская область') . ',' . $db->quote('MUR') . ',' . $db->quote('51'))
			    ->values('176,' . $db->quote('Ненецкий автономный округ') . ',' . $db->quote('NEN') . ',' . $db->quote('83'))
			    ->values('176,' . $db->quote('Нижегородская область') . ',' . $db->quote('NIZ') . ',' . $db->quote('52'))
			    ->values('176,' . $db->quote('Новгородская область') . ',' . $db->quote('NGR') . ',' . $db->quote('53'))
			    ->values('176,' . $db->quote('Новосибирская область') . ',' . $db->quote('NVS') . ',' . $db->quote('54'))
			    ->values('176,' . $db->quote('Омская область') . ',' . $db->quote('OMS') . ',' . $db->quote('55'))
			    ->values('176,' . $db->quote('Оренбургская область') . ',' . $db->quote('ORE') . ',' . $db->quote('56'))
			    ->values('176,' . $db->quote('Орловская область') . ',' . $db->quote('ORL') . ',' . $db->quote('57'))
			    ->values('176,' . $db->quote('Пензенская область') . ',' . $db->quote('PNZ') . ',' . $db->quote('58'))
			    ->values('176,' . $db->quote('Пермский край') . ',' . $db->quote('PER') . ',' . $db->quote('59'))
			    ->values('176,' . $db->quote('Приморский край') . ',' . $db->quote('PRI') . ',' . $db->quote('25'))
			    ->values('176,' . $db->quote('Псковская область') . ',' . $db->quote('PSK') . ',' . $db->quote('60'))
			    ->values('176,' . $db->quote('Ростовская область') . ',' . $db->quote('ROS') . ',' . $db->quote('61'))
			    ->values('176,' . $db->quote('Рязанская область') . ',' . $db->quote('RYA') . ',' . $db->quote('62'))
			    ->values('176,' . $db->quote('Самарская область') . ',' . $db->quote('SAM') . ',' . $db->quote('63'))
			    ->values('176,' . $db->quote('Санкт-Петербург') . ',' . $db->quote('SPE') . ',' . $db->quote('78'))
			    ->values('176,' . $db->quote('Саратовская область') . ',' . $db->quote('SAR') . ',' . $db->quote('64'))
			    ->values('176,' . $db->quote('Саха (Якутия) Республика') . ',' . $db->quote('SA') . ',' . $db->quote('14'))
			    ->values('176,' . $db->quote('Сахалинская область') . ',' . $db->quote('SAK') . ',' . $db->quote('65'))
			    ->values('176,' . $db->quote('Свердловская область') . ',' . $db->quote('SVE') . ',' . $db->quote('66'))
			    ->values('176,' . $db->quote('Северная Осетия-Алания Республика') . ',' . $db->quote('SE') . ',' . $db->quote('15'))
			    ->values('176,' . $db->quote('Смоленская область') . ',' . $db->quote('SMO') . ',' . $db->quote('67'))
			    ->values('176,' . $db->quote('Ставропольский край') . ',' . $db->quote('STA') . ',' . $db->quote('26'))
			    ->values('176,' . $db->quote('Тамбовская область') . ',' . $db->quote('TAM') . ',' . $db->quote('68'))
			    ->values('176,' . $db->quote('Татарстан Республика') . ',' . $db->quote('TA') . ',' . $db->quote('16'))
			    ->values('176,' . $db->quote('Тверская область') . ',' . $db->quote('TVE') . ',' . $db->quote('69'))
			    ->values('176,' . $db->quote('Томская область') . ',' . $db->quote('TOM') . ',' . $db->quote('70'))
			    ->values('176,' . $db->quote('Тульская область') . ',' . $db->quote('TUL') . ',' . $db->quote('71'))
			    ->values('176,' . $db->quote('Тыва Республика') . ',' . $db->quote('TY') . ',' . $db->quote('17'))
			    ->values('176,' . $db->quote('Тюменская область') . ',' . $db->quote('TYU') . ',' . $db->quote('72'))
			    ->values('176,' . $db->quote('Удмуртская Республика') . ',' . $db->quote('UD') . ',' . $db->quote('18'))
			    ->values('176,' . $db->quote('Ульяновская область') . ',' . $db->quote('ULY') . ',' . $db->quote('73'))
			    ->values('176,' . $db->quote('Хакасия Республика') . ',' . $db->quote('KK') . ',' . $db->quote('19'))
			    ->values('176,' . $db->quote('Челябинская область') . ',' . $db->quote('CHE') . ',' . $db->quote('74'))
			    ->values('176,' . $db->quote('Чеченская Республика') . ',' . $db->quote('CE') . ',' . $db->quote('20'))
			    ->values('176,' . $db->quote('Чувашская Республика') . ',' . $db->quote('CU') . ',' . $db->quote('21'))
			    ->values('176,' . $db->quote('Чукотский автономный округ') . ',' . $db->quote('CHU') . ',' . $db->quote('87'))
			    ->values('176,' . $db->quote('Хабаровский край') . ',' . $db->quote('KHA') . ',' . $db->quote('27'))
			    ->values('176,' . $db->quote('Ханты-Мансийский автономный округ') . ',' . $db->quote('KHM') . ',' . $db->quote('86'))
			    ->values('176,' . $db->quote('Ямало-Ненецкий автономный округ') . ',' . $db->quote('YAN') . ',' . $db->quote('89'))
			    ->values('176,' . $db->quote('Ярославская область') . ',' . $db->quote('YAR') . ',' . $db->quote('76'));
			 
			// Reset the query using our newly populated query object.
			$db->setQuery($query);
			$db->query();
			$err[] = $db->getErrorMsg();
			$states = 1;
		}
	
		//$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('extension_id, enabled');
		$query->from('#__extensions');
		$query->where($db->quoteName('element') . ' LIKE ' . $db->quote('postcalc'));
	    $db->setQuery((string)$query);
	    $postcalc_ext = $db->loadAssoc();
	    $postcalc_id = (int)$postcalc_ext['extension_id'];
	    $postcalc_pub = (int)$postcalc_ext['enabled'];
		$err[] = $db->getErrorMsg();

		//Publish plugin
		if (isset($postcalc_id) && !$postcalc_pub) {
			// Create a new query object.
			$query = $db->getQuery(true);		 
			//Build the query
			$query->update('#__extensions');
			$query->set('enabled = '. $db->quote('1'));
			$query->where('extension_id = '. $db->quote($postcalc_id ));
			$db->setQuery($query);			
			$db->query();
			$err[] = $db->getErrorMsg();
		}
		
		if ($release == "1.0.9") {
			//return;
		}
		
		$query = $db->getQuery(true);
		$query = 'SELECT `virtuemart_shipmentmethod_id` FROM `#__virtuemart_shipmentmethods`'
		. ' WHERE `shipment_element` = ' . $db->quote('russianemspost')
		. ' OR `shipment_element` = ' . $db->quote('postcalc');
		$db->setQuery($query);
		$shipmentmethods = $db->loadResultArray();
		$err[] = $db->getErrorMsg();
		
		//Insert shipping variants
		if (!count($shipmentmethods) && $postcalc_id) {
			// Create a new query object.
			$query = $db->getQuery(true);		 
			// Insert columns.
			$columns = array('shipment_jplugin_id', 'shipment_element', 'shipment_params', 'published');
			// Insert values.
			//Usage: $query->values('1,2,3')->values('4,5,6'); $query->values(array('1,2,3', '4,5,6'));
			//$values = array(1001, $db->quote('custom.message'), $db->quote('Inserting a record using insert()'), 1);
			 
			// Prepare the insert query.
			$query
			    ->insert($db->quoteName('#__virtuemart_shipmentmethods'))
			    ->columns($db->quoteName($columns));
			    //->values(implode(',', $values));
			
			$query->values(array(
			$postcalc_id . ',' . $db->quote('postcalc') . ',' . $db->quote('shipment_logos=["ruspost.gif"]|postcalc_delivery="\\u041f\\u0440\\u043e\\u0441\\u0442\\u0430\\u044f\\u0411\\u0430\\u043d\\u0434\\u0435\\u0440\\u043e\\u043b\\u044c"|') . ',0',
			$postcalc_id . ',' . $db->quote('postcalc') . ',' . $db->quote('shipment_logos=["ruspost.gif"]|postcalc_delivery="\\u0417\\u0430\\u043a\\u0430\\u0437\\u043d\\u0430\\u044f\\u0411\\u0430\\u043d\\u0434\\u0435\\u0440\\u043e\\u043b\\u044c"|postcalc_valuation="0"|postcalc_payment_forward="0"|postcalc_extra=""||postcalc_countries=""|postcalc_states=""|postcalc_except="0"|postcalc_weight_limit="1"|') . ',0',
			$postcalc_id . ',' . $db->quote('postcalc') . ',' . $db->quote('shipment_logos=["ruspost.gif"]|postcalc_delivery="\\u0426\\u0435\\u043d\\u043d\\u0430\\u044f\\u0411\\u0430\\u043d\\u0434\\u0435\\u0440\\u043e\\u043b\\u044c"|postcalc_valuation="0"|postcalc_payment_forward="0"|postcalc_extra=""||postcalc_countries=""|postcalc_states=""|postcalc_except="0"|postcalc_weight_limit="1"|') . ',0',
			$postcalc_id . ',' . $db->quote('postcalc') . ',' . $db->quote('shipment_logos=["ruspost.gif"]|postcalc_delivery="\\u0426\\u0435\\u043d\\u043d\\u0430\\u044f\\u0410\\u0432\\u0438\\u0430\\u0411\\u0430\\u043d\\u0434\\u0435\\u0440\\u043e\\u043b\\u044c"|postcalc_valuation="0"|postcalc_payment_forward="0"|postcalc_extra=""||postcalc_countries=""|postcalc_states=""|postcalc_except="0"|postcalc_weight_limit="1"|') . ',0',
			$postcalc_id . ',' . $db->quote('postcalc') . ',' . $db->quote('shipment_logos=["ruspost.gif"]|postcalc_delivery="\\u0417\\u0430\\u043a\\u0430\\u0437\\u043d\\u0430\\u044f\\u0411\\u0430\\u043d\\u0434\\u0435\\u0440\\u043e\\u043b\\u044c1\\u041a\\u043b\\u0430\\u0441\\u0441"|postcalc_valuation="0"|postcalc_payment_forward="0"|postcalc_extra=""||postcalc_countries=""|postcalc_states=""|postcalc_except="0"|postcalc_weight_limit="1"|') . ',0',
			$postcalc_id . ',' . $db->quote('postcalc') . ',' . $db->quote('shipment_logos=["ruspost.gif"]|postcalc_delivery="\\u0426\\u0435\\u043d\\u043d\\u0430\\u044f\\u0411\\u0430\\u043d\\u0434\\u0435\\u0440\\u043e\\u043b\\u044c1\\u041a\\u043b\\u0430\\u0441\\u0441"|postcalc_valuation="0"|postcalc_payment_forward="0"|postcalc_extra=""||postcalc_countries=""|postcalc_states=""|postcalc_except="0"|postcalc_weight_limit="1"|') . ',0',
			$postcalc_id . ',' . $db->quote('postcalc') . ',' . $db->quote('shipment_logos=["ruspost.gif"]|postcalc_delivery="\\u0426\\u0435\\u043d\\u043d\\u0430\\u044f\\u041f\\u043e\\u0441\\u044b\\u043b\\u043a\\u0430"|postcalc_valuation="0"|postcalc_payment_forward="0"|postcalc_extra=""||postcalc_countries=""|postcalc_states=""|postcalc_except="0"|postcalc_weight_limit="1"|') . ',0',
			$postcalc_id . ',' . $db->quote('postcalc') . ',' . $db->quote('shipment_logos=["ruspost.gif"]|postcalc_delivery="\\u0426\\u0435\\u043d\\u043d\\u0430\\u044f\\u0410\\u0432\\u0438\\u0430\\u041f\\u043e\\u0441\\u044b\\u043b\\u043a\\u0430"|postcalc_valuation="0"|postcalc_payment_forward="0"|postcalc_extra=""||postcalc_countries=""|postcalc_states=""|postcalc_except="0"|postcalc_weight_limit="1"|') . ',0',
			$postcalc_id . ',' . $db->quote('postcalc') . ',' . $db->quote('shipment_logos=["emspost.gif"]|postcalc_delivery="EMS"|postcalc_valuation="0"|postcalc_payment_forward="0"|postcalc_extra=""||postcalc_countries=""|postcalc_states=""|postcalc_except="0"|postcalc_weight_limit="1"|') . ',0',
			$postcalc_id . ',' . $db->quote('postcalc') . ',' . $db->quote('shipment_logos=["ruspost.gif"]|postcalc_delivery="\\u041c\\u0436\\u0434\\u041c\\u0435\\u0448\\u043e\\u043a\\u041c"|postcalc_valuation="0"|postcalc_payment_forward="0"|postcalc_extra=""|postcalc_countries=""|postcalc_states=""|postcalc_except="0"|postcalc_weight_limit="1"|') . ',0',
			$postcalc_id . ',' . $db->quote('postcalc') . ',' . $db->quote('shipment_logos=["ruspost.gif"]|postcalc_delivery="\\u041c\\u0436\\u0434\\u041c\\u0435\\u0448\\u043e\\u043a\\u041c\\u0417\\u0430\\u043a\\u0430\\u0437\\u043d\\u043e\\u0439"|postcalc_valuation="0"|postcalc_payment_forward="0"|postcalc_extra=""|postcalc_countries=""|postcalc_states=""|postcalc_except="0"|postcalc_weight_limit="1"|') . ',0',
			$postcalc_id . ',' . $db->quote('postcalc') . ',' . $db->quote('shipment_logos=["ruspost.gif"]|postcalc_delivery="\\u041c\\u0436\\u0434\\u041c\\u0435\\u0448\\u043e\\u043a\\u041c\\u0410\\u0432\\u0438\\u0430"|postcalc_valuation="0"|postcalc_payment_forward="0"|postcalc_extra=""|postcalc_countries=""|postcalc_states=""|postcalc_except="0"|postcalc_weight_limit="1"|') . ',0',
			$postcalc_id . ',' . $db->quote('postcalc') . ',' . $db->quote('shipment_logos=["ruspost.gif"]|postcalc_delivery="\\u041c\\u0436\\u0434\\u041c\\u0435\\u0448\\u043e\\u043a\\u041c\\u0410\\u0432\\u0438\\u0430\\u0417\\u0430\\u043a\\u0430\\u0437\\u043d\\u043e\\u0439"|postcalc_valuation="0"|postcalc_payment_forward="0"|postcalc_extra=""|postcalc_countries=""|postcalc_states=""|postcalc_except="0"|postcalc_weight_limit="1"|') . ',0',
			$postcalc_id . ',' . $db->quote('postcalc') . ',' . $db->quote('shipment_logos=["ruspost.gif"]|postcalc_delivery="\\u041c\\u0436\\u0434\\u0411\\u0430\\u043d\\u0434\\u0435\\u0440\\u043e\\u043b\\u044c"|postcalc_valuation="0"|postcalc_payment_forward="0"|postcalc_extra=""|postcalc_countries=""|postcalc_states=""|postcalc_except="0"|postcalc_weight_limit="1"|') . ',0',
			$postcalc_id . ',' . $db->quote('postcalc') . ',' . $db->quote('shipment_logos=["ruspost.gif"]|postcalc_delivery="\\u041c\\u0436\\u0434\\u0411\\u0430\\u043d\\u0434\\u0435\\u0440\\u043e\\u043b\\u044c\\u0417\\u0430\\u043a\\u0430\\u0437\\u043d\\u0430\\u044f"|postcalc_valuation="0"|postcalc_payment_forward="0"|postcalc_extra=""|postcalc_countries=""|postcalc_states=""|postcalc_except="0"|postcalc_weight_limit="1"|') . ',0',
			$postcalc_id . ',' . $db->quote('postcalc') . ',' . $db->quote('shipment_logos=["ruspost.gif"]|postcalc_delivery="\\u041c\\u0436\\u0434\\u0411\\u0430\\u043d\\u0434\\u0435\\u0440\\u043e\\u043b\\u044c\\u0410\\u0432\\u0438\\u0430"|postcalc_valuation="0"|postcalc_payment_forward="0"|postcalc_extra=""|postcalc_countries=""|postcalc_states=""|postcalc_except="0"|postcalc_weight_limit="1"|') . ',0',
			$postcalc_id . ',' . $db->quote('postcalc') . ',' . $db->quote('shipment_logos=["ruspost.gif"]|postcalc_delivery="\\u041c\\u0436\\u0434\\u0411\\u0430\\u043d\\u0434\\u0435\\u0440\\u043e\\u043b\\u044c\\u0410\\u0432\\u0438\\u0430\\u0417\\u0430\\u043a\\u0430\\u0437\\u043d\\u0430\\u044f"|postcalc_valuation="0"|postcalc_payment_forward="0"|postcalc_extra=""|postcalc_countries=""|postcalc_states=""|postcalc_except="0"|postcalc_weight_limit="1"|') . ',0',
			$postcalc_id . ',' . $db->quote('postcalc') . ',' . $db->quote('shipment_logos=["ruspost.gif"]|postcalc_delivery="\\u041c\\u0436\\u0434\\u041c\\u0435\\u043b\\u043a\\u0438\\u0439\\u041f\\u0430\\u043a\\u0435\\u0442"|postcalc_valuation="0"|postcalc_payment_forward="0"|postcalc_extra=""|postcalc_countries=""|postcalc_states=""|postcalc_except="0"|postcalc_weight_limit="1"|') . ',0',
			$postcalc_id . ',' . $db->quote('postcalc') . ',' . $db->quote('shipment_logos=["ruspost.gif"]|postcalc_delivery="\\u041c\\u0436\\u0434\\u041c\\u0435\\u043b\\u043a\\u0438\\u0439\\u041f\\u0430\\u043a\\u0435\\u0442\\u0417\\u0430\\u043a\\u0430\\u0437\\u043d\\u043e\\u0439"|postcalc_valuation="0"|postcalc_payment_forward="0"|postcalc_extra=""|postcalc_countries=""|postcalc_states=""|postcalc_except="0"|postcalc_weight_limit="1"|') . ',0',
			$postcalc_id . ',' . $db->quote('postcalc') . ',' . $db->quote('shipment_logos=["ruspost.gif"]|postcalc_delivery="\\u041c\\u0436\\u0434\\u041c\\u0435\\u043b\\u043a\\u0438\\u0439\\u041f\\u0430\\u043a\\u0435\\u0442\\u0410\\u0432\\u0438\\u0430"|postcalc_valuation="0"|postcalc_payment_forward="0"|postcalc_extra=""|postcalc_countries=""|postcalc_states=""|postcalc_except="0"|postcalc_weight_limit="1"|') . ',0',
			$postcalc_id . ',' . $db->quote('postcalc') . ',' . $db->quote('shipment_logos=["ruspost.gif"]|postcalc_delivery="\\u041c\\u0436\\u0434\\u041c\\u0435\\u043b\\u043a\\u0438\\u0439\\u041f\\u0430\\u043a\\u0435\\u0442\\u0410\\u0432\\u0438\\u0430\\u0417\\u0430\\u043a\\u0430\\u0437\\u043d\\u043e\\u0439"|postcalc_valuation="0"|postcalc_payment_forward="0"|postcalc_extra=""|postcalc_countries=""|postcalc_states=""|postcalc_except="0"|postcalc_weight_limit="1"|') . ',0',
			$postcalc_id . ',' . $db->quote('postcalc') . ',' . $db->quote('shipment_logos=["emspost.gif"]|postcalc_delivery="EMS_\\u041c\\u0436\\u0434\\u0422\\u043e\\u0432\\u0430\\u0440\\u044b"|postcalc_valuation="0"|postcalc_payment_forward="0"|postcalc_extra=""|postcalc_countries=""|postcalc_states=""|postcalc_except="0"|postcalc_weight_limit="1"|') . ',0'));
	
			// Reset the query using our newly populated query object.
			$db->setQuery($query);
			$db->query();
			
			// Return the new id.
			$insertid = (int) $db->insertid();
			$err[] = $db->getErrorMsg();
	
			// Create a new query object.
			$query = $db->getQuery(true);		 
			// Insert columns.
			$columns = array('virtuemart_shipmentmethod_id', 'shipment_name', 'shipment_desc', 'slug');
			// Insert values.
			//Usage: $query->values('1,2,3')->values('4,5,6'); $query->values(array('1,2,3', '4,5,6'));
			//$values = array(1001, $db->quote('custom.message'), $db->quote('Inserting a record using insert()'), 1);
			 
			$params = JComponentHelper::getParams('com_languages');
			$lang = $params->get('site', 'ru-RU');//use default Russian
			$lang = strtolower(strtr($lang,'-','_'));
	
			// Prepare the insert query.
			$query
			    ->insert($db->quoteName('#__virtuemart_shipmentmethods_'.$lang))
			    ->columns($db->quoteName($columns));
			    //->values(implode(',', $values));
			
			$query->values(array(
			$insertid . ',' . $db->quote('Простая бандероль') . ',' . $db->quote('Почта России') . ',' . $db->quote('prostaya-banderol'),
			($insertid+1) . ',' . $db->quote('Заказная бандероль') . ',' . $db->quote('Почта России') . ',' . $db->quote('zakaznaya-banderol'),
			($insertid+2) . ',' . $db->quote('Ценная бандероль') . ',' . $db->quote('Почта России') . ',' . $db->quote('tsennaya-banderol'),
			($insertid+3) . ',' . $db->quote('Ценная авиабандероль') . ',' . $db->quote('Почта России') . ',' . $db->quote('tsennaya-aviabanderol'),
			($insertid+4) . ',' . $db->quote('Заказная бандероль 1 класса') . ',' . $db->quote('Почта России') . ',' . $db->quote('zakaznaya-banderol-1-klassa'),
			($insertid+5) . ',' . $db->quote('Ценная бандероль 1 класс') . ',' . $db->quote('Почта России') . ',' . $db->quote('tsennaya-banderol-1-klass'),
			($insertid+6) . ',' . $db->quote('Ценная посылка') . ',' . $db->quote('Почта России') . ',' . $db->quote('tsennaya-posylka'),
			($insertid+7) . ',' . $db->quote('Ценная авиапосылка') . ',' . $db->quote('Почта России') . ',' . $db->quote('tsennaya-aviaposylka'),
			($insertid+8) . ',' . $db->quote('Курьерская экспресс-доставка') . ',' . $db->quote('EMS Почта России') . ',' . $db->quote('kurerskaya-ekspress-dostavka'),
			($insertid+9) . ',' . $db->quote('Международный мешок М') . ',' . $db->quote('Почта России') . ',' . $db->quote('mezhdunarodnyj-meshok-m'),
			($insertid+10) . ',' . $db->quote('Международный заказной мешок М') . ',' . $db->quote('Почта России') . ',' . $db->quote('mezhdunarodnyj-zakaznoj-meshok-m'),
			($insertid+11) . ',' . $db->quote('Международный мешок М авиа-отправлением') . ',' . $db->quote('Почта России') . ',' . $db->quote('mezhdunarodnyj-meshok-m-avia-otpravleniem'),
			($insertid+12) . ',' . $db->quote('Международный заказной мешок М авиа-отправлением') . ',' . $db->quote('Почта России') . ',' . $db->quote('mezhdunarodnyj-zakaznoj-meshok-m-avia-otpravleniem'),
			($insertid+13) . ',' . $db->quote('Международная бандероль') . ',' . $db->quote('Почта России') . ',' . $db->quote('mezhdunarodnaya-banderol'),
			($insertid+14) . ',' . $db->quote('Международная заказная бандероль') . ',' . $db->quote('Почта России') . ',' . $db->quote('mezhdunarodnaya-zakaznaya-banderol'),
			($insertid+15) . ',' . $db->quote('Международная бандероль авиа-отправлением') . ',' . $db->quote('Почта России') . ',' . $db->quote('mezhdunarodnaya-banderol-avia-otpravleniem'),
			($insertid+16) . ',' . $db->quote('Международная заказная бандероль авиа-отправлением') . ',' . $db->quote('Почта России') . ',' . $db->quote('mezhdunarodnaya-zakaznaya-banderol-avia-otpravleniem'),
			($insertid+17) . ',' . $db->quote('Международный мелкий пакет') . ',' . $db->quote('Почта России') . ',' . $db->quote('mezhdunarodnyj-melkij-paket'),
			($insertid+18) . ',' . $db->quote('Международный заказной мелкий пакет') . ',' . $db->quote('Почта России') . ',' . $db->quote('mezhdunarodnyj-zakaznoj-melkij-paket'),
			($insertid+19) . ',' . $db->quote('Международный мелкий пакет авиа-отправлением') . ',' . $db->quote('Почта России') . ',' . $db->quote('mezhdunarodnyj-melkij-paket-avia-otpravleniem'),
			($insertid+20) . ',' . $db->quote('Международный заказной мелкий пакет авиа-отправлением') . ',' . $db->quote('Почта России') . ',' . $db->quote('mezhdunarodnyj-zakaznoj-melkij-paket-avia-otpravleniem'),
			($insertid+21) . ',' . $db->quote('Международная экспресс-доставка') . ',' . $db->quote('EMS Почта России') . ',' . $db->quote('mezhdunarodnaya-ekspress-dostavka')));
	
			// Reset the query using our newly populated query object.
			$db->setQuery($query);
			$db->query();
			$err[] = $db->getErrorMsg();
		}
		elseif (count($shipmentmethods) && $postcalc_id) {
			// Create a new query object.
			$query = $db->getQuery(true);		 
			//Build the query
			$query->update('#__virtuemart_shipmentmethods');
			$query->set('shipment_jplugin_id = ' . $db->quote($postcalc_id));
			$query->set('shipment_element = ' . $db->quote('postcalc'));
			$query->where('shipment_element = ' . $db->quote('postcalc') 
			. ' OR shipment_element = ' . $db->quote('russianemspost'));
			$db->setQuery($query);			
			$db->query();
			$err[] = $db->getErrorMsg();			
		}
		/*echo "<pre>";
		print_r($db);
		print_r($err);
		echo "</pre>";
		exit;
		*/
	}
}
