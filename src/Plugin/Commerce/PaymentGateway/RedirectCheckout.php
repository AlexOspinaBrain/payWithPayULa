<?php

namespace Drupal\pay_with_payula\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the QuickPay offsite Checkout payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "redirect_checkout",
 *   label = @Translation("PayU LA (Redirect)"),
 *   display_label = @Translation("PayU LA"),
 *    forms = {
 *     "offsite-payment" = "Drupal\pay_with_payula\PluginForm\RedirectCheckoutForm",
 *   },
 *   payment_method_types = {"credit_card"},
 *   credit_card_types = {
 *     "mastercard", "visa",
 *   },
 * )
 */
class RedirectCheckout extends OffsitePaymentGatewayBase  {

  public function defaultConfiguration() {
    return [
        'merchantId' => '',
        'api_key' => '',
        'accountId' => '',
        'urlProvider' => '',
        ] + parent::defaultConfiguration();
  }

  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['merchantId'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Merchant Id'),
      '#description' => $this->t('This is the Merchant Id from the PayU LA.'),
      '#default_value' => $this->configuration['merchantId'],
      '#required' => TRUE,
    ];

    $form['apiKey'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API key'),
      '#description' => $this->t('The API key for the same user as used in PayU LA.'),
      '#default_value' => $this->configuration['apiKey'],
      '#required' => TRUE,
    ];

    $form['apiLogin'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Login'),
      '#description' => $this->t('The API Login for the same user as used in PayU LA.'),
      '#default_value' => $this->configuration['apiLogin'],
      '#required' => TRUE,
    ];

    $form['accountId'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Account Id'),
        '#description' => $this->t('The Account Id for the same user as used in PayU LA.'),
        '#default_value' => $this->configuration['accountId'],
        '#required' => TRUE,
      ];

      $form['urlProvider'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Url Provider'),
        '#description' => $this->t('URL PayU LA, it depends if it is test or live, also include the schema in the URL: (http or https)'),
        '#default_value' => $this->configuration['urlProvider'],
        '#required' => TRUE,
      ];

      $form['urlReport'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Url Report'),
        '#description' => $this->t('Queries API URL, it is provided by PayU LA'),
        '#default_value' => $this->configuration['urlReport'],
        '#required' => TRUE,
      ];
    return $form;
  }

  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $values = $form_state->getValue($form['#parents']);
    $this->configuration['merchantId'] = $values['merchantId'];
    $this->configuration['apiKey'] = $values['apiKey'];
    $this->configuration['apiLogin'] = $values['apiLogin'];
    $this->configuration['accountId'] = $values['accountId'];
    $this->configuration['urlProvider'] = $values['urlProvider'];
    $this->configuration['urlReport'] = $values['urlReport'];
  }
}