<?php

namespace App\Http\Controllers;

use App\Models\BorrowProcess;
use App\Models\Category;
use App\Models\Order;
use App\Models\SubOrder;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatisticsController extends Controller
{
    use ApiResponder;

    public function __construct()
    {
        $this->middleware('auth:api');
    }
    //عرض السنين التي حصل فيها عمليات بيع
    public function years()
    {
        $orderes_time = Order::where('library_id', Auth::id())
            ->orderBy('created_at', 'DESC')
            ->get('created_at');
        $response = [];
        $i = 0;
        //لاضافة السنين دوون تكرار الى المصفوفة
        foreach ($orderes_time as $order) {
            $add = 1;
            for ($j = 0; $j < $i; $j++) {
                if ($response[$j] == $order->created_at->year) $add = 0;
            }
            if ($add == 1) {
                $response[$i] = $order->created_at->year;
                $i += 1;
            }
        }

        return $this->okResponse($response, '');
    }

    //عرض الربح في كل شهر ضمن سنة
    public function years_sales($year)
    {
        //الاوردرات المطلوبة خلال هذه السنة
        $year_orderes = Order::where('orders.library_id', Auth::id())
            ->whereYear('orders.created_at', $year)
            ->get('created_at');

        $months = [];
        $i = 0;

        //لجلب الاشهر التي تم طلب فيها اوردرات بدون تكرار
        foreach ($year_orderes as $order) {
            $month_add = 1;
            for ($j = 0; $j < $i; $j++) {
                if ($months[$j][0] == $order->created_at->month) {
                    $month_add = 0;
                }
            }

            if ($month_add == 1) {
                $months[$i][0] = $order->created_at->month;
                $months[$i][1] = 0;
                $i += 1;
            }
        }

        $total_count = 0;
        for ($j = 0; $j < $i; $j++) {
            $month_new_book_sub_orderes = SubOrder::join('orders', 'orders.id', 'sub_orders.order_id')
                ->join('library_books', 'library_books.id', 'sub_orders.book_id')
                ->where('sub_orders.type', 'book')
                ->where('orders.library_id', Auth::id())
                ->where('library_books.state', '=', 'new')
                ->where('orders.status_id', '!=', 4)
                ->whereYear('sub_orders.created_at', $year)
                ->whereMonth('sub_orders.created_at', $months[$j][0])
                ->get([
                    'sub_orders.quantity',
                    'library_books.id as book_id',
                    'library_books.purchasing_price',
                    'library_books.selling_price',
                ]);

            $month_borrow_book_sub_orderes = BorrowProcess::join('orders', 'orders.id', 'borrow_processes.order_id')
                ->join('library_books', 'library_books.id', 'borrow_processes.book_id')
                ->where('orders.library_id', Auth::id())
                ->where('orders.status_id', '!=', 4)
                ->whereYear('orders.created_at', $year)
                ->whereMonth('orders.created_at', $months[$j][0])
                ->distinct()
                ->get([
                    'library_books.id as book_id',
                    'library_books.purchasing_price',
                    'library_books.selling_price',
                ]);


            $month_offer_sub_orderes = SubOrder::join('orders', 'orders.id', 'sub_orders.order_id')
                ->join('offers', 'offers.id', 'sub_orders.offer_id')
                ->join('book_offers', 'offers.id', 'book_offers.offer_id')
                ->join('library_books', 'library_books.id', 'book_offers.book_id')
                ->where('sub_orders.type', 'offer')
                ->where('orders.library_id', Auth::id())
                ->where('orders.status_id', '!=', 4)
                ->whereYear('sub_orders.created_at', $year)
                ->whereMonth('sub_orders.created_at', $months[$j][0])
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


            $cnt_month_price = 0;

            //حساب الربح من الكتب الجديدة
            foreach ($month_new_book_sub_orderes as $mbsu) {
                $cnt_month_price += ($mbsu->quantity * ($mbsu->selling_price - $mbsu->purchasing_price));
            }

            //  لحساب الربح من الكتب المستعملة يجب ان تكون عمليات الاستعارة قد وفرت سعر شراء الكتاب لتكون قد حققت ربح نحسب كم مرة قع استعير الكتاب قب هذا الشهر
            foreach ($month_borrow_book_sub_orderes as $mbsu) {
                //المربح من الاشهر السابقة

                $book_borrow_cnt = BorrowProcess::where('book_id', $mbsu->book_id)
                    ->whereYear('created_at', '=', $year)
                    ->whereMonth('created_at', '<', $months[$j][0])
                    ->orWhereYear('created_at', '<', $year)
                    ->count();
                $earn = $book_borrow_cnt * $mbsu->selling_price;

                //المربح هاد الشهر
                $book_borrow_cnt = BorrowProcess::where('book_id', $mbsu->book_id)
                    ->whereYear('created_at', '=', $year)
                    ->whereMonth('created_at', '=', $months[$j][0])
                    ->count();

                $earn2 = $book_borrow_cnt * $mbsu->selling_price;

                if ($mbsu->purchasing_price > $earn) {
                    $n = $mbsu->purchasing_price - $earn;
                    $earn2 -= $n;
                }

                // اذا كانت عمليات الاستعارة قد جلبت سعر شراء الكتاب نضيف الربح
                if ($earn2 > 0) {
                    $cnt_month_price += $earn2;
                }
            }

            $last_offer = 0;
            $offer_total_earn = 0;
            foreach ($month_offer_sub_orderes as $mosu) {
                // لحساب الربح من بيع عرض في المكتبة ننقص من سعر العرض سعر شراء الكتب الجديدة وسعر مبيع الكتب المستعملة
                if ($last_offer != $mosu->offer_id) {
                    $cnt_month_price += $offer_total_earn;
                    $offer_total_earn = $mosu->totalPrice;
                }
                if ($mosu->state == 'new') {
                    $offer_total_earn -= ($mosu->quantity * $mosu->purchasing_price);
                } else {
                    $offer_total_earn -= ($mosu->quantity * $mosu->selling_price);
                }

                $last_offer = $mosu->offer_id;
            }

            $cnt_month_price += $offer_total_earn;

            $months[$j][1] = $cnt_month_price;

            //لحساب الربح الكلي في هذه السنة
            $total_count += $cnt_month_price;
        }

        //لحساب الربح بالنسبة المئوية
        for ($j = 0; $j < $i; $j++){
            $months[$j][1] = round( ($months[$j][1] * 100) / $total_count );
        }

        return $this->okResponse($months, '');
    }

    public function category_sales_borrows($year)
    {
        //عدد الكتب المباعة خلال هذه السنة
        $total_count = 0;
        $count = SubOrder::join('orders', 'orders.id', 'sub_orders.order_id')
            ->where('orders.library_id', Auth::id())
            ->where('orders.status_id', '!=', 4)
            ->whereYear('sub_orders.created_at', $year)
            ->get('sub_orders.quantity');

        foreach ($count as $cnt) {
            $total_count += $cnt->quantity;
        }


        $categories = Category::all();

        //لجلب الكاتيغوري التابعين لهذا الكتاب المباع
        $count1 = SubOrder::join('orders', 'orders.id', 'sub_orders.order_id')
            ->join('library_books', 'library_books.id', 'sub_orders.book_id')
            ->join('books', 'books.id', 'library_books.book_id')
            ->join('book_categories', 'book_categories.book_id', 'books.id')
            ->where('books.library_id', Auth::id())
            ->where('orders.status_id', '!=', 4)
            ->where('sub_orders.type', 'book')
            ->whereYear('sub_orders.created_at', $year)
            ->get(['sub_orders.quantity', 'category_id']);

        //لجلب الكاتيغوري الخاصين بكل كتاب ضمن عرض مباع في المكتبة
        $count2 = SubOrder::join('orders', 'orders.id', 'sub_orders.order_id')
            ->join('offers', 'offers.id', 'sub_orders.offer_id')
            ->join('book_offers', 'book_offers.offer_id', 'offers.id')
            ->join('library_books', 'library_books.id', 'book_offers.book_id')
            ->join('books', 'books.id', 'library_books.book_id')
            ->join('book_categories', 'book_categories.book_id', 'books.id')
            ->where('books.library_id', Auth::id())
            ->where('orders.status_id', '!=', 4)
            ->where('sub_orders.type', 'offer')
            ->whereYear('sub_orders.created_at', $year)
            ->get(['sub_orders.quantity', 'category_id']);

        $cnt = 0;
        $response = [];
        $i = 0;
        foreach ($categories as $category) {

            foreach ($count1 as $c) {
                if ($c->category_id == $category->id) {
                    $cnt += $c->quantity;
                }
            }

            foreach ($count2 as $c) {
                if ($c->category_id == $category->id) {
                    $cnt += $c->quantity;
                }
            }

            $response[$i] = [
                'category_name' => $category->name,
                'cnt' => round(($cnt * 100) / $total_count)
            ];
            $cnt = 0;
            $i += 1;
        }
        return $this->okResponse([
            'total_count' => $total_count,
            'response' => $response
        ], '');
    }
}
