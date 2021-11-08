<?php

namespace Drupal\pay_with_payula\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\commerce_price\Price;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\SupportsNotificationsInterface;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Render\Markup;

/**
 * Class Pay U LA controller.
 *
 * This class manages all about 'Pay U LA Responses'.
 */
class PayulaController extends ControllerBase implements SupportsNotificationsInterface {

  private $paymentPlugin;

  public function __construct()
  {
        /** The payment setting should have been like 'payu_la'  */
        $this->paymentPlugin =  \Drupal::config('commerce_payment.commerce_payment_gateway.payu_la');
  }
  /**
   * Function return.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   */
  public function return(Request $request){

    $data['transactionState']=$request->query->get('transactionState') ?? '';
    $data['polResponseCode']=$request->query->get('polResponseCode') ?? '';

    $data['merchantId']=$request->query->get('merchantId') ?? '';
    $data['referenceCode']=$request->query->get('referenceCode') ?? '';
    $data['signature=']=$request->query->get('signature') ?? '';

    $data['lapPaymentMethodType']=$request->query->get('lapPaymentMethodType') ?? '';

    $data['merchant_name']=$request->query->get('merchant_name') ?? '';
    $data['merchant_address']=$request->query->get('merchant_address') ?? '';
    $data['telephone']=$request->query->get('telephone') ?? '';
    $data['merchant_url']=$request->query->get('merchant_url') ?? '';
    $data['description']=$request->query->get('description') ?? '';

    $data['lapTransactionState']=$request->query->get('lapTransactionState') ?? '';
    $data['lapResponseCode']=$request->query->get('lapResponseCode') ?? '';
    $data['message']=$request->query->get('message') ?? '';

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

    $renderr = $this->htmlresponse($data);

    return [
        '#type' => '#markup',
        '#markup' => render($renderr),
      ];

  }

  private function htmlresponse(array $data){
    $rows = [
        [ Markup::create('<strong>'.t('Pay Method').'</strong>'), $data['merchant_name'] ],
        [  Markup::create('<strong>Entity</strong>') , $data['lapResponseCode'] ],
        [  Markup::create('<strong>Transaction result:</strong>'), $data['lapTransactionState'] ],
        [  Markup::create('<strong>Message:</strong>') , $data['message'] ],
      ];
    $header = [
        ['colspan'=>2,],
      ];
    /*$attributes = [
      'style' => 'widht = 1%'
    ];*/
    $build['table'] = [
        '#type' => 'table',
        '#header' => $header,
        //'#attributes' => $attributes,
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

    $validate = $this->validationSignature(
      $notification['merchant_id'],
      $notification['reference_sale'],
      $notification['value'],
      $notification['currency'],
      $notification['state_pol'],
      $notification['sign']
    );

    if ($validate && $notification['state_pol'] == '4' && $request->getScheme() == 'https'){
      $payment_storage = $this->entityTypeManager()->getStorage('commerce_payment');
      $payment = $payment_storage->create([
        'type' => 'payment_default',
        'state' => 'completed',
        'payment_gateway' => $this->paymentPlugin->get('id'),
        'payment_gateway_mode' => $this->paymentPlugin->getOriginal()['configuration']['mode'],
        'test' => $this->paymentPlugin->getOriginal()['configuration']['mode'] == 'test',

        'amount' => new Price($notification['value'], $notification['currency']),
        'remote_id' => $notification['reference_pol'],
        'remote_state' => $notification['reference_code_pol'],
        'order_id' => $notification['reference_sale'],

      ]);
      $payment->save();

      return new Response();
    } else {
      return new Response('', Response::HTTP_NON_AUTHORITATIVE_INFORMATION);
    }
  }

  /**
   * Validation Signature
   *
   * @var string $merchant_id
   * @var string $reference_sale
   * @var string $value
   * @var string $currency
   * @var int $state_pol
   * @var string $sign
   *
   * @return bool
   */
  private function validationSignature(
      string $merchant_id, string $reference_sale,
      string $value, string $currency, int $state_pol, string $sign
  ) : bool {

    $valArray = explode('.', $value);
    $decPart='';
    if (isset($valArray[1])) {
      $decPart = substr($valArray[1],1,1) != '0' ? substr($valArray[1],0,2) : substr($valArray[1],0,1) ;
    }
    $valNew = $decPart != '' ? $valArray[0] . '.' . $decPart : $valArray[0];

    $signVerification = md5(
      $this->paymentPlugin->getOriginal()['configuration']['api_key'] . '~' .
      $merchant_id . '~' .
      $reference_sale . '~' .
      $valNew . '~' .
      $currency . '~' .
      $state_pol
    );

    return $signVerification === $sign;
  }

}
