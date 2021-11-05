<?php

namespace Drupal\pay_with_payula\PluginForm;

use Drupal\commerce_payment\PluginForm\PaymentOffsiteForm as BasePaymentOffsiteForm;
use Drupal\Core\Form\FormStateInterface;

class RedirectCheckoutForm  extends BasePaymentOffsiteForm  {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildConfigurationForm($form, $form_state);

    /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
    $payment = $this->entity;
    /** @var \Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayInterface $payment_gateway_plugin */
    $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();
    $configuration = $payment_gateway_plugin->getConfiguration();

    $data['merchantId'] = $configuration['merchantId'];
    $data['accountId'] = $configuration['accountId'];

    $data['referenceCode'] = $payment->getOrderId();
    $data['description'] = "Venta Cuadernos";

    $data['currency'] = $payment->getAmount()->getCurrencyCode();
    $data['amount'] = $payment->getAmount()->getNumber();
    $data['tax'] = "0";
    $data['taxReturnBase'] = "0";

    //$data['signature'] = "7ee7cf808ce6a39b17481c54f2c57acc";

    $order = $payment->getOrder();
    $billing_address = $order->getBillingProfile()->get('address')->first();
    $data['buyerFullName'] = $billing_address->getGivenName() . ' ' . $billing_address->getFamilyName();
    $data['shippingAddress'] = $billing_address->getAddressLine1() . ' ' . $billing_address->getAddressLine2();
    $data['shippingCity'] = $billing_address->getLocality();
    $data['shippingCountry'] = $billing_address->getCountryCode();

    $data['buyerEmail'] = $payment->getOrder()->getEmail();

    $data['test'] = "1";

    $data['responseUrl'] = "http://drupal.lc/respuestaPayuLA";
    $data['confirmationUrl'] = "http://drupal.lc/confirmPayuLA";

    return $this->buildRedirectForm(
        $form,
        $form_state,
        'https://sandbox.checkout.payulatam.com/ppp-web-gateway-payu/',
        $data,
        self::REDIRECT_POST
    );
  }

}