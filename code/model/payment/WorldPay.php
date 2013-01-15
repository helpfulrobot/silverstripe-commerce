<?php

class WorldPay extends CommercePaymentMethod {

    public $Title = 'WorldPay';

    public static $db = array(
        'InstallID' => 'Varchar(10)'
    );
    
    public function getCMSFields() {
        $fields = parent::getCMSFields();
        
        if($this->ID)
            $fields->addFieldToTab('Root.Main', TextField::create('InstallID', 'Instalation ID'));
        
        return $fields;
    }
    
    public function getGatewayFields() {
        $order = Session::get('Order');
        $site = SiteConfig::current_site_config();
    
        $fields = new FieldList(
            // Account details
            HiddenField::create('instId', null, $this->InstallID),
            HiddenField::create('cartId', null, $order->OrderNumber),
            HiddenField::create('MC_callback', null, Director::absoluteBaseURL() . Payment_Controller::$url_segment),
            
            // Amount and Currency details
            HiddenField::create('amount', null, $order->getOrderTotal()),
            HiddenField::create('currency', null, $site->Currency()->GatewayCode),
            
            // Payee details
            HiddenField::create('name', null, $order->BillingFirstnames . " " . $order->BillingSurname),
            HiddenField::create('address1', null, $order->BillingAddress1),
            HiddenField::create('address2', null, $order->BillingAddress2),
            HiddenField::create('town', null, $order->BillingCity),
            HiddenField::create('region', null, $order->BillingState),
            HiddenField::create('postcode', null, $order->BillingPostCode),
            HiddenField::create('country', null, $order->BillingCountry),
            HiddenField::create('email', null, $order->BillingEmail)
        );
        
        if($this->GatewayMessage)
            $fields->add(HiddenField::create('desc', null, $this->GatewayMessage));
        
        if(Director::isDev())
            $fields->add(HiddenField::create('testMode', null, '100'));
        
        return $fields;
    }
    
    public function onBeforeWrite() {
        parent::onBeforeWrite();     
    
        if(!$this->Summary)
            $this->Summary = "Pay with credit/debit card securely via WorldPay";
    }
}