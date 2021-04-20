<?php
class ModelExtensionPaymentMyfatoorah extends Model {
	public function getMethod($address, $total) {
		$this->load->language('extension/payment/myfatoorah');
            $method_data = array(
                'code'       => 'myfatoorah',
                'title'      => $this->language->get('text_title'),
                'terms'      => '',
                'sort_order' => $this->config->get('myfatoorah_sort_order')
            );

        return $method_data;
	}
}