<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    //DB functions
    public function getProdcutById($id){
        $products= DB::select('select * from ec_products where id= ?', [$id]);
        if(count($products)>0){
            return $products[0];
        }
        else{
            return null;
        } 
    }
    public function getProductsIdByCategoryId($categoryId){
        $productIds= DB::select('select distinct product_id from ec_product_category_product where category_id= ?', [$categoryId]);
        return $productIds;
    }
    public function getProductsByProductIds($productIds){
        $products= array();
        for($i= 0; $i< count($productIds); $i++){
            $product_id= $productIds[$i]->product_id;
            $product= DB::select('select * from ec_products where id= ?', [$product_id]);
            if(count($product)> 0){
                array_push($products, $product[0]);
            }
            
        }
        return $products;
    }
    public function getProductsByProductIdswithFilter($modifiedProductIds, $price, $order, $orderby){
        $products= DB::table('ec_products')
            ->whereIn('id', $modifiedProductIds)
            ->where('price', '<', $price)
            ->orderBy($orderby, $order)
            ->get();
        //to convert to array
        $products = json_decode(json_encode($products), true);
        return $products;
    }
    public function getProductsByNameWithfilter($name, $categoryId, $price){
        if($categoryId!= null){
            $productIds= $this->getProductsIdByCategoryId($categoryId);
            $modifiedProductIds= [];
            if(count($productIds)> 0){
                for($i= 0; $i< count($productIds); $i++){
                    array_push($modifiedProductIds, $productIds[$i]->product_id);
                }
            }
            if($name!= ''){
                $products= DB::table('ec_products')
                    ->whereIn('id', $modifiedProductIds)
                    ->where(DB::raw('lower(name)'), 'like', '%' . strtolower($name) . '%')//case insensitive
                    ->where('price', '<', $price)
                    ->get();
                $products = json_decode(json_encode($products), true);
                return $products;
            }
            else{
                $products= DB::table('ec_products')
                    ->whereIn('id', $modifiedProductIds)
                    ->where('price', '<', $price)
                    ->get();
                $products = json_decode(json_encode($products), true);
                return $products;
            }
            
        }else{
            $products= DB::table('ec_products')
                ->where(DB::raw('lower(name)'), 'like', '%' . strtolower($name) . '%')//case insensitive
                ->where('price', '<', $price)
                ->get();
            $products = json_decode(json_encode($products), true);
            return $products;
        }
        
         
    }
    public function featuredProducts(){
        $products= DB::select('select * from ec_products where is_featured= ?', [1]);
        $products= json_decode(json_encode($products), true);
        return $products;
    }
    public function getCustomerBytoken($phone){
        $customers= DB::select('select * from ec_customers where phone= ?', [$phone]);
        if(count($customers)>0){
            return $customers[0];
        }
        else{
            return 'invalid user';
        } 
    }
    public function getPaymentsByCustomerId($customerId){
        $orders= DB::select('select * from payments where customer_id= ?', [$customerId]);
        return $orders;
    }
    public function getOrdersByCustomerId($customerId){
        $orders= DB::select('select * from ec_orders where user_id= ?', [$customerId]);
        // $orders= json_decode(json_encode($orders), true);
        return $orders;
    }
    
    public function getOrderAdd($orderId){
        $orderAdds= DB::select('select * from ec_order_addresses where order_id= ?', [$orderId]);
        if(count($orderAdds)> 0){
            return $orderAdds[0];
        }
        else{
            return null;
        } 
    }
    public function getLineItems($orderId){
        $line_items= DB::select('select * from ec_order_product where order_id= ?', [$orderId]);
        $line_items= json_decode(json_encode($line_items), true);
        return $line_items;
    }
    public function getCurrencyByTitle($title){
        $currencies= DB::select('select * from ec_currencies where title= ?', [$title]);
        if(count($currencies)> 0){
            return $currencies[0];
        }
        else{
            return null;
        }
    }
    public function getCurrencyById($id){
        $currencies= DB::select('select * from ec_currencies where id= ?', [$id]);
        if(count($currencies)>0){
            return $currencies[0];
        }
        else{
            return null;
        }
    }
    //request handlers
    public function getAllproducts(Request $request){
        $products= DB::select('select* from ec_products where quantity> ?', [0]);
        if(count($products)> $request->input('per_page')){
            if($page==1){
                $products= array_slice($products, 0, $request->input('per_page'));
            }
        }
        return response()->json($products);
    }

    public function getBannerHighProduct(Request $request){
        $productId= $request->input('tagId');
        $modifiedProductIds= [];
        for($i= $productId; $i< $productId+ 3; $i++){
            array_push($modifiedProductIds, $i);
        }
        $products= DB::table('ec_products')
            ->whereIn('id', $modifiedProductIds)
            ->get();
        //to convert to array
        $products = json_decode(json_encode($products), true);
        return response()->json($products);
    }

    public function getFeaturedProducts(Request $request){
        $page= $request->input('page');
        $products= $this->featuredProducts();
        $products= array_slice($products, ($page-1)*20, $page*20-1);
        return response()->json($products);
    }
    public function getProductsByCategoryId(Request $request){
        $categoryId= $request->input('categoryId');
        $page= $request->input('page');
        $productIds= $this->getProductsIdByCategoryId($categoryId);
        $products= $this->getProductsByProductIds($productIds);
        $products= array_slice($products, ($page-1)*20, $page*20-1);
        return response()->json($products);
    }

    public function productsByCategoryIdFilter(Request $request){
        $filters= $request->input('filters');
        //distinguish if filtered by sub category.
        $categoryId= $request->input('categoryId');
        if(array_key_exists('category', $filters)){
            $categoryId= $filters['category'];
        }
        
        $per_page= $request->input('per_page');
        $page= $request->input('page');
        
        $price= 1000000000;
        if(array_key_exists('max_price', $filters)){
            $price= $filters['max_price'];
        }
        $order= "asc";
        $orderby= "id";
        if(array_key_exists('order', $filters)){
            $order= $filters['order'];
            if($filters['orderby']== "title"){
                $orderby= "name";
            }else{
                $orderby= "created_at";
            }
        }

        $productIds= $this->getProductsIdByCategoryId($categoryId);
        $modifiedProductIds= [];
        if(count($productIds)> 0){
            for($i= 0; $i< count($productIds); $i++){
                array_push($modifiedProductIds, $productIds[$i]->product_id);
            }
        }
        $products= $this->getProductsByProductIdswithFilter($modifiedProductIds, $price, $order, $orderby);
        
        // if(count($products)> $page*$per_page){
        //     $products= array_slice($products, 0, $page*$per_page);
        // }
        $products= array_slice($products, ($page-1)*20, $page*20-1);
        return response()->json($products);
        
    }
    public function productsByName(Request $request){
        $filter= $request->input('filter');
        //distinguish if filtered by sub category.
        $categoryId= null;
        $name= $request->input('name');
        $per_page= $request->input('per_page');
        $page= $request->input('page');
        $price= 1000000000;
        if($filter){
            if(array_key_exists('category', $filter)){
                $categoryId= $filter['category'];
            }
            
            if(array_key_exists('max_price', $filter)){
                $price= $filter['max_price'];
            }
        }
        
        $products= $this->getProductsByNameWithfilter($name, $categoryId, $price);
        $products= array_slice($products, ($page-1)*20, $page*20-1);
        return response()->json($products);
    }
    public function getAllCategories(){
        $categories= DB::select('select* from ec_product_categories where id> ?', [0]);
        return response()->json($categories);
    }

    public function reviewsByProductId(Request $request){//don't implement to get reviewer.
        $productId= $request->input('productId');
        $reviews= DB::select('select* from ec_reviews where product_id=?', [$productId]);
        return response()->json($reviews);
    }

    public function createComment(Request $request){
        DB::table('ec_reviews')->insert([
            'customer_id'=>$request->input('user_id'),
            'product_id'=>$request->input('post_id'),
            'star'=>$request->input('rating'),
            'comment'=>$request->input('content'),
            'status'=>'published',
            'created_at'=>date('Y-m-d')
        ]);
        return "ok";
    }

    //payment
    public function getAllCurrencies(){
        $currencies= DB::select('select * from ec_currencies where id> ?', [0]);
        if(count($currencies)>0 ){
            for($i= 0; $i< count($currencies); $i++){
                $currencies[$i]->{'code'}= $currencies[$i]->title;
                $currencies[$i]->{'decimal_digits'}= $currencies[$i]->decimals;
                $currencies[$i]->{'rounding'}= 0;
                $currencies[$i]->{'symbol_native'}= $currencies[$i]->symbol;
                
            }
        }
        return response()->json($currencies) ;
    }
    public function getAllCouponCode(){
        $couponCodes= DB::select('select* from ec_discounts where id> ?', [0]);
        return response()->json($couponCodes);
    }

    public function getAddressDefault(Request $request){
        $addresses= DB::table('ec_customer_addresses')
            ->where('customer_id', '=', $request->input('customerId'))
            ->where('is_default', '=', 1)
            ->get();
        //to convert to array
        $addresses = json_decode(json_encode($addresses), true);
        if(count($addresses)> 0){
            return response()->json($addresses[0]);
        }
        else{
            return "no";
        } 
    }

    public function createNewOrder(Request $request){
        $customerValid= $this->getCustomerBytoken($request->input('token'));
        if($customerValid== 'invalid user'){
            return 'invalid user';
        }
        $coupon_code= null;
        $discount_amount= null;
        if($request->input('coupon_lines')){
            $coupon_code= $request->input('coupon_lines')[0]['code'];
            $discount_amount= $request->input('coupon_lines')[0]['discount'];
        }
        $currency= $request->input('currency');
        $currencyId= $this->getCurrencyByTitle($currency);
        if($currencyId== null){
            $currencyId= 1;
        }else{
            $currencyId= $currencyId->id;
        }
        $token= bcrypt($request->input('token'));
        //this is for order create
        $orderId= DB::table('ec_orders')->insertGetId([
            'user_id'=>$request->input('customer_id'),
            'shipping_method'=>'default',
            'amount'=>$request->input('subTotal'),
            'sub_total'=>$request->input('totalPrice'),
            'is_confirmed'=> 0,
            'discount_amount'=> $discount_amount,
            'coupon_code'=>$coupon_code,
            'description'=>$request->input('customer_note'),
            'is_finished'=> 1,
            'created_at'=>date('Y-m-d'),
            'updated_at'=>date('Y-m-d'),
            'token'=>$token,
            'currency_id'=>$currencyId // now currency is not valid
        ]);
        //this is for relateion with order and product
        $products= $request->input('line_items');
        for($i= 0; $i<count($products); $i++){
            $product= $this->getProdcutById($products[$i]['product_id']);
            DB::table('ec_order_product')->insert([
                'order_id'=>$orderId,
                'qty'=>$products[$i]['quantity'],
                'price'=>$product->price,
                'product_id'=>$products[$i]['product_id'],
                'product_name'=>$product->name,
                'created_at'=>date('Y-m-d'),
                'updated_at'=>date('Y-m-d'),
                'tax_amount'=> 0
            ]);
        }
        //this is for order address save
        DB::table('ec_order_addresses')->insert([
            'name'=>$request->input('shipping')['name'],
            'phone'=>$request->input('billing')['phone'],
            'email'=>$request->input('billing')['email'],
            'country'=>"Bangladesh",
            'city'=>$request->input('shipping')['city'],
            'state'=>$request->input('shipping')['state'],
            'address'=>$request->input('billing')['address'],
            'order_id'=>$orderId
        ]);
        //this is for customer address save
        
        DB::table('ec_customer_addresses')->insert([
            'name'=>$request->input('billing')['name'],
            'email'=>$request->input('billing')['email'],
            'phone'=>$request->input('billing')['phone'],
            'country'=>"Bangladesh",
            'state'=>$request->input('billing')['state'],
            'city'=>$request->input('billing')['city'],
            'address'=>$request->input('billing')['address'],
            'customer_id'=>$customerValid->id
        ]);

        //this is for payments save
        $paymentId=DB::table('payments')->insertGetId([
            'currency'=>'BDT',
            'user_id'=>$customerValid->id,
            'payment_channel'=>'cod',
            'amount'=>$request->input('subTotal'),
            'order_id'=>$orderId,
            'status'=>'pending',
            'payment_type'=>'confirm',
            'customer_id'=>$customerValid->id,
            'created_at'=>date('Y-m-d'),
            'updated_at'=>date('Y-m-d'),
        ]);
        //this is for update ec_orders table by payment_id
        DB::table('ec_orders')
              ->where('id', $orderId)
              ->update(['payment_id'=>$paymentId]);
        return 'ok';
    }
    public function updateOrder(Request $request){
        $orderId= $request->input('orderId');
        $status= $request->input('status');
        DB::table('ec_orders')
              ->where('id', $orderId)
              ->update(['status' => $status,
                        'description'=> 'This order has been '.$status.' at '.date('Y-m-d').'.']);
        //this is for payments table update
        DB::table('payments')
            ->where('order_id', $orderId)
            ->update(['status'=> $status]);
        return response()->json(['id'=> $orderId]);
    }
    public function ordersByCustomerId(Request $request){
        //this is to get from payments

        $userId= $request->input('userId');
        $orders= $this->getPaymentsByCustomerId($userId);
        //modify for address and line_items
        if(count($orders)>0 ){
            for($i= 0; $i< count($orders); $i++){
                $orderId= $orders[$i]->order_id;
                $orderAdd= $this->getOrderAdd($orderId);
                $currency= $orders[$i]->currency;
                if($currency== null){
                    $currency= "BDT";
                }
                $orders[$i]->{'id'}= $orderId;
                $orders[$i]->{'country'}= "";
                $orders[$i]->{'city'}= "";
                $orders[$i]->{'address'}= "";
                $orders[$i]->{'currency'}= $currency;
                $orders[$i]->{'sub_total'}= $orders[$i]->amount;
                if($orderAdd){
                    $orders[$i]->{'country'}= $orderAdd->country;
                    $orders[$i]->{'city'}= $orderAdd->city;
                    $orders[$i]->{'address'}= $orderAdd->address;
                }
                $orderLineItems= $this->getLineItems($orderId);
                $orders[$i]->{'line_items'}= $orderLineItems;
            }
        }
        //this is to get from ec_orders;

        // $userId= $request->input('userId');
        // $orders= $this->getOrdersByCustomerId($userId);
        // //modify for address and line_items
        // if(count($orders)>0 ){
        //     for($i= 0; $i< count($orders); $i++){
        //         $orderId= $orders[$i]->id;
        //         $orderAdd= $this->getOrderAdd($orderId);
        //         $currency= $this->getCurrencyById($orders[$i]->currency_id);
        //         if($currency== null){
        //             $currency= "BDT";
        //         }
        //         else{
        //             $currency= $currency->title;
        //         }
        //         $orders[$i]->{'country'}= "";
        //         $orders[$i]->{'city'}= "";
        //         $orders[$i]->{'address'}= "";
        //         $orders[$i]->{'currency'}= $currency;
        //         if($orderAdd){
        //             $orders[$i]->{'country'}= $orderAdd->country;
        //             $orders[$i]->{'city'}= $orderAdd->city;
        //             $orders[$i]->{'address'}= $orderAdd->address;
        //         }
        //         $orderLineItems= $this->getLineItems($orderId);
        //         $orders[$i]->{'line_items'}= $orderLineItems;
        //     }
        // }
        return $orders;
    }
    //auth
    public function emailUnique(Request $request){
        $email= $request->input('email');
        $users= DB::select('select * from ec_customers where email= ?', [$email]);
        if(count($users)>0){
            return "email in use";
        }
        else{
            return "ok";
        }
    }
    public function phoneUnique(Request $request){
        $phoneNumber= $request->input('phoneNumber');
        if (strpos($phoneNumber, '+') !== false) {
            $phoneNumber= $phoneNumber;
        }
        else{
            $phoneNumber= "+".$phoneNumber;
        }
        
        $users= DB::select('select * from ec_customers where phone= ?', [$phoneNumber]);
        if(count($users)>0){
            return "phone in use";

        }
        else{
            return $phoneNumber;
        }
    }
    public function register(Request $request){
        $name= $request->input('username');
        $email= $request->input('email');
        $fcm_token= $request->input('fcm_token');
        $phone= $request->input('phoneNumber');
        if (strpos($phone, '+') !== false) {
            $phone= $phone;
        }
        else{
            $phone= "+".$phone;
        }
        $password= bcrypt($request->input('password'));
        $id= DB::table('ec_customers')->insertGetId([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'phone' => $phone
        ]);
        $tokens= DB::select('select device_token from ec_customers_token where user_id= ?', [$id]);
        if(count($tokens)>0){
            $token= $tokens[0]->device_token;
            $tokenToArr= json_decode($token);
            if(in_array($fcm_token, $tokenToArr)){
            }
            else{
                array_push($tokenToArr, $fcm_token);
                $modifiedTkArr= json_encode($tokenToArr);
                DB::table('ec_customers_token')
                    ->where('user_id', $id)
                    ->update(['device_token' => $modifiedTkArr]);
            }
        }
        else{
            $fcmToArr= array($fcm_token);
            $modifiedArr= json_encode($fcmToArr);
            DB::table('ec_customers_token')->insert([
                'user_id'=>$id,
                'device_token'=>$modifiedArr
            ]);
        }
        // $cookie= withCookie(cookie('cookie', $email));
        return response()->json(['user_id'=> $id, 'cookie'=> $phone]);
    }
    public function getUserById(Request $request){
        $id= $request->input('userId');
        $users= DB::select('select * from ec_customers where id= ?', [$id]);
        if(count($users)>0 ){
            return response()->json($users[0]);
        }
    }

    public function login(Request $request){
        $password= $request->input('password');
        $phoneNumber= $request->input('phoneNumber');
        if (strpos($phoneNumber, '+') !== false) {
            $phoneNumber= $phoneNumber;
        }
        else{
            $phoneNumber= "+".$phoneNumber;
        }
        $users = DB::table('ec_customers')
                ->where('phone', '=', $phoneNumber)->get();
                // ->where('password', '=', $password)->get();
        if(count($users)>0){
            $hashpassword= $users[0]->password;
            if(password_verify($password, $hashpassword)){
                return response()->json(['user.id'=> $users[0]->id, 'cookie'=> $phoneNumber]);
            }
        }
    }
    public function resetPass(Request $request){
        
        $phone= $request->input('phoneNumber');
        if (strpos($phone, '+') !== false) {
            $phone= $phone;
        }
        else{
            $phone= "+".$phone;
        }
        $password= bcrypt($request->input('password'));
        DB::table('ec_customers')
              ->where('phone', $phone)
              ->update(['password' => $password]);
        $idTmps= DB::table('ec_customers')
                ->where('phone', $phone)->get();
        $id= $idTmps[0]->id;
        // $cookie= withCookie(cookie('cookie', $email));
        return response()->json(['user_id'=> $id, 'cookie'=> $phone]);
    }
    //profile
    public function getCustomerAddressInfo(Request $request){
        $userId= $request->input('userId');
        $addresses= DB::select('select * from ec_customer_addresses where customer_id= ?', [$userId]);
        return $addresses;
    }
    public function removeAddress(Request $request){
        $id= $request->input('id');
        $customerId= $request->input('customerId');
        DB::table('ec_customer_addresses')->where('id', '=', $id)->delete();
        $addresses= DB::select('select * from ec_customer_addresses where customer_id= ?', [$customerId]);
        return $addresses;
    }
    public function addAddress(Request $request){
        $customerId= $request->input('customerId');
        DB::table('ec_customer_addresses')->insert([
            'name'=>$request->input('name'),
            'email'=>$request->input('email'),
            'phone'=>$request->input('phone'),
            'country'=>"Bangladesh",
            'state'=>$request->input('state'),
            'city'=>$request->input('city'),
            'address'=>$request->input('address'),
            'zip_code'=> $request->input('postcode'),
            'customer_id'=>$customerId
        ]);
        $addresses= DB::select('select * from ec_customer_addresses where customer_id= ?', [$customerId]);
        return $addresses;
    }
    public function getCustompages(Request $request){
        $title= $request->input('title');
        $htmls= DB::select('select content from pages where name= ?', [$title]);
        if(count($htmls)>0){
            $html= $htmls[0]->content;
            return response()->json(["data"=>$html]);
        }
        else{
            return response()->json(["data"=>"contact us"]);
        }
    }

    //save notification token handling from React Native
    public function saveDeviceToken(Request $request){
        $fcm_token= $request->input('fcm_token');
        $userId= $request->input('userId');
        $tokens= DB::select('select device_token from ec_customers_token where user_id= ?', [$userId]);
        if(count($tokens)>0){
            $token= $tokens[0]->device_token;
            $tokenToArr= json_decode($token);
            if(in_array($fcm_token, $tokenToArr)){
                return 'token not changed';
            }
            else{
                array_push($tokenToArr, $fcm_token);
                $modifiedTkArr= json_encode($tokenToArr);
                DB::table('ec_customers_token')
                    ->where('user_id', $userId)
                    ->update(['device_token' => $modifiedTkArr]);
                return 'token added';
            }
        }
        else{
            $fcmToArr= array($fcm_token);
            $modifiedArr= json_encode($fcmToArr);
            DB::table('ec_customers_token')->insert([
                'user_id'=>$userId,
                'device_token'=>$modifiedArr
            ]);
            return 'token added';
        }
    }

    //here differant is that in "registration_ids" this is array, and 'to' is not array.
    public function sendPushNotiToAdmin(Request $request)
    {
        $SERVER_API_KEY = "AAAAWqeaKSE:APA91bFB_iiRqU5bs7lkHi4SZvw0PUPV2_penKqFBwfj29-ldHs6829uagebQGQyi85Zw_nocUTqXjdA-w_-pDxxVw0_1EjNzk3AfchYLBrydZvrIxeXXLA0D1F-VWGwN1XV2j_VmfX5";
        $device_token= $this->fcmAdminTokenByUserId();
        if($request->input('billing')!= null){
            $user= $request->input('billing')['name'];
            $data = [
                "registration_ids" => $device_token,
                "notification" =>
                    [
                        "title" => 'New order',
                        "body" => "A new order placed by ".$user."."
                    ],
            ];
        }
        else if($request->input('status')!= null){
            $status= $request->input('status');
            $orderId= $request->input('orderId');
            $data= [
                "registration_ids" => $device_token,
                "notification" =>
                    [
                        "title" => 'Order status changed',
                        "body" => "Order id ".$orderId." changed to ".$status." by user."
                    ],
            ];
        }
        
        $dataString = json_encode($data);
  
        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];
  
        $ch = curl_init();
  
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
  
        curl_exec($ch);
        return response()->json(["data"=>$device_token]);;
        // return redirect('/home')->with('message', 'Notification sent!'); 
    }

    public function fcmAdminTokenByUserId(){
        $fcmUsers= DB::select('select * from ec_customers_token where user_id= ?', [0]);
        $device_token= [];
        if(count($fcmUsers)>0){
            $device_tokenTmp= $fcmUsers[0]->device_token;
            $device_token= json_decode($device_tokenTmp);
        }
        return $device_token;
    }
    
}
