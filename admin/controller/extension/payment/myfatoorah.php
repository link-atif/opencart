<?php
class ControllerExtensionPaymentMyfatoorah extends Controller {

    public function index() {
        // Load language
        $this->load->language('extension/payment/myfatoorah');

        // Load settings
        $this->load->model('setting/setting');

        // Set document title
        $this->document->setTitle($this->language->get('heading_title'));

        if (isset($this->session->data['success']))
        {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        // If isset request to change settings
        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
          

            // Edit settings
            $this->model_setting_setting->editSetting('myfatoorah', $this->request->post);
            
            $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '0', `code` = '" . $this->db->escape('myfatoorah') . "', `key` = '" . $this->db->escape('payment_myfatoorah_status') . "', `value` = '" . $this->db->escape($this->request->post['payment_myfatoorah_status']) . "'");

            // Set success message
            $this->session->data['success'] = $this->language->get('text_success');

            // Return to extensions page
            $this->response->redirect($this->url->link('extension/payment/myfatoorah', 'user_token=' . $this->session->data['user_token'], 'SSL'));
        }

        // Load default layout
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        // Load language variables
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_all_zones'] = $this->language->get('text_all_zones');
        $data['text_mypos_join'] = $this->language->get('text_mypos_join');
        $data['help_total'] = $this->language->get('help_total');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        // Field entries
        $data['entry_status']			   = $this->language->get('entry_status');
        $data['entry_test']		           = $this->language->get('entry_test');
        $data['entry_sid']				   = $this->language->get('entry_sid');
        $data['entry_wallet_number']	   = $this->language->get('entry_wallet_number');
        $data['entry_private_key']		   = $this->language->get('entry_private_key');
        $data['entry_public_certificate']  = $this->language->get('entry_public_certificate');
        $data['entry_developer_keyindex']  = $this->language->get('entry_developer_keyindex');
        $data['entry_production_keyindex'] = $this->language->get('entry_production_keyindex');
        $data['entry_developer_url']	   = $this->language->get('entry_developer_url');
        $data['entry_production_url']	   = $this->language->get('entry_production_url');
        $data['entry_sort_order']  	       = $this->language->get('entry_sort_order');
        $data['entry_logging']  	       = $this->language->get('entry_logging');

        $data['username'] = $this->language->get('username');
        $data['password'] = $this->language->get('password');
        $data['merchant_code'] = $this->language->get('merchant_code');
        $data['merchant_username'] = $this->language->get('merchant_username');
        $data['merchant_password'] = $this->language->get('merchant_password');
        $data['return_url'] = $this->language->get('return_url');
        $data['merchant_error_url'] = $this->language->get('merchant_error_url');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');
        $data['entry_order_status'] = $this->language->get('entry_order_status');


        // Tooltips
        $data['tooltip_sid'] = $this->language->get('tooltip_sid');
        $data['tooltip_wallet_number'] = $this->language->get('tooltip_wallet_number');
        $data['tooltip_private_key'] = $this->language->get('tooltip_private_key');
        $data['tooltip_public_certificate'] = $this->language->get('tooltip_public_certificate');
        $data['tooltip_keyindex'] = $this->language->get('tooltip_keyindex');

        // Load breadcrumbs
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('extension/payment', 'user_token=' . $this->session->data['user_token'], 'SSL')
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('payment/cod', 'user_token=' . $this->session->data['user_token'], 'SSL')
        );

        // Load action buttons urls
        $data['action'] = $this->url->link('extension/payment/myfatoorah', 'user_token=' . $this->session->data['user_token'], 'SSL');
        $data['cancel'] = $this->url->link('extension/payment', 'user_token=' . $this->session->data['user_token'], 'SSL');

        // Set default values for fields

        // Status of payment module Enabled/Disabled
        if (isset($this->request->post['payment_myfatoorah_status'])) {
            $data['payment_myfatoorah_status'] = $this->request->post['payment_myfatoorah_status'];
        } else {
            $data['payment_myfatoorah_status'] = $this->config->get('payment_myfatoorah_status');
        }


        if (isset($this->request->post['myfatoorah_username'])) {
            $data['myfatoorah_username'] = $this->request->post['myfatoorah_username'];
        } else {
            $data['myfatoorah_username'] = $this->config->get('myfatoorah_username');
        }

