<?php

namespace App\Http\Controllers;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\ProductCollection;

use Carbon\Carbon;
use App\Events\OrderEvent;
use App\Events\StatusOnOrder;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\DeleveredOrder;
use App\Models\DeliveryRangeQty;
use App\Models\OnlineOrders;

use App\Models\OrderDetailsModel;
use DB;

class OrderController extends Controller
{   

  public function createWalkin(Request $request)
  {
    try{
      $data = $request->all();
      $price = DB::table('products')
        ->select('product_price')
        ->where('product_name',$data['product_name'])
        ->value('product_price');
      $total = (int)$price*(int)$data['product_quantity'];

      if(OnlineOrders::where('order_id',$data['order_id'] )->exists()){
        
        $randomId  =   rand(2,5000);
        $post = new OnlineOrders;
        $post->order_id = $randomId;
        $post->receiver_name = $data['receiver_name'];
        $post->email = $data['email'];
        $post->order_status = $data['order_status'];
        $post->building_or_street = $data['building_street'];
        $post->barangay = $data['barangay'];
        $post->city_or_municipality = $data['city_municipality'];
        $post->province = $data['province'];
        $post->contact_number = $data['contactNumber']; 
        $post->total_payment = $total;
        $post->preferred_delivery_date = $data['deliveryDate'];
        $post->landmark = $data['landmark'];
        $post->payment_method = $data['payment_method'];
        $post->payment_status = $data['payment_status'];
        $post->save();
       }else{
        $post = new OnlineOrders;
        $post->order_id = $data['order_id'];
        $post->receiver_name = $data['receiver_name'];
        $post->email = $data['email'];
        $post->order_status = $data['order_status'];
        $post->building_or_street = $data['building_street'];
        $post->barangay = $data['barangay'];
        $post->city_or_municipality = $data['city_municipality'];
        $post->province = $data['province'];
        $post->contact_number = $data['contactNumber']; 
        $post->total_payment = $total;
        $post->preferred_delivery_date = $data['deliveryDate'];
        $post->landmark = $data['landmark'];
        $post->payment_method = $data['payment_method'];
        $post->payment_status = $data['payment_status'];
        $post->save();
       }
    
      //event(new OrderEvent($post));
      return 'success';
    } catch (\Exception $e){
      //event(new OrderEvent('good'));
      return response()->json(['error'=>$e->getMessage()]);
    }
  }

  public function fetchProduct(){
    try {
      return 
      DB::table('products')
      ->select('*')
      ->get();
    } catch (\Exception $e) {
      return response()->json(['error'=>$e->getMessage()]);
    }

  }
  
    public function createOrder(Request $request)
    {
      try{
        $post = new OnlineOrders;
        $data = $request->all();
        $post->receiver_name = $data['receiver_name'];
        $post->order_id = $data['order_id'];
        $post->building_or_street = $data['landmark'];
        $post->landmark = $data['landmark'];
        $post->barangay = $data['barangay'];
        $post->city_or_municipality = $data['city_municipality'];
        $post->province = $data['province'];
        $post->email = $data['email'];
        $post->contact_number = $data['contactNumber'];
        $post->total_payment = $data['total_payment'];
        $post->payment_method = $data['payment_method'];
        $post->payment_status = $data['payment_status'];
        $post->preferred_delivery_date = $data['deliveryDate'];
        $post->order_status = $data['order_status'];
        $post->save();
        event(new OrderEvent($post));
        return 'success';
      } catch (\Exception $e){
        event(new OrderEvent('good'));
        return response()->json(['error'=>$e->getMessage()]);
      }
    }
  
   public function fetchOrder()
    {
      try {
        return new OrderCollection(Order::where('order_status', 'On order')
          ->orWhere('order_status', 'Canceled')
          ->orderBy('preferred_delivery_date', 'asc')
          ->get());
      } catch (\Exception $e) {
        return response()->json(['error'=>$e->getMessage()]);
      }
    }


    public function fetchPendingOrder()
    {
      try {
        return new OrderCollection(Order::where('order_status', 'Pending')
        ->orderBy('preferred_delivery_date', 'asc')
        ->get());
      } catch (\Exception $e) {
        return response()->json(['error'=>$e->getMessage()]);
      }
      
    }

