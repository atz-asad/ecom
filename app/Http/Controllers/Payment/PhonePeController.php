<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Session;
use App\Http\Controllers\User\UserCheckoutController;
use App\Http\Controllers\Front\CheckoutController;
use App\Http\Helpers\Common;
use App\Http\Helpers\MegaMailer;
use App\Http\Helpers\UserPermissionHelper;
use App\Models\Package;
use App\Models\Language;
use Carbon\Carbon;
use Ixudra\Curl\Facades\Curl;

class PhonePeController extends Controller
{
    public function paymentProcess(Request $request, $_amount, $_success_url, $_cancel_url)
    {
        Session::put('request', $request->all());
        $paymentMethod = PaymentGateway::where('keyword', 'phonepe')->first();
        $paydata = json_decode($paymentMethod->information, true);
        $notify_url = $_success_url;
        $random_id = rand(111, 999);

        $data = array(
            'merchantId' => $paydata['merchant_id'], // sandbox merchant id
            'merchantTransactionId' => uniqid(),
            'merchantUserId' => 'MUID' . $random_id, // it will be the ID of tenants / vendors from database
            'amount' => intval($_amount * 100),
            'redirectUrl' => $notify_url,
            'redirectMode' => 'POST',
            'callbackUrl' => $notify_url,
            'mobileNumber' => $request->phone,
            'paymentInstrument' =>
            array(
                'type' => 'PAY_PAGE',
            ),
        );

        $encode = base64_encode(json_encode($data));
        $saltKey = $paydata['salt_key'];
        $saltIndex = $paydata['salt_index'];

        $string = $encode . '/pg/v1/pay' . $saltKey;
        $sha256 = hash('sha256', $string);

        $finalXHeader = $sha256 . '###' . $saltIndex;

        if ($paydata['sandbox_status'] == 1) {
            $url = "https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/pay";
        } else {
            $url = "https://api.phonepe.com/apis/pg/checkout/v2/pay";
        }

        $response = Curl::to($url)
            ->withHeader('Content-Type:application/json')
            ->withHeader('X-VERIFY:' . $finalXHeader)
            ->withData(json_encode(['request' => $encode]))
            ->post();

        $rData = json_decode($response);
        if (empty(@$rData->data->instrumentResponse->redirectInfo->url)) {
            return redirect($_cancel_url);
        }
        Session::put('cancel_url', $_cancel_url);
        return redirect()->to($rData->data->instrumentResponse->redirectInfo->url);
    }


    public function successPayment(Request $request)
    {
        $requestData = Session::get('request');
        $currentLang = session()->has('lang') ?
            (Language::where('code', session()->get('lang'))->first())
            : (Language::where('is_default', 1)->first());
        $be = $currentLang->basic_extended;
        $bs = $currentLang->basic_setting;
        /** clear the session payment ID **/
        $cancel_url = route('membership.cancel');

        if ($request->code == 'PAYMENT_SUCCESS') {
            $paymentFor = Session::get('paymentFor');
            $package = Package::find($requestData['package_id']);
            $transaction_id = UserPermissionHelper::uniqidReal(8);
            $transaction_details = null;
            if ($paymentFor == "membership") {
                $amount = $requestData['price'];
                $password = $requestData['password'];
                $checkout = new CheckoutController();
                $requestData['status'] = 1;
                $user = $checkout->store($requestData, $transaction_id, $transaction_details, $amount, $be, $password);

                $lastMemb = $user->memberships()->orderBy('id', 'DESC')->first();
                $activation = Carbon::parse($lastMemb->start_date);
                $expire = Carbon::parse($lastMemb->expire_date);
                $file_name = Common::makeInvoice($requestData, "membership", $user, $password, $amount, "Yoco", $requestData['phone'], $be->base_currency_symbol_position, $be->base_currency_symbol, $be->base_currency_text, $transaction_id, $package->title, 1);

                $mailer = new MegaMailer();
                $data = [
                    'toMail' => $user->email,
                    'toName' => $user->fname,
                    'username' => $user->username,
                    'package_title' => $package->title,
                    'package_price' => ($be->base_currency_text_position == 'left' ? $be->base_currency_text . ' ' : '') . $package->price . ($be->base_currency_text_position == 'right' ? ' ' . $be->base_currency_text : ''),
                    'activation_date' => $activation->toFormattedDateString(),
                    'expire_date' => Carbon::parse($expire->toFormattedDateString())->format('Y') == '9999' ? 'Lifetime' : $expire->toFormattedDateString(),
                    'membership_invoice' => $file_name,
                    'website_title' => $bs->website_title,
                    'templateType' => 'registration_with_premium_package',
                    'type' => 'registrationWithPremiumPackage'
                ];
                $mailer->mailFromAdmin($data);

                session()->flash('success', __('successful_payment'));
                Session::forget('request');
                Session::forget('paymentFor');
                return redirect()->route('success.page');
            } elseif ($paymentFor == "extend") {
                $amount = $requestData['price'];
                $password = uniqid('qrcode');
                $checkout = new UserCheckoutController();
                $user = $checkout->store($requestData, $transaction_id, $transaction_details, $amount, $be, $password);

                $lastMemb = $user->memberships()->orderBy('id', 'DESC')->first();
                $activation = Carbon::parse($lastMemb->start_date);
                $expire = Carbon::parse($lastMemb->expire_date);
                $file_name = Common::makeInvoice($requestData, "extend", $user, $password, $amount, $requestData["payment_method"], $user->phone_number, $be->base_currency_symbol_position, $be->base_currency_symbol, $be->base_currency_text, $transaction_id, $package->title, 1);

                $mailer = new MegaMailer();
                $data = [
                    'toMail' => $user->email,
                    'toName' => $user->fname,
                    'username' => $user->username,
                    'package_title' => $package->title,
                    'package_price' => ($be->base_currency_text_position == 'left' ? $be->base_currency_text . ' ' : '') . $package->price . ($be->base_currency_text_position == 'right' ? ' ' . $be->base_currency_text : ''),
                    'activation_date' => $activation->toFormattedDateString(),
                    'expire_date' => Carbon::parse($expire->toFormattedDateString())->format('Y') == '9999' ? 'Lifetime' : $expire->toFormattedDateString(),
                    'membership_invoice' => $file_name,
                    'website_title' => $bs->website_title,
                    'templateType' => 'membership_extend',
                    'type' => 'membershipExtend'
                ];
                $mailer->mailFromAdmin($data);


                session()->flash('success', __('successful_payment'));
                Session::forget('request');
                Session::forget('paymentFor');
                return redirect()->route('success.page');
            }
        }
        return redirect($cancel_url);
    }
}
