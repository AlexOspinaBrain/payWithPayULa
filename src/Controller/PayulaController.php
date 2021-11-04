<?php

namespace Drupal\pay_with_payula\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\commerce_price\Price;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\SupportsNotificationsInterface;

/**
 * Class Pay U LA controller.
 *
 * This class manages all about 'Pay U LA Responses'.
 */
class PayulaController extends ControllerBase implements SupportsNotificationsInterface {

  /**
   * Function return.
   */
  public function return(Request $request){

    $data['transactionState']=$request->query->get('transactionState');
    $data['polResponseCode']=$request->query->get('polResponseCode');

    $data['merchantId']=$request->query->get('merchantId');
    $data['referenceCode']=$request->query->get('referenceCode');
    $data['signature=']=$request->query->get('signature');

    $data['lapPaymentMethodType']=$request->query->get('lapPaymentMethodType');

    $data['merchant_name']=$request->query->get('merchant_name');
    $data['merchant_address']=$request->query->get('merchant_address');
    $data['telephone']=$request->query->get('telephone');
    $data['merchant_url']=$request->query->get('merchant_url');
    $data['description']=$request->query->get('description');

    $data['lapTransactionState']=$request->query->get('lapTransactionState');
    $data['lapResponseCode']=$request->query->get('lapResponseCode');
    $data['message']=$request->query->get('message');

    if ($data['transactionState'] == 4 ) {
        $data['message']  = "Transaction approved";
    }

    else if ($data['transactionState'] == 6 ) {
        $data['message']  = "Transaction rejected";
    }

    else if ($data['transactionState'] == 104 ) {
        $data['message'] = "Error";
    }

    else if ($data['transactionState'] == 7 ) {
        $data['message'] = "Pending payment";
    }

    return [
        '#type' => '#markup',
        '#markup' => render($this->htmlresponse($data))
      ];

  }

  private function htmlresponse(Array $data){
    $rows = [
        [ t('Pay method: ') . $data['merchant_name'] ],
        [ t('Entity: ') . $data['lapResponseCode'] ],
        [ t('Transaction result: ') . $data['lapTransactionState'] ],
        [ t('Message: ') . $data['message'] ],
      ];
    $header = [
        'title' => t('Purchase information'),
      ];
    $build['table'] = [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#empty' => t('No content has been found.'),
      ];
    return $build;

  }

  /**
   * Processes the notification request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\Response|null
   *   The response, or NULL to return an empty HTTP 200 response.
   */
  public function onNotify(Request $request){

    $notification = $request->query->all();

    $paymentPlugin =  \Drupal::config('commerce_payment.commerce_payment_gateway.payu_la');


    $payment_storage = $this->entityTypeManager()->getStorage('commerce_payment');
    $payment = $payment_storage->create([
      'type' => 'payment_default',
      'state' => 'completed',
      'payment_gateway' => $paymentPlugin->get('id'),
      'payment_gateway_mode' => $paymentPlugin->getOriginal()['configuration']['mode'],
      'test' => $paymentPlugin->getOriginal()['configuration']['mode'] == 'test',

      'amount' => new Price($notification['value'], $notification['currency']),
      'remote_id' => $notification['reference_pol'],
      'remote_state' => $notification['reference_code_pol'],
      'order_id' => $notification['reference_sale'],


    ]);
    $payment->save();

    return null;
  }

}
