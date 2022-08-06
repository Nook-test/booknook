<?php

namespace App\Http\Controllers;

use App\Models\BorrowProcess;
use App\Models\SubOrder;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EarningController extends Controller
{
    //

    use ApiResponder;

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    //الربح من عمليات البيع
    public function earning_sales()
    {
        //عمليات بيع الكتب الجديدة
        $count1 = SubOrder::join('orders', 'orders.id', 'sub_orders.order_id')
            ->join('library_books', 'library_books.id', 'sub_orders.book_id')
            ->where('sub_orders.type', 'book')
            ->where('orders.library_id', Auth::id())
            ->where('library_books.state', '=', 'new')
            ->where('orders.status_id', '!=', 4)
            ->get([
                'sub_orders.quantity',
                'library_books.id as book_id',
                'library_books.purchasing_price',
                'library_books.selling_price',
            ]);

        //الربح من بيع عرض ضمن مكتبة
        $count2 = SubOrder::join('orders', 'orders.id', 'sub_orders.order_id')
            ->join('offers', 'offers.id', 'sub_orders.offer_id')
            ->join('book_offers', 'offers.id', 'book_offers.offer_id')
            ->join('library_books', 'library_books.id', 'book_offers.book_id')
            ->where('sub_orders.type', 'offer')
            ->where('orders.library_id', Auth::id())
            ->where('orders.status_id', '!=', 4)
            ->orderBy('offers.id')
            ->get([
                'sub_orders.quantity',
                'orders.totalPrice',
                'offers.id as offer_id',
                'library_books.id as book_id',
                'library_books.state',
                'library_books.purchasing_price',
                'library_books.selling_price'
            ]);

        $cnt = 0;
        foreach ($count1 as $c) {
            $cnt += ($c->quantity * ($c->selling_price - $c->purchasing_price));
        }

        $last_offer = 0;
        $offer_total_earn = 0;
        foreach ($count2 as $c) {
            if ($last_offer != $c->offer_id) {
                $cnt += $offer_total_earn;
                $offer_total_earn = $c->totalPrice;
            }
            if ($c->state == 'new') {
                $offer_total_earn -= ($c->quantity * $c->purchasing_price);
            } else {
                $offer_total_earn -= ($c->quantity * $c->selling_price);
            }
            $last_offer = $c->offer_id;
        }

        $cnt += $offer_total_earn;

        return $this->okResponse($cnt, 'earning sales in this library');
    }

    //الربح من عمليات الاستعارة
    public function earning_borrowing()
    {
        $borrow_books = BorrowProcess::join('orders', 'orders.id', 'borrow_processes.order_id')
            ->join('library_books', 'library_books.id', 'borrow_processes.book_id')
            ->where('orders.library_id', Auth::id())
            ->where('orders.status_id', '!=', 4)
            ->distinct()
            ->get([
                'library_books.id as book_id',
                'library_books.purchasing_price',
                'library_books.selling_price',
            ]);

        $cnt = 0;
        foreach ($borrow_books as $borrow_book) {
            $book_borrow_cnt = BorrowProcess::where('book_id', $borrow_book->book_id)
                ->count();
            $earn = $book_borrow_cnt * $borrow_book->selling_price;
            if ($borrow_book->purchasing_price < $earn) {
                $cnt += $earn - $borrow_book->purchasing_price;
            }
        }

        return $this->okResponse($cnt, 'earning borrowing in this library');
    }
}
