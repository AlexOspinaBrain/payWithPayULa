<?php

namespace Drupal\pay_with_payula\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Render\Markup;


/**
 * Class Pay U LA controller.
 *
 * This class manages all about 'Pay U  Responses'.
 */
class PayulaController extends ControllerBase  {

  /**
   * Function return.
   */
  public function return(Request $request){
    //$foo = $request->query->get('title');

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

}
