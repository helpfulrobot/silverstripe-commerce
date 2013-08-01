<?php
/**
 * Summary Controller is responsible for displaying all order data before posting
 * to the final payment gateway.
 *
 * @author morven
 * @package commerce
 */

class Payment_Controller extends Page_Controller {
    public static $url_segment = "payment";

    static $allowed_actions = array(
        'index',
        'success',
        'failer',
        'error',
        'callback'
    );

    public function init() {
        parent::init();
    }

    public function index() {
        // If no shopping cart doesn't exist, redirect to base
        if(!ShoppingCart::get()->Items()->exists())
            return $this->redirect(Director::BaseURL());

        $vars = array(
            'ClassName' => "Payment",
            'Title'     => _t('Commerce.CHECKOUTSUMMARY',"Summary"),
            'MetaTitle' => _t('Commerce.CHECKOUTSUMMARY',"Summary"),
        );

        return $this->renderWith(array('Payment','Page'), $vars);
    }

    public function getOrder() {
        return Session::get('Order');
    }

    public function getPaymentMethod() {
        // Check if payment slug is set and that corresponds to a payment
        if($this->request->param('ID') && $method = CommercePaymentMethod::get()->filter('CallBackSlug',$this->request->param('ID'))->first())
            return $method;
        // Then check session
        elseif($method = CommercePaymentMethod::get()->byID(Session::get('PaymentMethod')))
            return $method;
        else
            return false;
    }

    // Get the existing gateway data from the relevent PaymentMethod object
    public function getGatewayData() {
        $payment_method = $this->getPaymentMethod();

        return $this->renderWith('GatewayData_' . $payment_method->ClassName, $payment_method->GatewayData());
    }

    public function GatewayForm() {
        $payment = $this->getPaymentMethod();

        $form = Form::create($this, $payment->Title . 'Form', $payment->getGatewayFields(), $payment->getGatewayActions());
        $form->addExtraClass('forms');
        $form->setFormMethod('POST');
        $form->setFormAction($payment->GatewayURL());

        return $form;
    }

    // Get relevent payment gateway URL to use in HTML form
    public function getGatewayURL() {
        return $this->getPaymentMethod()->GatewayURL();
    }

    /**
     * Method to clear any existing sessions related to commerce module
     */
    public function ClearSessionData() {
        if(isset($_SESSION)) {
            ShoppingCart::get()->clear();
            unset($_SESSION['Order']);
            unset($_SESSION['PostageID']);
            unset($_SESSION['PaymentMethod']);
        }
    }

    /**
     * This method takes any post data submitted via a payment provider and
     * sends it to the relevent gateway class for processing
     *
     */
    public function callback() {
        // See if data has been passed via the request
        if($this->request->postVars())
            $data = $this->request->postVars();
        elseif(count($this->request->getVars()) > 1)
            $data = $this->request->getVars();
        else
            $data = false;

        // If post data exists, process. Otherwise provide error
        if($data) {
            $callback = $this->getPaymentMethod()->ProcessCallback($data);

            if($callback)
                $return = $this->success();
            else
                $return = $this->error();
        } else
            $return = $this->error();

        // Clear our session data
        $this->ClearSessionData();

        // Render our return values
        return $this->renderWith(array('Payment_Response','Page'), $return);
    }

    /*
     * Method called when payement gateway returns the sucess URL
     *
     * @return array
     */
    public function success() {
        $site = SiteConfig::current_site_config();
        $order = Session::get('Order');

        if($order)
            $commerceOrderSuccess = true;
        else {
            $commerceOrderSuccess = false;
            $order = false;
        }

        return array(
            'CommerceOrderSuccess' => $commerceOrderSuccess,
            'Order' => $order,
            'Title' => _t('Commerce.ORDERCOMPLETE','Order Complete'),
            'Content' => ($site->SuccessCopy) ? nl2br(Convert::raw2xml($site->SuccessCopy), true) : false
        );
    }

    /*
     * Represents an error in the in all stages of the payment process
     *
     */
    public function error() {
        $site = SiteConfig::current_site_config();

        return array(
            'Title'     => _t('Commerce.ORDERFAILED','Order Failed'),
            'Content'   => ($site->FailerCopy) ? nl2br(Convert::raw2xml($site->FailerCopy), true) : false
        );
    }
}