        if (isset($this->request->post['myfatoorah_password'])) {
            $data['myfatoorah_password'] = $this->request->post['myfatoorah_password'];
        } else {
            $data['myfatoorah_password'] = $this->config->get('myfatoorah_password');
        }

        if (isset($this->request->post['myfatoorah_merchant_username'])) {
            $data['myfatoorah_merchant_username'] = $this->request->post['myfatoorah_merchant_username'];
        } else {
            $data['myfatoorah_merchant_username'] = $this->config->get('myfatoorah_merchant_username');
        }

        if (isset($this->request->post['myfatoorah_merchant_password'])) {
            $data['myfatoorah_merchant_password'] = $this->request->post['myfatoorah_merchant_password'];
        } else {
            $data['myfatoorah_merchant_password'] = $this->config->get('myfatoorah_merchant_password');
        }

        if (isset($this->request->post['myfatoorah_return_url'])) {
            $data['myfatoorah_return_url'] = $this->request->post['myfatoorah_return_url'];
        } else {
            $data['myfatoorah_return_url'] = $this->config->get('myfatoorah_return_url');
        }

        if (isset($this->request->post['myfatoorah_gateway_url'])) {			$data['myfatoorah_gateway_url'] = $this->request->post['myfatoorah_gateway_url'];		} else {			$data['myfatoorah_gateway_url'] = $this->config->get('myfatoorah_gateway_url'); 		}

	if (isset($this->request->post['myfatoorah_payment_type'])) {			
		$data['myfatoorah_payment_type'] = $this->request->post['myfatoorah_payment_type'];		
	} else {
		$data['myfatoorah_payment_type'] = $this->config->get('myfatoorah_payment_type'); 		
	}

        if (isset($this->request->post['myfatoorah_merchant_error_url'])) {
            $data['myfatoorah_merchant_error_url'] = $this->request->post['myfatoorah_merchant_error_url'];
        } else {
            $data['myfatoorah_merchant_error_url'] = $this->config->get('myfatoorah_merchant_error_url');
        }


        if (isset($this->request->post['myfatoorah_total'])) {
            $data['myfatoorah_total'] = $this->request->post['myfatoorah_total'];
        } else {
            $data['myfatoorah_total'] = $this->config->get('myfatoorah_total');
        }

