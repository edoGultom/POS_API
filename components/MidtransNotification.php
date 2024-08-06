<?php

namespace app\components;

use Midtrans\Notification;
use Midtrans\Transaction;
use Yii;
use yii\base\Component;

class MidtransNotification extends Component
{
    public $isProduction;
    public $merchantId;
    public $clientKey;
    public $serverKey;

    private function generateCustomerDetails()
    {
        // $userAccount = Yii::$app->user->identity->userAccount;
        // $nameParts = explode(' ', $userAccount->pic_full_name);
        // $firstName = $nameParts[0] ?? '';
        // $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : "";

        // return [
        //     'first_name' => $firstName,
        //     'last_name' => $lastName,
        //     'email' => $userAccount->email,
        //     'phone' => $userAccount->phone_number,
        // ];
        return [
            'first_name' => 'edo',
            'last_name' => 'gultom',
            'email' => 'edogultom10395@gmail.com',
            'phone' => '082285811523',
        ];
    }

    private function generateItemDetails($model)
    {
        $itemDetails = [];
        foreach ($model as $val) {
            $itemDetails[] = [
                'id' => $val->id,
                'price' => $val->harga,
                'quantity' => $val->quantity,
                'name' => $val->menu['nama']
            ];
        }

        return $itemDetails;
    }

    public function checkout($orderID, $totalBayar, $tblPembayaran)
    {
        $itemDetails = $this->generateItemDetails($tblPembayaran);
        $customerDetails = $this->generateCustomerDetails();
        \Midtrans\Config::$isProduction = $this->isProduction;
        \Midtrans\Config::$serverKey = $this->serverKey;

        $params = array(
            'transaction_details' => array(
                'order_id' => $orderID,
                'gross_amount' => $totalBayar,
            ),
            'payment_type' => 'qris',
            'item_details' => $itemDetails,
            'customer_details' => $customerDetails,
            // 'qris' => array(
            //     'acquirer' => 'ovo'
            // ),
            'enabled_payments' => array('other_qris')
        );
        // echo "<pre>";
        // print_r($params);
        // echo "</pre>";
        // exit();
        $response = '';
        try {
            $response = \Midtrans\CoreApi::charge($params);
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            exit;
        }

        // var_dump($response);
        // exit;
        return $response;
    }

    public function cancel($orderId)
    {

        \Midtrans\Config::$isProduction = $this->isProduction;
        \Midtrans\Config::$serverKey = $this->serverKey;

        return Transaction::cancel($orderId);
    }

    public function expire($orderId)
    {

        \Midtrans\Config::$isProduction = $this->isProduction;
        \Midtrans\Config::$serverKey = $this->serverKey;

        return Transaction::expire($orderId);
    }

    public function notificationHandler()
    {
        \Midtrans\Config::$isProduction = $this->isProduction;
        \Midtrans\Config::$serverKey = $this->serverKey;

        try {
            return new Notification();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function notificationHandlerTest()
    {
        \Midtrans\Config::$isProduction = $this->isProduction;
        \Midtrans\Config::$serverKey = $this->serverKey;

        try {
            return json_decode(file_get_contents('php://input'));
        } catch (\Exception $e) {
            return false;
        }
    }
}