    public function fetchDelivered()
    {
      try {
        // return new OrderCollection(Order::where('order_status', 'Delivered')
        // ->orderBy('preferred_delivery_date', 'desc')
        // ->get());
      //  $data = DB::table('online_orders')
      //   ->select('receiver_name', 'building_or_street', 
      //     'barangay','city_or_municipality', 'province', 
      //     'contact_number','distance','preferred_delivery_date',
      //     'ubehalayajar_qty','ubehalayatub_qty', 'total_payment',
      //     'order_status' )
      //     ->where("order_status", "=", "Delivered")
      //     ->get();
      //     return response()->json(compact('data'));

      $order_products = array();
      $data = DB::table('online_orders')
      ->join('order_details', 'order_details.order_id', '=', 'online_orders.order_id')
      ->select('online_orders.*', 'order_details.*')
      ->where("order_status", "=", "Delivered")
      ->get();
      
      $arr = array();
      foreach($data as $key){
        $order_details = DB::table('products')
        ->select('*')
        ->where('id', $key->product_id)
        ->get();

        // $order_details = (array) $order_details;
        $arr = (array)$key;

        // $order_details['order_quantity'] = $key->order_quantity;
        $arr['line_items'] = $order_details;
        $order_products[] = $arr;
      }
      return $order_products;
      } catch (\Exception $e) {
        return response()->json(['error'=>$e->getMessage()]);
      }
    }

    public function totalTab(){
      try {
        $date = Carbon::today();
        $data = DB::table('orders')
        ->where('preferred_delivery_date',$date)
        ->where('order_status', 'On order' )
        ->get();
        
        $i = 0;
        $total = 0;
        foreach($data as $item){
            $total += $item->ubehalayatub_qty;
            $i++;
        }
        return $total;
      } catch (\Exception $e){
        return response()->json(['error'=>$e->getMessage()]);
      }
  }

  public function totalJar(){
    try {
      $date = Carbon::today();
      $data = DB::table('orders')
      ->where('preferred_delivery_date',$date)
      ->where('order_status', 'On order' )
      ->get();
      $i = 0;
      $total = 0;
      foreach($data as $item){
          $total += $item->ubehalayajar_qty;
          $i++;
      }
      return $total;
    } catch (\Exception $e){
      return response()->json(['error'=>$e->getMessage()]);
    }
}

public function fetchDelivery(Request $request){
  try {
    $order_products = array();
    $data = DB::table('online_orders')
    ->join('order_details', 'order_details.order_id', '=', 'online_orders.order_id')
    ->select('online_orders.*', 'order_details.*')
    ->where('online_orders.preferred_delivery_date', Carbon::today()->toDateString())
    ->where( function($query) {
      $query->where('online_orders.order_status', 'On order')
      ->orWhere('online_orders.order_status', 'Canceled')
      ->orWhere('online_orders.order_status', 'Delivered');
    })
    ->orderBy('online_orders.distance', 'asc')
    ->get();
    
    $arr = array();
    foreach($data as $key){
      $order_details = DB::table('products')
      ->select('*')
      ->where('id', $key->product_id)
      ->get();

      // $order_details = (array) $order_details;
      $arr = (array)$key;

      // $order_details['order_quantity'] = $key->order_quantity;
      $arr['line_items'] = $order_details;
      $order_products[] = $arr;
    }
    return $order_products;
    // $order_products = array();
    // $data = OnlineOrders::where('preferred_delivery_date', Carbon::today()->toDateString())
    // ->where( function($query) {
    //   $query->where('order_status', 'On order')
    //   ->orWhere('order_status', 'Canceled')
    //   ->orWhere('order_status', 'Delivered');
    // })
    // ->orderBy('distance', 'asc')
    // ->get();

    // $arr = array();
    //   foreach($data as $key){
    //     $order_details = DB::table('products')
    //     ->select('*')
    //     ->where('id', $key->product_id)
    //     ->get();

    //     // $order_details = (array) $order_details;
    //     $arr = (array)$key;

    //     // $order_details['order_quantity'] = $key->order_quantity;
    //     $arr['line_items'] = $order_details;
    //     $order_products[] = $arr;
    //   }
    //   return $order_products;
  } catch (\Exception $e){
    return response()->json(['error'=>$e->getMessage()]);
  }
}

// public function fetchDeliveries(Request $request){
//   try {
//     $data = Order::select('receiver_name','customer_address','contact_number','distance','preferred_delivery_date','ubehalayajar_qty','ubehalayatub_qty','total_item','total_payment','order_status')->where('preferred_delivery_date', Carbon::today()->toDateString())
//     ->orWhere( function($query) {
//       $query->where('order_status', 'On order')
//       ->orWhere('order_status', 'Canceled')
//       ->orWhere('order_status', 'Delivered');
//     })
//     ->orderBy('distance', 'asc')
//     ->get();
//     return response()->json(compact('data'));
//   } catch (\Exception $e){
//     return response()->json(['error'=>$e->getMessage()]);
//   }
// }

