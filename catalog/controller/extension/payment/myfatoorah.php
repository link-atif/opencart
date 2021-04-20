<?php

class ControllerExtensionPaymentMyfatoorah extends Controller {

    /**
     * @var Log $log
     */
    private $log;
    private $isTest;
    private $keyindex;
    private $sid;
    private $wallet_number;
    private $private_key;
    private $public_key;
    private $version;
    private $action;
    private $logging;
    private $token;

    public function __construct($registry) {
        parent::__construct($registry);

        $this->log = new Log('myfatoorah.log');

        $this->isTest = $this->config->get('myfatoorah_test') === '1' ? true : false;
        $this->logging = $this->config->get('myfatoorah_logging') === '1' ? true : false;
        $this->version = '1.1';

        if (!$this->isTest) {
            $this->merchant_username = $this->config->get('myfatoorah_merchant_username');
            $this->merchant_password = $this->config->get('myfatoorah_merchant_password');
		$url = parse_url($this->config->get('myfatoorah_gateway_url'));
        	$this->gateway_url = $url['scheme'].'://'.$url['host'];

        } else {
            $this->merchant_username = 'apiaccount@myfatoorah.com';
            $this->merchant_password = 'api12345*';
            $this->gateway_url = "https://apidemo.myfatoorah.com";
        }
    }

    public function index() {
        $this->language->load('extension/payment/myfatoorah');

        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['action'] = 'index.php?route=extension/payment/myfatoorah/confirm';
        $data['continue'] = $this->url->link('checkout/success');

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/payment/myfatoorahtpl')) {
            return $this->load->view($this->config->get('config_template') . '/template/extension/payment/myfatoorahtpl', $data);
        } else {
            return $this->load->view('extension/payment/myfatoorahtpl', $data);
        }
    }

    public function confirm() {
        $t = time();
        $url = $this->gateway_url;
        $this->load->model('checkout/order');

        $products = $this->cart->getProducts();
        if (isset($_SESSION['default']['shipping_method'])) {
            unset($_SESSION['default']['shipping_method']);
        }

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
       
        // generate token
        // check currency in myfatoorah
        $this->getToken();

        $this->language->load('extension/payment/myfatoorah');
	    $currencyIso = $this->checkCountryAndCurrency($order_info['currency_code']);
        if(!$currencyIso ){
            // go to checkout and err msg Curreny not supported with payment gateway
            $this->session->data['error'] = $order_info['currency_code'].' '.$this->language->get('curreny_error');
            $this->response->redirect('index.php?route=checkout/checkout'); 
        }
        // 
        
        $the_total = 0;
        $invoiceItemsArr = array();
        foreach ($products as $product) {
            if (trim($product['name']) == '')
                continue;
            $invoiceItemsArr[] = array('ProductId' => '', 'ProductName' => $product['name'], 'Quantity' => $product['quantity'], 'UnitPrice' => $product['price'] * $order_info['currency_value']);
            $the_total += $product['price'] * $order_info['currency_value'];
        }
        $total = $this->cart->gettotal();
        // Totals
        $this->load->model('setting/extension');

        $totals = array();
        $taxes = $this->cart->getTaxes();
        $total = 0;

        // Because __call can not keep var references so we put them into an array. 			
        $total_data = array(
            'totals' => &$totals,
            'taxes' => &$taxes,
            'total' => &$total
        );
        // get discount, taxes, shipping, total and subtotal prices

        if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
            $sort_order = array();

            $results = $this->model_setting_extension->getExtensions('total');

            foreach ($results as $key => $value) {
                $sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
            }

            array_multisort($sort_order, SORT_ASC, $results);

            foreach ($results as $result) {
                if ($this->config->get('total_' . $result['code'] . '_status')) {
                    $this->load->model('extension/total/' . $result['code']);

                    // We have to put the totals in an array so that they pass by reference.
                    $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
                }
            }

            $sort_order = array();

            foreach ($totals as $key => $value) {
                $sort_order[$key] = $value['sort_order'];
            }

            array_multisort($sort_order, SORT_ASC, $totals);
        }

        $subTotal = $totalPrice = null;
        foreach ($totals as $total) {
            if ($total['code'] == 'sub_total') {
                $subTotal = $total['value'];
            } else if ($total['code'] == 'total') {
                $totalPrice = $total['value'];
            } else {

                $invoiceItemsArr[] = array('ProductId' => '', 'ProductName' => $total['title'], 'Quantity' => 1, 'UnitPrice' => $total['value'] * $order_info['currency_value']);
            }
        }
	// Gift Voucher

        if (!empty($this->session->data['vouchers'])) {
            foreach ($this->session->data['vouchers'] as $voucher) {
                $invoiceItemsArr[] = array('ProductId' => '', 'ProductName' => $voucher['description'], 'Quantity' => 1, 'UnitPrice' => $voucher['amount'] * $order_info['currency_value']);
            }
        }

        $fname = $this->customer->getFirstName();
        $lname = $this->customer->getLastName();
        $name = isset($fname, $lname) ? $fname . $lname : $this->session->data['guest']['lastname'] . $this->session->data['guest']['firstname'];

        $gemail = $this->customer->getEmail();
        $email = isset($gemail) ? $gemail : $this->session->data['guest']['email']; //"harbourspace@gmail.com";

        $gtelephone = $this->customer->getTelephone();
        $telephone = isset($gtelephone) ? $gtelephone : $this->session->data['guest']['telephone']; //"1234567890";

    	$lang = $this->language->get('code'); 
    	$language = 2;
    	if($lang =='ar'){
    		$language = 1;
    	}
        // json data 
        $curl_data = '{
            "InvoiceValue": ' . $t . ',
            "CustomerName": "' . $name . '",
            "CustomerBlock": "",
            "CustomerStreet": "",
            "CustomerHouseBuildingNo": "string",
            "CustomerCivilId": "2",
            "CustomerAddress": "string",
            "CustomerReference": "'.$order_info['order_id'].'",
            "DisplayCurrencyIsoAlpha":"'.$currencyIso.'",
            "CountryCodeId": 0,
            "CustomerMobile": "' . $this->checkTelephone($telephone) . '",
            "CustomerEmail": "' . $email . '",
            "SendInvoiceOption": 2,
            "InvoiceItemsCreate": ' . json_encode($invoiceItemsArr) . ',
            "CallBackUrl": "' .  htmlentities($this->url->link('extension/payment/myfatoorah/callback'))  . '",
            "Language": '.$language.',
            "ExpireDate": "'.gmdate("D, d M Y H:i:s", time() + 7 * 24 * 60 * 60).'",
            "ApiCustomFileds": "string",
          	"ErrorUrl": "' .  htmlentities($this->url->link('extension/payment/myfatoorah/callback'))  . '"
    	}';
        //echo $curl_data; die;
        // call rest api 
        $api_invoice = $this->gateway_url . '/ApiInvoices/CreateInvoiceIso';
        $result = '';
        do {
            $retry = false;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_invoice);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $curl_data);

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "Accept: application/json",
                "Authorization: $this->token"
            ));

            $res = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            $result = json_decode($res);
