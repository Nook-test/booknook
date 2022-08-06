<?php

namespace App\Http\Controllers;

use App\Events\OrderCanceled;
use App\Events\OrderConfirmed;
use App\Events\OrderDelivered;
use App\Events\OrderStored;
use App\Http\Resources\BorrowBookResource;
use App\Http\Resources\OrderResource;
use App\Models\BookOffer;
use App\Models\BorrowProcess;
use App\Models\LibraryBook;
use App\Models\Offer;
use App\Models\Order;
use App\Models\SubOrder;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    //
    use ApiResponder;

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orderes' => 'required|array',
            'library_id' => 'required',
            'address_id' => 'required',
            'totalPrice' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->badRequestResponse(null, $validator->errors()->toJson());
        }

        $order = new Order();
        $order->user_id = Auth::id();
        $order->library_id = $request->library_id;
        $order->address_id = $request->address_id;
        $order->totalPrice = $request->totalPrice;
        $order->status_id = 1; //Processing
        $order->save();

        foreach ($request['orderes'] as $sub_order) {
            if ($sub_order['type'] == 'book') {
                $book_order = new SubOrder();
                $book_order->order_id = $order->id;
                $book_order->book_id = $sub_order['book_id'];
                $book_order->quantity = $sub_order['quantity'];
                $book_order->type = $sub_order['type'];
                $book_order->save();

                $book = LibraryBook::find($sub_order['book_id']);

                (new ShortcutController)->decrease_quantity('book', $book->id, $sub_order['quantity']);

                $cnt = $sub_order['quantity'];
                while ($cnt > 0) {
                    if ($book->state != 'new') {
                        $borrow_process = new BorrowProcess();
                        $borrow_process->book_id = $book->id;
                        $borrow_process->order_id = $order->id;
                        $borrow_process->save();
                    }
                    $cnt -= 1;
                }
            } else {
                $book_order = new SubOrder();
                $book_order->order_id = $order->id;
                $book_order->offer_id = $sub_order['offer_id'];
                $book_order->quantity = $sub_order['quantity'];
                $book_order->type = $sub_order['type'];
                $book_order->save();

                (new ShortcutController)->decrease_quantity('offer', $sub_order['offer_id'], $sub_order['quantity']);

                $offer_books = BookOffer::where('offer_id', $sub_order['offer_id'])->get();

                foreach ($offer_books as $offer_book) {
                    $bk = LibraryBook::find($offer_book->book_id);

                    $cnt = $sub_order['quantity'];
                    while ($cnt > 0) {
                        if ($bk->state != 'new') {
                            $borrow_process = new BorrowProcess();
                            $borrow_process->book_id = $offer_book->book_id;
                            $borrow_process->order_id = $order->id;
                            $borrow_process->save();
                        }
                        $cnt -= 1;
                    }
                }
            }
        }

        event(new OrderStored($order));

        return $this->okResponse(null, 'Your request has been received');
    }

    public function show_an_order(Order $order)
    {
        return $this->okResponse(
            new OrderResource($order),
            'this order details'
        );
    }

    public function confirm_order(Order $order)
    {
        $order->update([
            $order->status_id = 2 //delivey in progress
        ]);

        event(new OrderConfirmed($order));

        return $this->okResponse(null, 'The order is being delivered');
    }

    // عندما يتم اعادة كتاب مستعار
    public function restored(BorrowProcess $book)
    {
        (new ShortcutController)->increase_quantity('book', $book->book_id, 1);
        $book->update([
            $book->returned = 1
        ]);

        return $this->okResponse(null, 'The book has been restored');
    }

    // //عندما يتم تسليم الطلب
    public function order_delivered(Order $order)
    {
        $order->update([
            $order->status_id = 3 //delivired
        ]);

        event(new OrderDelivered($order));

        return $this->okResponse(null, 'The order has been delivered');
    }

    //الكتب المستعارة ضمن مكتبة معينة
    public function borrow_books_in_library()
    {
        $borrow_books = BorrowProcess::join('orders', 'orders.id', 'borrow_processes.order_id')
            ->where('orders.library_id', Auth::id())
            ->orderBy('borrow_processes.returned', 'ASC')
            ->orderBy('borrow_processes.created_at', 'ASC')
            ->get('borrow_processes.*');

        return $this->okResponse(
            BorrowBookResource::collection($borrow_books),
            'All borrowed books that have not yet been returned'
        );
    }

    //طلبات مكتبة معينة
    public function orderes_in_library()
    {
        return $this->okResponse(OrderResource::collection(
            Order::where('library_id', Auth::id())->orderBy('created_at', 'ASC')->get()
        ), 'this library orderes');
    }

    //طلبات شخص معين
    public function user_orderes()
    {
        return $this->okResponse(
            OrderResource::collection(
                Order::where('user_id', Auth::id())->orderBy('created_at', 'DESC')->get()
            ),
            'this customer orderes'
        );
    }

    // عمليات الاستعارة عند شخص معين
    public function user_borrow()
    {
        $borrow_proccesses = BorrowProcess::join('orders', 'orders.id', 'borrow_processes.order_id')
            ->where('orders.user_id', Auth::id())
            ->where('orders.status_id', '3')
            ->orderBy('returned', 'ASC')
            ->orderBy('created_at', 'ASC')
            ->get('borrow_processes.*');
        return $this->okResponse(
            BorrowBookResource::collection($borrow_proccesses),
            'ongoing borrow proccesses'
        );
    }

    // //الغاء طلب
    public function cancel_order(Order $order)
    {
        $sub_orderes = SubOrder::where('order_id', $order->id)->get();
        foreach ($sub_orderes as $sub_order) {
            if ($sub_order->type == 'book') {
                $book = LibraryBook::find($sub_order->book_id);
                (new ShortcutController)->increase_quantity('book', $book->id, $sub_order->quantity);
            } else {
                $offer = Offer::find($sub_order->offer_id);
                (new ShortcutController)->increase_quantity('offer', $offer->id, $sub_order->quantity);
            }

            $borrow_proccesses =  BorrowProcess::where('order_id', $order->id)->get();
            foreach ($borrow_proccesses as $borrow_process) {
                (new ShortcutController)->increase_quantity('book', $borrow_process->book_id, 1);
                $borrow_process->update([
                    $borrow_process->returned = 1
                ]);
            }
        }

        $order->update([
            $order->status_id = 4 //canceled
        ]);

        event(new OrderCanceled($order));

        return $this->okResponse(null, 'The order has been canceled');
    }
}