    public function updateCancelledStatus(Request $request, $id)
    {
      try {
        $newItem =  $request->all();
        $post = OnlineOrders::firstOrCreate(['id' => $request->id]);
        $post->order_status = 'Cancelled';
        $post->save();
        return response()->json(compact('post'));
      } catch (\Exception $e){
        return response()->json(['error'=>$e->getMessage()]);
      }
      
    }


    public function editOrder($id)
    {
      try {
        $post = Order::find($id);
        return response()->json($post);
      } catch (\Exception $e){
        return response()->json(['error'=>$e->getMessage()]);
      }
    }


    public function updateOrder(Request $request)
    {
       try {
        $newItem =  $request->all();
        $post = OnlineOrders::firstOrCreate(['id' => $request->id]); 
        $post->receiver_name = $request['receiver_name'];
        $post->building_or_street = $request['building_or_street'];
        $post->barangay = $request['barangay'];
        $post->city_or_municipality = $request['city_or_municipality'];
        $post->province = $request['province'];
        $post->preferred_delivery_date = $request['preferred_delivery_date'];
        // $post->ubehalayajar_qty = $request['ubehalayajar_qty'];
        // $post->ubehalayatub_qty = $request['ubehalayatub_qty'];
        $post->distance = $request['distance'];
        $post->save();
        return response()->json(compact('post'));
       } catch (\Exception $e){
        return response()->json(['error'=>$e->getMessage()]);
      }
     
    }

    public function updateStatus(Request $request, $id)
    {
      try {
        $newItem =  $request->all();
        $post = Order::firstOrCreate(['id' => $id]);
        $post->order_status = 'Delivered';
        $post->save();
        event(new OrderEvent($post));
        return response()->json(compact('post'));
      } catch (\Exception $e){
        return response()->json(['error'=>$e->getMessage()]);
      }
    }


    public function fetchOnOrderStat($id){
      try {
        $post = new OrderCollection(Order::where('order_status', 'On order')
        ->orWhere('order_status', 'Pending')
        ->where('customer_id','=', $id)
        ->orderBy('preferred_delivery_date')
        ->get());
        return response()->json(compact('post'));
      } catch (\Exception $e){
        return response()->json(['error'=>$e->getMessage()]);
      }
    }
    
    public function fetchOngoingOrder($id){
      $post = new OrderCollection(Order::where('customer_id', $id)
      ->where(function($q) {
          $q->where('order_status', 'On order')
            ->orWhere('order_status', 'Pending');
      })
      ->orderBy('id', 'DESC')
      ->get());
      return response()->json(compact('post'));
    }

    public function fetchDeliveredOrder($id){
      try {
        $post = new OrderCollection(Order::where('order_status', 'Delivered')
        ->where('customer_id','=', $id)
        ->orderBy('preferred_delivery_date')
        ->get());
        return response()->json(compact('post'));
      } catch (\Exception $e){
        return response()->json(['error'=>$e->getMessage()]);
      }
    }
    
  
    public function deleteOrder($id)
    {
      try {
        $post = Order::find($id);
        $post->delete();
      return response()->json('successfully deleted');
      } catch (\Exception $e){
        return response()->json(['error'=>$e->getMessage()]);
      }
    }



    public function updateConfirmStatus(Request $request, $id){
      try {
        $newItem =  $request->all();
        $post = Order::firstOrCreate(['id' => $id]);
        $post->order_status = 'On order';
       
        $post->save();
         event(new StatusOnOrder($post));
        return response()->json($post);
      } catch (\Exception $e) {
        return response()->json(['error'=>$e->getMessage()]);
      }
    }

    public function fetchProcessOrder()
    {
      try {
        return new OrderCollection(Order::where('order_status', 'On order')
          ->orWhere('order_status', 'Pending')
          ->orderBy('id', 'DESC')
          ->get());
      } catch (\Exception $e) {
        return response()->json(['error'=>$e->getMessage()]);
      }
    }

    public function unReadOrder($id){
      $post = new OrderCollection(Order::where('customer_id', $id)
      ->where('mark_status','Unread')
      ->where(function($q) {
          $q->where('order_status', 'On order')
            ->orWhere('order_status', 'Pending');
      })
      ->orderBy('preferred_delivery_date')
      ->get());
      return response()->json(compact('post'));
    }

    public function unreadAdminOrder()
    {
      $post = new OrderCollection(Order::where('mark_adminstatus', 'Unread')
      ->where(function($q) {
          $q->where('order_status', 'On order')
            ->orWhere('order_status', 'Pending');
      })
      ->orderBy('id', 'asc')
      ->get());
      return response()->json(compact('post'));
      }
    

    public function updateMarkStatus(Request $request, $id){
      try {
        $newItem =  $request->all();
        $post = Order::firstOrCreate(['id' => $id]);
        $post->mark_status = 'Read';
        $post->save();
        event(new OrderEvent($post));
        return response()->json($post);
      } catch (\Exception $e) {
        return response()->json(['error'=>$e->getMessage()]);
      }
    }