//echo $res; die;
            if ($httpcode === 401) { // unauthorized
                $this->getToken();
                $retry = true;
            }
            curl_close($ch);
        } while ($retry);
        $redirectUrl ='';
        if (isset($result->IsSuccess) && $result->IsSuccess ) {
            foreach($result->PaymentMethods as $PaymentMethod){
                if($PaymentMethod->PaymentMethodCode === $this->config->get('myfatoorah_payment_type')){
                    $redirectUrl = $PaymentMethod->PaymentMethodUrl;
                }
            }
            if($redirectUrl =='' || $this->api_gateway_payment === 'myfatoorah'){
                $redirectUrl =  $result->RedirectUrl;
            }
            $this->response->redirect($redirectUrl);
        } else {
            foreach($result->FieldsErrors as $error){
                if($error->Name == 'CustomerMobile'){
                   $this->session->data['error'] = $this->language->get('Phone_error');
                }else{
                    $this->session->data['error'] =$error->Name .' '.$error->Error;

                }
            } 
            $this->response->redirect('index.php?route=checkout/checkout'); 
        }
        
    }

    function checkTelephone($phone){
        $code = array('+973','+965','+968','+974','+962','+966','+971','00973','00965','00968','00974','00962','00966','00971');
        $result = trim($phone);
        foreach ( $code as $value){
            if(strpos($phone,$value) !== false){
                $result = str_replace($value,'',trim($phone));
            }
        }
        return $result; 
    }

       function checkCountryAndCurrency($cur) {
        $url = $this->gateway_url . '/AuthLists/GetCountiesWithCurrenciesIso';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Accept: application/json",
            "Content-Length: 0",
            "Authorization: ".$this->token
        ));

        $res = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        $result = json_decode($res);
        curl_close($ch);
//print_r($result); die;
        if($httpcode == 200){
            foreach ($result as $value) {
                if (strpos($value->Text, $cur) !== false || strpos($value->Value, $cur) !== false) {
                    return $value->Value;
                    exit;
                }
            }
        }
        return false;
    }

    function getToken() {
        $url_token = $this->gateway_url . '/Token';
        $client_id = $this->merchant_username;
        $client_secret = $this->merchant_password;
        $params = "grant_type=password"
                . "&username=" . $client_id
                . "&password=" . $client_secret;

        $curl = curl_init($url_token);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        $json_response = curl_exec($curl);
        $response = json_decode($json_response, true);
	if(!empty($response['access_token'])){
        	$this->token = $response['token_type'] . ' ' . $response['access_token'];
        }else{
       		$this->language->load('extension/payment/myfatoorah');

            $this->session->data['error'] = $this->language->get('token_error');
            $this->response->redirect('index.php?route=checkout/checkout'); 
        }
    }

    public function callback() {
        $paymentId = $_REQUEST['paymentId'];
        $url = $this->gateway_url . '/ApiInvoices/Transaction/' . $paymentId;
        $this->getToken();
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Accept: application/json",
            "Authorization: $this->token"
        ));

        $json_response = curl_exec($curl);
        $err = curl_error($curl);
        $response = json_decode($json_response, true);
        curl_close($curl);
//        echo "<pre>";
//        print_r($response);
//        die;
        if ($response['TransactionStatus'] === 2) {
            $this->language->load('checkout/success');
            $this->load->model('checkout/order');

            $data['text_title'] = $this->language->get('heading_title');
            $data['text_success'] = $this->language->get('text_success');

            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('myfatoorah_order_status_id'));

            $this->response->redirect('index.php?route=checkout/success');
        } else {
            $this->language->load('checkout/failure');
            $data['text_failure'] = $this->language->get('text_failure');
            $data['continue'] = $this->url->link('checkout/cart');

            $this->response->redirect('index.php?route=checkout/failure');
        }
    }

    private function log($message) {
        if ($this->logging) {
            $this->log->write($message);
        }
    }

}