        if (isset($this->request->post['myfatoorah_order_status_id'])) {
            $data['myfatoorah_order_status_id'] = $this->request->post['myfatoorah_order_status_id'];
        } else {
            $data['myfatoorah_order_status_id'] = $this->config->get('myfatoorah_order_status_id');
        }


        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['myfatoorah_status'])) {
            $data['myfatoorah_status'] = $this->request->post['myfatoorah_status'];
        } else {
            $data['myfatoorah_status'] = $this->config->get('myfatoorah_status');
        }

        if (isset($this->request->post['myfatoorah_sort_order'])) {
            $data['myfatoorah_sort_order'] = $this->request->post['myfatoorah_sort_order'];
        } else {
            $data['myfatoorah_sort_order'] = $this->config->get('myfatoorah_sort_order');
        }

        // Logging Enabled/Disabled
        if (isset($this->request->post['myfatoorah_logging'])) {
            $data['myfatoorah_logging'] = $this->request->post['myfatoorah_logging'];
        } else {
            $data['myfatoorah_logging'] = $this->config->get('myfatoorah_logging');
        }

        // Sort order
        if (isset($this->request->post['myfatoorah_sort_order'])) {
            $data['myfatoorah_sort_order'] = $this->request->post['myfatoorah_sort_order'];
        } else {
            $data['myfatoorah_sort_order'] = $this->config->get('myfatoorah_sort_order');
        }

        // Test / Production
        if (isset($this->request->post['myfatoorah_test'])) {
            $data['myfatoorah_test'] = $this->request->post['myfatoorah_test'];
        } else {
            $data['myfatoorah_test'] = $this->config->get('myfatoorah_test');
        }

        // Site ID
        if (isset($this->request->post['myfatoorah_developer_sid'])) {
            $data['myfatoorah_developer_sid'] = $this->request->post['myfatoorah_developer_sid'];
        } else {
            $data['myfatoorah_developer_sid'] = $this->config->get('myfatoorah_developer_sid');
        }

        // Wallet number
        if (isset($this->request->post['myfatoorah_developer_wallet_number'])) {
            $data['myfatoorah_developer_wallet_number'] = $this->request->post['myfatoorah_developer_wallet_number'];
        } else {
            $data['myfatoorah_developer_wallet_number'] = $this->config->get('myfatoorah_developer_wallet_number');
        }

        // Private key
        if (isset($this->request->post['myfatoorah_developer_private_key'])) {
            $data['myfatoorah_developer_private_key'] = trim($this->request->post['myfatoorah_developer_private_key']);
        } else {
            $data['myfatoorah_developer_private_key'] = trim($this->config->get('myfatoorah_developer_private_key'));
        }

        // Public certificate
        if (isset($this->request->post['myfatoorah_developer_public_certificate'])) {
            $data['myfatoorah_developer_public_certificate'] = trim($this->request->post['myfatoorah_developer_public_certificate']);
        } else {
            $data['myfatoorah_developer_public_certificate'] = trim($this->config->get('myfatoorah_developer_public_certificate'));
        }

        // Developer url
        if (isset($this->request->post['myfatoorah_developer_url'])) {
            $data['myfatoorah_developer_url'] = $this->request->post['myfatoorah_developer_url'];
        } else {
            $data['myfatoorah_developer_url'] = $this->config->get('myfatoorah_developer_url');
        }

        // Developer keyindex
        if (isset($this->request->post['myfatoorah_developer_keyindex'])) {
            $data['myfatoorah_developer_keyindex'] = $this->request->post['myfatoorah_developer_keyindex'];
        } else {
            $data['myfatoorah_developer_keyindex'] = $this->config->get('myfatoorah_developer_keyindex');
        }

        // Site ID
        if (isset($this->request->post['myfatoorah_production_sid'])) {
            $data['myfatoorah_production_sid'] = $this->request->post['myfatoorah_production_sid'];
        } else {
            $data['myfatoorah_production_sid'] = $this->config->get('myfatoorah_production_sid');
        }

        // Wallet number
        if (isset($this->request->post['myfatoorah_production_wallet_number'])) {
            $data['myfatoorah_production_wallet_number'] = $this->request->post['myfatoorah_production_wallet_number'];
        } else {
            $data['myfatoorah_production_wallet_number'] = $this->config->get('myfatoorah_production_wallet_number');
        }

        // Private key
        if (isset($this->request->post['myfatoorah_production_private_key'])) {
            $data['myfatoorah_production_private_key'] = trim($this->request->post['myfatoorah_production_private_key']);
        } else {
            $data['myfatoorah_production_private_key'] = trim($this->config->get('myfatoorah_production_private_key'));
        }

        // Public certificate
        if (isset($this->request->post['myfatoorah_production_public_certificate'])) {
            $data['myfatoorah_production_public_certificate'] = trim($this->request->post['myfatoorah_production_public_certificate']);
        } else {
            $data['myfatoorah_production_public_certificate'] = trim($this->config->get('myfatoorah_production_public_certificate'));
        }

        // Production url
        if (isset($this->request->post['myfatoorah_production_url'])) {
            $data['myfatoorah_production_url'] = $this->request->post['myfatoorah_production_url'];
        } else {
            $data['myfatoorah_production_url'] = $this->config->get('myfatoorah_production_url');
        }

        // Production keyindex
        if (isset($this->request->post['myfatoorah_production_keyindex'])) {
            $data['myfatoorah_production_keyindex'] = $this->request->post['myfatoorah_production_keyindex'];
        } else {
            $data['myfatoorah_production_keyindex'] = $this->config->get('myfatoorah_production_keyindex');
        }

        // Default values

        if ($data['myfatoorah_developer_url'] == null)
        {
            $data['myfatoorah_developer_url'] = 'https://www.mypos.eu/vmp/checkout-test';
        }

        if ($data['myfatoorah_developer_keyindex'] == null)
        {
            $data['myfatoorah_developer_keyindex'] = '1';
        }

        if ($data['myfatoorah_production_url'] == null)
        {
            $data['myfatoorah_production_url'] = 'https://www.mypos.eu/vmp/checkout';
        }

        if ($data['myfatoorah_production_keyindex'] == null)
        {
            $data['myfatoorah_production_keyindex'] = '1';
        }

        $this->response->setOutput($this->load->view('extension/payment/myfatoorahtpl', $data));
    }
}