    public function updateadminStatus(Request $request, $id){
      try {
        $newItem =  $request->all();
        $post = Order::firstOrCreate(['id' => $id]);
        $post->mark_adminstatus = 'Read';
        $post->save();
        event(new OrderEvent($post));
        return response()->json($post);
      } catch (\Exception $e) {
        return response()->json(['error'=>$e->getMessage()]);
      }
    }

    public function filter(Request $request,$month,$year){
      try{
        $data = DB::table('orders')
        ->select('receiver_name', 'building_or_street', 
          'barangay','city_or_municipality', 'province', 
          'contact_number','distance','preferred_delivery_date',
          'ubehalayajar_qty','ubehalayatub_qty', 'total_payment',
          'order_status' )
          ->whereMonth("created_at", (int)$month)
          ->whereYear("created_at", (int)$year)
          ->where("order_status", "=", "Delivered")
          ->get();
          return response()->json(compact('data'));
      } catch (Exception $e){
        return response()->json($e->getMessage());
      }
    }

    //New

    public function postOrder(Request $request){
      try {
        $data = $request->all();
        // return $data;
        // $order_id = 0;
        // foreach($data as $index) {
        //   $customerDetails = (array)$index['billing'];
        //   // $meta_data = (array)$index['meta_data'];

        //   $order_id = $index['id'];
        //   $name = $customerDetails['first_name']." ".$customerDetails['last_name'];
        //   $contact_number = $customerDetails['phone'];
        //   $email = $customerDetails['email'];
        //   $building_or_street = $customerDetails['address_2'];
        //   $barangay =  $customerDetails['address_1'];
        //   $city_or_municipality =  $customerDetails['city'];
        //   $province =  $customerDetails['postcode'];
        //   $total_payment = $index['total'];
        //   $preferred_delivery_date = $index['preferred_delivery_date'];
        //   $order_status = "On order";
        //   $landmark = $customerDetails['company'];
        //   $payment_method = $index['payment_method'];
        //   $payment_status = $index['status'];

          $post = new OnlineOrders;
          $post->receiver_name = $data['receiver_name'];
          $post->order_id = $data['order_id'];
          $post->contact_number = $data['contact_number'];
          $post->email = $data['email'];
          $post->building_or_street = $data['building_or_street'];
          $post->barangay = $data['barangay'];
          $post->city_or_municipality = $data['city_or_municipality'];
          $post->province = $data['province'];
          $post->total_payment = $data['total_payment'];
          $post->preferred_delivery_date = $data['preferred_delivery_date'];
          $post->order_status = $data['order_status'];
          $post->landmark = $data['landmark'];
          $post->payment_method = $data['payment_method'];
          $post->payment_status = $data['payment_status'];

          $isExist = OnlineOrders::where('order_id', '=', $data['order_id'])->first();
          if ($isExist === null) {
            $post->save();
          }
          return 'success';
        // }
      } catch (Exception $e){
        return response()->json(['error: ' => $e->getMessage()]);
      }
    }

    public function saveOrderDetails(Request $request){
      try {
        $data = $request->all();
        // foreach($data as $index) {
        //   $order_items = (array) $index['line_items'];
        //   foreach($order_items as $order){
        //     $name = $order['name'];

            $productID = DB::table('products')
            ->where('product_name', $data['product_name'])
            ->value('id');

            $customerID = DB::table('online_orders')
            ->where('order_id', $data['order_id'])
            ->value('id');
            
            $post = new OrderDetailsModel;
            $post->order_id = $data['order_id'];
            $post->customer_id = $customerID;
            $post->product_id = $productID;
            $post->order_quantity = $data['order_quantity'];

            $orderIDExist = OrderDetailsModel::where('order_id', '=', $data['order_id'])->first();
            $productIDExist = OrderDetailsModel::where('product_id', '=', $productID)->first();
            if ($orderIDExist === null && $productIDExist === null) {
              $post->save();
            }
            return 'success';
          // }
        // }
      } catch(Exception $e) {
        return response()->json(['error: ' => $e->getMessage()]);
      }
    }

    public function fetchOrders(){
      $order_products = array();
      $data = DB::table('online_orders')
      ->join('order_details', 'order_details.order_id', '=', 'online_orders.order_id')
      ->select('online_orders.*', 'order_details.*')
      ->get();
      $arr = array();
      foreach($data as $key){
        $order_details = DB::table('products')
        ->select('*')
        ->where('id', $key->product_id)
        ->get();

        $arr = (array)$key;
        $arr['line_items'] = $order_details;
        $order_products[] = $arr;
      }
      return $order_products;
    }
   
}