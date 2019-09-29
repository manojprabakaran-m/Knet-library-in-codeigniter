<?php
/*
         Manuigniter
        WWW.menu.house
*/
class Knet_new extends CI_Controller{
 function __construct()
 {
       parent::__construct();
      $this->load->model('Branches_model');
 }
 /*

*/
/* do payment */
  public function do_knet_payment($cart_order_id)
{
	$this->load->model('cart_order_model');
	$this->load->model('Cart_item_model');
	$this->load->library('encryption');
	$this->load->model('Cust_address_model');
	$this->load->model('Product_model');
	$this->load->model('Product_choices_model');

	//echo $cart_order_id;
	  $cart_order_id = strtr($cart_order_id, array('.' => '+', '-' => '=', '~' => '/'));
	  $cart_order_id=$this->encryption->decrypt($cart_order_id);
	  $cart_item= $this->Cart_item_model->get_all_cart_itembycartid($cart_order_id);
  	  $cart_order = $this->cart_order_model->get_cart_order($cart_order_id);
	  $order_items=array();
	  $pro_data=array();
	   $cust_address = $this->Cust_address_model->get_cust_address($cart_order['address_id']);
		foreach($cart_item as $ci)
		{
			$product= $this->Product_model->get_product($ci['product_id']);
		//	$pro_data[]=$this->Product_model->get_productforpg($ci['product_id']);
			if($product!=null)
			{
			}
			$re=array();
			$a=array();
			$product_name=$product['product_name'];
			if($ci['product_or_pro_choice']=="pro_choice" && $ci['p_choice_id']!=null)
			{
				  $product_choices = $this->Product_choices_model->get_product_choices($ci['p_choice_id']);
				  if($product_choices!=null)
				  {
						$product_name=$product_name."(".$product_choices['choiceName']."-".$product_choices['p_color'].")";
				  }

			}

            $a['product_name']=$product_name;
            $a['unitPrice']=$ci['cart_price']/$ci['quantity'];
			$a['qty']=$ci['quantity'];
			$pro_data[]=$a;
			/* $order_items['image']=$product['image'];
			$order_items['name']=$product['product_name'];
			$order_items['qty']=$ci['qty'];
			$order_items['sub_total']=$ci['cart_price'];*/
			$ci['product']=$product;
			$order_items[] =$ci;
		}
		 $a=array();
            $a['product_name']="Delivery Charge";
            $a['unitPrice']=$cart_order['delivery_charge'];
			$a['qty']=1;
			 $pro_data[]=$a;
			 	 $a=array();
            $a['product_name']="Service Charge";
            $a['unitPrice']=$cart_order['service_charge'];
			$a['qty']=1;
		 	$pro_data[]=$a;
		$data['cart_order']['cart_items']=$order_items;
      //https://www.formget.com/curl-library-codeigniter/
        //echo $url;
       $customer_arr=array(
				'customer_name'=>$cart_order['fullname'],
				'email'=>$cart_order['email'],
				'mobile'=>$cart_order['phone'],
				'gender'=>'',
				'dob'=>'',
				'civilid_no'=>'',
				'city'=>$cust_address['city_id'],
				'block'=>$cust_address['block'],
				'street'=>$cust_address['street'],
				'avenue'=>$cust_address['jadda'],
				'building'=>$cust_address['houe_no'],
				'floor'=>$cust_address['floor'],
				'apartment'=>$cust_address['apartment'],
			);
			$product_arr=$pro_data;
			$customer_data=json_encode($customer_arr);
			$product_data=json_encode($product_arr);
			$return_url=site_url('pay-success/'.$cart_order_id);
			$error_url=site_url('pay-error/'.$cart_order_id);
			 $total_product=$cart_order['sum_total'];
			$merchantData = array(
					'customer'=>$customer_data,
					'merchant_code'=>25,
					'merchant_username'=>PAY_USERNAME_TEST,
					'password'=>PAY_PASSWORD_TEST,
					'reference_id'=>'123456',
					'return_url'=>$return_url,
					'error_url'=>$error_url,
					'product_data'=>$product_data,
					'subtotal'=>$total_product,
					'paymentMode'=>'knet',
					'currency_code' => 'KWD',
				);

        try {
		 $TranAmount = $total_product;

		 if(KNET_LIVE_TEST==1)
		 {
			$TranportalId=KNET_TRANSPORTAL_ID;
			$ReqTranportalId="id=".$TranportalId;

			$TranportalPassword=KNET_TRANSPORTAL_PWD;
			$ReqTranportalPassword="password=".$TranportalPassword;

			$ReqAmount="amt=".$TranAmount;

			//$TranTrackid=mt_rand();
			$TranTrackid=$cart_order['trackid'];
			$ReqTrackId="trackid=".$TranTrackid;

			$ReqCurrency="currencycode=".KNET_CURRENCY;
			$ReqLangid="langid=".KNET_REQ_LANG;
			$termResourceKey=KNET_LIVE_RESOURCE_KEY;
		 }
		 else
		 {
			$TranportalId=KNET_TEST_TRANSPORTAL_ID;
			$ReqTranportalId="id=".$TranportalId;

			$TranportalPassword=KNET_TEST_TRANSPORTAL_PWD;
			$ReqTranportalPassword="password=".$TranportalPassword;

			$ReqAmount="amt=".$TranAmount;

			//$TranTrackid=mt_rand();
			$TranTrackid=$cart_order['trackid'];
			$ReqTrackId="trackid=".$TranTrackid;

			$ReqCurrency="currencycode=".KNET_TEST_CURRENCY;
			$ReqLangid="langid=".KNET_TEST_REQ_LANG;
			$termResourceKey=KNET_TEST_RESOURCE_KEY;
		 }

		/* Action Code of the transaction, this refers to type of transaction.
			Action Code 1 stands of Purchase transaction  */
		$ReqAction="action=1";

		//$ResponseUrl="https://www.yourwebsite.com/PHP/GetHandlerResponse.php";
		$ResponseUrl=site_url('knet-success/'.$cart_order_id);
		$ReqResponseUrl="responseURL=".$ResponseUrl;

		//$ErrorUrl="https://www.yourwebsite.com/PHP/result2.php";
		$ErrorUrl=site_url('knet-error/'.$cart_order_id);
		$ReqErrorUrl="errorURL=".$ErrorUrl;


		$ReqUdf1="";
		$ReqUdf2="";
		$ReqUdf3="";
		$ReqUdf4="";
		$ReqUdf5="";

		$param=$ReqTranportalId."&".$ReqTranportalPassword."&".$ReqAction."&".$ReqLangid."&".$ReqCurrency."&".$ReqAmount."&".$ReqResponseUrl."&".$ReqErrorUrl."&".$ReqTrackId."&".$ReqUdf1."&".$ReqUdf2."&".$ReqUdf3."&".$ReqUdf4."&".$ReqUdf5;


		//echo "<pre>".var_export($param,true)."</pre>";
		$param=$this->encryptAES($param,$termResourceKey)."&tranportalId=".$TranportalId."&responseURL=".$ResponseUrl."&errorURL=".$ErrorUrl;

		//echo "<pre>".var_export($param,true)."</pre>";
		//==============================Encryption LOGIC CODE End=================================================================================

		/* Log the complete request in the log file for future reference
		Now creating a connection and sending request
		Note - In PHP header function is used for redirecting request
		*********UNCOMMENT THE BELOW REDIRECTION CODE TO CONNECT TO EITHER TEST OR PRODUCTION********* */
		if(KNET_LIVE_TEST==1)
		{
			header("Location: ".KNET_URL."?param=paymentInit"."&trandata=".$param); /* send request and redirect to PRODUCTION */
 		}
		else
		{
				header("Location: ".KNET_TEST_URL."?param=paymentInit"."&trandata=".$param); /* send request and redirect to TEST */
		}
      //	header("Location: ".KNET_TEST_URL."?param=paymentInit"."&trandata=".$param); /* send request and redirect to TEST */
		//header("Location: ".KNET_URL."?param=paymentInit"."&trandata=".$param); /* send request and redirect to PRODUCTION */

		 //
         // var_dump($result);
          }
          catch(Exception $e) {
            trigger_error(sprintf(
           'Curl failed with error #%d: %s',
           $e->getCode(), $e->getMessage()),
             E_USER_ERROR);
          }
}



/* sucees url*/
public function knet_success($cart_order_id=null,$data2=null)
{
		if(isset($_REQUEST['ErrorText']) || isset($_REQUEST['Error']))
		{
			$ResErrorText= $_REQUEST['ErrorText']; 	  	//Error Text/message
			$ResErrorNo = $_REQUEST['Error'];           //Error Number
			$ResTranData = null;
		} else {
			$ResErrorText= null;
			$ResErrorNo = null;
			$ResTranData= $_REQUEST['trandata'];
		}
		$ResPaymentId = $_REQUEST['paymentid'];		//Payment Id
		$ResTrackID = $_REQUEST['trackid'];       	//Merchant Track ID
		$ResResult =  $_REQUEST['result'];          //Transaction Result
		$ResPosdate = $_REQUEST['postdate'];        //Postdate
		$ResTranId = $_REQUEST['tranid'];           //Transaction ID
		$ResAuth = $_REQUEST['auth'];               //Auth Code
		$ResRef = $_REQUEST['ref'];                 //Reference Number also called Seq Number
		$ResAmount = $_REQUEST['amt'];              //Transaction Amount
		$Resudf1 = $_REQUEST['udf1'];               //UDF1
		$Resudf2 = $_REQUEST['udf2'];               //UDF2
		$Resudf3 = $_REQUEST['udf3'];               //UDF3
		$Resudf4 = $_REQUEST['udf4'];               //UDF4
		$Resudf5 = $_REQUEST['udf5'];               //UDF5

		 if(KNET_LIVE_TEST==1)
		 {
			$termResourceKey=KNET_LIVE_RESOURCE_KEY;
 		 }
		 else
		 {
			$termResourceKey=KNET_TEST_RESOURCE_KEY;
 		 }



		if($ResErrorText==null && $ResErrorNo==null && $ResTranData !=null)
		{

			//Decryption logice starts
			$decrytedData=$this->decrypt($ResTranData,$termResourceKey);

			/* IMPORTANT NOTE - MERCHANT SHOULD UPDATE TRANACTION PAYMENT STATUS IN HIS DATABASE AT THIS POINT
			AND THEN REDIRECT CUSTOMER TO THE RESULT PAGE. */
			redirect('knet-result/'.$cart_order_id.'?'.$decrytedData);
			//header("Location: https://www.yourwebsite.com/PHP/result.php?".$decrytedData);
			//exit();
		}
		else{
			redirect('knet-error/'.$cart_order_id.'?Error='.$ResErrorNo."&ErrorText=".$ResErrorText."&trackid=".$ResTrackID."&amt=".$ResAmount."&paymentid=".$ResPaymentId."&tranid=".$ResTranId."&result=".$ResResult);
			//header("Location: https://www.yourwesbite.com/PHP/result.php?Error=".$ResErrorNo."&ErrorText=".$ResErrorText."&trackid=".$ResTrackID."&amt=".$ResAmount."&paymentid=".$ResPaymentId."&tranid=".$ResTranId."&result=".$ResResult);
			//exit();
		}

}
/* result url*/
public function knet_result($cart_order_id=null)
{


	$result = isset($_GET['result']) ? $_GET['result'] : '';
	$trackid = isset($_GET['trackid']) ? $_GET['trackid'] : '';
	$PaymentID = isset($_GET['paymentid']) ? $_GET['paymentid'] : '';
	$ref = isset($_GET['ref']) ? $_GET['ref'] : '';
	$tranid = isset($_GET['tranid']) ? $_GET['tranid'] : '';
	$amount = isset($_GET['amt']) ? $_GET['amt'] : '';
	$trx_error = isset($_GET['Error']) ? $_GET['Error'] : '';
	$trx_errortext = isset($_GET['ErrorText']) ? $_GET['ErrorText'] : '';
	$postdate = isset($_GET['postdate']) ? $_GET['postdate'] : '';
	$auth = isset($_GET['auth']) ? $_GET['auth'] : '';
	$udf1 = isset($_GET['udf1']) ? $_GET['udf1'] : '';
	$udf2 = isset($_GET['udf2']) ? $_GET['udf2'] : '';
	$udf3 = isset($_GET['udf3']) ? $_GET['udf3'] : '';
	$udf4 = isset($_GET['udf4']) ? $_GET['udf4'] : '';
	$udf5 = isset($_GET['udf5']) ? $_GET['udf5'] : '';


		$this->load->model('cart_order_model');
		 $params = array(
           'payment_id'=> $PaymentID ,
           'result'=> $result,
		   'payment_date'=>DATE_TIME,
		    'post_date'=>$postdate,
		   'res_tranid'=> $tranid ,
           'ref'=> $ref,
		   'trx_error'=>$trx_error,
		   'trx_errortext'=> $trx_errortext ,
           'auth'=> $auth,

		   'paid_amount'=>$amount,
        		);
		  $this->cart_order_model->update_cart_order($cart_order_id,$params);
			 $this->load->library('encryption');

			 $this->change_cart_product_qty_status($$cart_order_id,'paid');//change paid status in cart_product qty table

		     $cart_order_id=$this->encryption->encrypt($cart_order_id);
            $cart_order_id = strtr($cart_order_id, array('+' => '.', '=' => '-', '/' => '~'));


		  redirect('order-page/'.$cart_order_id);


}
/*change_cart_product_qty_status*/
public function change_cart_product_qty_status($cart_order_id=null, $status=null)
{

		$this->load->model('cart_order_model');
		$this->load->model('Cart_item_model');
		$this->load->model('Cust_address_model');
   		$this->load->model('Product_model');
     	$this->load->model('Category_model');
     	$this->load->model('Cart_item_time_model');
	    $this->load->model('Cart_product_qty_model');
		  $cart_item= $this->Cart_item_model->get_all_cart_itembycartid($cart_order_id);
		   $data['cart_order'] = $this->cart_order_model->get_cart_order($cart_order_id);
		 if($cart_item!=null)
  {
    $data['cust_address'] = $this->Cust_address_model->get_cust_address($data['cart_order']['address_id']);
    $this->load->model('Product_model');
   // $order_data=array();
   // $order_data['netamount']=$netamount;
   //$order_data['service_charge']=$netamount;

    $order_items=array();
    foreach($cart_item as $ci)
    {
        $product= $this->Product_model->get_product($ci['product_id']);
        if($data['cart_order']['result']=="CAPTURED")
        {
			if($product!=null)
			{
				$params_up = array(
					'qty'=> $product['qty']-$ci['quantity'],
					'cart_qty'=> $product['cart_qty']-$ci['quantity'],
					);
				// var_dimp( $params_up);
				$this->Product_model->update_product($product['product_id'],$params_up);

				$sess_guest=$this->session->userdata(SESSION_NAME_GUEST);
					$cust_uniqe_id_arr= $this->Cart_product_qty_model->get_cart_product_qtybyclm_nameByp_id('cust_uniqe_id',$sess_guest['cust_uniqe_id'],$product['product_id']);
				$params_p_q_u = array(

							'updated_date'=>DATE_TIME,
							'status'=>'paid'
							);
				$this->Cart_product_qty_model->update_cart_product_qty($cust_uniqe_id_arr['cart_product_qty_id'],$params_p_q_u);
			}
			else
			{

			}
        }
        else
        {
          //echo "else".$data['cart_order']['result'];
        }

		/* $order_items['image']=$product['image'];
			$order_items['name']=$product['product_name'];
			$order_items['qty']=$ci['qty'];
			$order_items['sub_total']=$ci['cart_price'];*/
			$ci['product']=$product;
			$order_items[] =$ci;
		}
		$data['cart_order']['cart_items']=$order_items;
    }
      else
    {
     // echo "else";
    }

}
/* error url*/
public function knet_errors($cart_order_id=null)
{
		$result = isset($_GET['result']) ? $_GET['result'] : '';
	$trackid = isset($_GET['trackid']) ? $_GET['trackid'] : '';
	$PaymentID = isset($_GET['paymentid']) ? $_GET['paymentid'] : '';
	$ref = isset($_GET['ref']) ? $_GET['ref'] : '';
	$tranid = isset($_GET['tranid']) ? $_GET['tranid'] : '';
	$amount = isset($_GET['amt']) ? $_GET['amt'] : '';
	$trx_error = isset($_GET['Error']) ? $_GET['Error'] : '';
	$trx_errortext = isset($_GET['ErrorText']) ? $_GET['ErrorText'] : '';
	$postdate = isset($_GET['postdate']) ? $_GET['postdate'] : '';
	$auth = isset($_GET['auth']) ? $_GET['auth'] : '';
	$udf1 = isset($_GET['udf1']) ? $_GET['udf1'] : '';
	$udf2 = isset($_GET['udf2']) ? $_GET['udf2'] : '';
	$udf3 = isset($_GET['udf3']) ? $_GET['udf3'] : '';
	$udf4 = isset($_GET['udf4']) ? $_GET['udf4'] : '';
	$udf5 = isset($_GET['udf5']) ? $_GET['udf5'] : '';
	//echo $trx_error;
		$this->load->model('cart_order_model');
		 $params = array(
           'payment_id'=> $PaymentID ,
           'result'=> $result,
		   'payment_date'=>DATE_TIME,
		    'post_date'=>$postdate,
		   'res_tranid'=> $tranid ,
           'ref'=> $ref,
		   'trx_error'=>$trx_error,
		   'trx_errortext'=> $trx_errortext ,
           'auth'=> $auth,

		   'paid_amount'=>$amount,
        		);
		  $this->cart_order_model->update_cart_order($cart_order_id,$params);
			 $this->load->library('encryption');
		     $cart_order_id=$this->encryption->encrypt($cart_order_id);
            $cart_order_id = strtr($cart_order_id, array('+' => '.', '=' => '-', '/' => '~'));

		  redirect('order-page/'.$cart_order_id);
}



/* */

//AES Encryption Method Starts
function encryptAES($str,$key) {
$str = $this->pkcs5_pad($str);
$encrypted = openssl_encrypt($str, 'AES-128-CBC', $key, OPENSSL_ZERO_PADDING, $key);
$encrypted = base64_decode($encrypted);
$encrypted=unpack('C*', ($encrypted));
$encrypted=$this->byteArray2Hex($encrypted);
$encrypted = urlencode($encrypted);
return $encrypted;
}

function pkcs5_pad ($text) {
$blocksize = 16;
$pad = $blocksize - (strlen($text) % $blocksize);
return $text . str_repeat(chr($pad), $pad);
	}
/* */
function byteArray2Hex($byteArray) {
$chars = array_map("chr", $byteArray);
$bin = join($chars);
return bin2hex($bin);
}


function decrypt($code,$key) {
$code =  $this->hex2ByteArray(trim($code));
$code=$this->byteArray2String($code);
$iv = $key;
$code = base64_encode($code);
$decrypted = openssl_decrypt($code, 'AES-128-CBC', $key, OPENSSL_ZERO_PADDING, $iv);
return $this->pkcs5_unpad($decrypted);
}

function hex2ByteArray($hexString) {
$string = hex2bin($hexString);
return unpack('C*', $string);
}


function byteArray2String($byteArray) {
$chars = array_map("chr", $byteArray);
return join($chars);
}


function pkcs5_unpad($text) {
$pad = ord($text{strlen($text)-1});
if ($pad > strlen($text)) {
return false;
}
if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
return false;
}
return substr($text, 0, -1 * $pad);
}
/* live payment*/

 /*

*/
/* do old knet payment */
  public function knet_payment($cart_order_id)
{
	$this->load->model('cart_order_model');
	 $this->load->model('Cart_item_model');
	 $this->load->library('encryption');
	  $this->load->model('Cust_address_model');
	$this->load->model('Product_model');
	//echo $cart_order_id;
	  $cart_order_id = strtr($cart_order_id, array('.' => '+', '-' => '=', '~' => '/'));
	  $cart_order_id=$this->encryption->decrypt($cart_order_id);
	  $cart_item= $this->Cart_item_model->get_all_cart_itembycartid($cart_order_id);
  	  $cart_order = $this->cart_order_model->get_cart_order($cart_order_id);
	  $order_items=array();
	  $pro_data=array();
	   $cust_address = $this->Cust_address_model->get_cust_address($cart_order['address_id']);
		foreach($cart_item as $ci)
		{
			$product= $this->Product_model->get_product($ci['product_id']);
		//	$pro_data[]=$this->Product_model->get_productforpg($ci['product_id']);
			if($product!=null)
			{
			}
			$re=array();
            $a=array();
            $a['product_name']=$product['product_name'];
            $a['unitPrice']=$ci['cart_price']/$ci['quantity'];
			$a['qty']=$ci['quantity'];
			$pro_data[]=$a;
			/* $order_items['image']=$product['image'];
			$order_items['name']=$product['product_name'];
			$order_items['qty']=$ci['qty'];
			$order_items['sub_total']=$ci['cart_price'];*/
			$ci['product']=$product;
			$order_items[] =$ci;
		}
		$data['cart_order']['cart_items']=$order_items;
      //https://www.formget.com/curl-library-codeigniter/
        //echo $url;
       $customer_arr=array(
				'customer_name'=>$cart_order['fullname'],
				'email'=>$cart_order['email'],
				'mobile'=>$cart_order['phone'],
				'gender'=>'',
				'dob'=>'',
				'civilid_no'=>'',
				'city'=>$cust_address['city_id'],
				'block'=>$cust_address['block'],
				'street'=>$cust_address['street'],
				'avenue'=>$cust_address['jadda'],
				'building'=>$cust_address['houe_no'],
				'floor'=>$cust_address['floor'],
				'apartment'=>$cust_address['apartment'],
			);
			$product_arr=$pro_data;
			$customer_data=json_encode($customer_arr);
			$product_data=json_encode($product_arr);
			$return_url=site_url('pay-success/'.$cart_order_id);
			$error_url=site_url('pay-error/'.$cart_order_id);
			 $total_product=$cart_order['sum_total'];
			$merchantData = array(
					'customer'=>$customer_data,
					'merchant_code'=>25,
					'merchant_username'=>PAY_USERNAME_TEST,
					'password'=>PAY_PASSWORD_TEST,
					'reference_id'=>'123456',
					'return_url'=>$return_url,
					'error_url'=>$error_url,
					'product_data'=>$product_data,
					'subtotal'=>$total_product,
					'paymentMode'=>'knet',
					'currency_code' => 'KWD',
				);

        try {
		  //echo "<pre>".var_export($merchantData,true)."</pre>";
		  $this->load->library('paymenuhouse');

		    $r= $this->paymenuhouse->checkout_test($merchantData,PAYMENT_URL_TEST);

		   // $r= $this->paymenuhouse->checkout($merchantData,PAYMENT_URL);

		  // $r=json_decode($result,true);
			//var_dump($r);
			// echo "<pre>".var_export($r['paymentlink'],true)."</pre>";

			 $params = array(
           'invoice_id'=> $r['id'],
           'payment_url'=> $r['paymentlink'],
           'payment_date'=>DATE_TIME,
        		);
		  $this->cart_order_model->update_cart_order($cart_order_id,$params);

		 redirect($r['paymentlink']);
         // var_dump($result);
          }
          catch(Exception $e) {
            trigger_error(sprintf(
           'Curl failed with error #%d: %s',
           $e->getCode(), $e->getMessage()),
             E_USER_ERROR);
          }
}
/*   old knet payment checkout */
public function checkout( $customer_data=null,$product_data=array(),$merchantData=array(),$return_url=null,$error_url=null)
{
	$userbio=$this->mhlanguage->getuserbio();
    $sitelanguage=$this->mhlanguage->getsitelanguage();
	$suplang=$this->mhlanguage->getsuplanguage();
	 $currency_country=$this->mhlanguage->getcurrency();
	$suplier_suplier_bio_id=$userbio['suplier_suplier_bio_id'];
    //echo $suplier_suplier_bio_id;

		$data['no_cat']='none';
		$this->load->model('Cart_model');
		$this->load->model('Login_model');
		$this->load->model('Cart_item_model');
		$this->load->model('Product_model');

		$cart = $this->Cart_model->get_cartbysuplier_id($userbio['suplier_suplier_bio_id'],'active');

		$cartproduct=array();
		$pro_data=array();
		if($cart!=null)
		{
		   $cart_item = $this->Cart_item_model->get_all_cart_itembyc_id($cart['cart_id'],$currency_country['country_id']);
		   if($cart_item !=null)
		   {

			  foreach($cart_item as $c)
			  {

				$c['product']= $this->Product_model->get_product($c['suplier_product_id']);

				$p_data=$this->Product_model->get_productforpg($c['suplier_product_id']);

				$a=array();
		            $a['product_name']=$p_data['product_name'];
		            $a['unitPrice']=$c['cart_price'];
		            $a['qty']=$c['quantity'];
				$pro_data[]=$a;
				$cartproduct[]=$c;
			  }
		   }
		}
		  $data['cartproduct']=$cartproduct;
		//print_r($data);
		//echo "<pre>".var_export($userbio,true)."</pre>";
	 // echo "<pre>".var_export($data,true)."</pre>";
		 $total_product=0;
		foreach($cartproduct as $c)
		{
			$total=$c['quantity']*$c['cart_price'];
		    //echo $total."<br>";
			$total_product+=$total;
		}

		if($currency_country['country_code']=="KW")
        {
		//	echo  ">>".$total_product;
			if($total_product!=0)
			{
				//https://www.formget.com/curl-library-codeigniter/
				$url=PAYMENT_URL."paymh/paymentgatewayservicev1";
				$params=array();
			//	echo $url;
			/*	$this->curl->create($url);
				$this->curl->option('SSL_VERIFYPEER', false);
				$this->curl->option('SSL_VERIFYHOST', false);
				$this->curl->http_login('info@menu.house', 'admin!@#');
				$params['country_code']='KWD';
				$params['merchant_code']='99686019066';
				$params['merchant_username']='m.prabakaran@menu.house';$params['merchant_password']='000000';
				$result = $this->curl->simple_post($url,$params);
				//echo $result."<<";
				var_dump($result);
				var_dump(json_decode($result),true); */

			//	$url = 'http://localhost/codeigniter/api/example/user/';



			 $customer_arr=array(
				'customer_name'=>'Required',
				'email'=>'Required',
				'mobile'=>'Required',
				'gender'=>'Optional',
				'dob'=>'Optional',
				'civilid_no'=>'Optional',
				'city'=>'Optional',
				'block'=>'Optional',
				'street'=>'Optional',
				'avenue'=>'Optional',
				'building'=>'Optional',
				'floor'=>'Optional',
				'apartment'=>'Optional',
			);
			//multi dimentioanl array
			$product_arr=array(
				'product_name'=>'',
				'unitPrice'=>'',
				'qty'=>'',
			);
			/*	$customer_arr=array(
				'customer_name'=>$userbio['suplier_name'],
				'email'=>$userbio['suplier_name'],
				'mobile'=>$userbio['suplier_name'],
				'gender'=>'',
				'dob'=>'',
				'civilid_no'=>'',
				'city'=>'',
				'block'=>'',
				'street'=>'',
				'avenue'=>'',
				'building'=>'',
				'floor'=>'',
				'apartment'=>'',
			);*/
			//$product_arr=$pro_data;
			$customer_data=json_encode($customer_arr);
			$product_data=json_encode($product_arr);

			$return_url=site_url('pay-success');
			$error_url=site_url('pay-error');


				//$merchantData['customer']=$customer;
		 	//echo "<pre>".var_export($product_arr,true)."</pre>";
			// echo "<pre>".var_export($merchantData,true)."</pre>";
				//create a new cURL resource
				//API key
		  //  echo $url;
			$apiKey = PAY_APIKEY;
		   //Auth credentials
			$username = PAY_USERNAME;
			$password = PAY_PASSWORD;

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-API-KEY: " . $apiKey,
														 'Content-Type:  multipart/form-data'
														));
			curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $merchantData);
			$result = curl_exec($ch);
				//close cURL resource
			curl_close($ch);
			$r=json_decode($result,true);
			// var_dump($r);
			// echo "<pre>".var_export($r['paymentlink'],true)."</pre>";
			 redirect($r['paymentlink']);
			}
		}
		else {
			 echo "Currency code not match";
		}
}

/* success redirection*/
public function pay_success()
{
	echo "success";
}
/*pay_errors*/
public function pay_errors()
{
		echo "error";
}


}