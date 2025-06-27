<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrdersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = DB::table('products')->pluck('id', 'prod_unique_id');
        $adminMap = DB::table('admins')->pluck('id', 'name');
        $oldOrders = DB::table('old_orders')->get();
        $groupedOrders = $oldOrders->groupBy('orderid');
        $userMap = DB::table('users')->pluck('id', 'userid'); // maps userid => id


        foreach ($groupedOrders as $orderid => $items) {
            $first = $items->first();
            $orderExists = DB::table('orders')->where('orderid', $orderid)->exists();

            // Insert order row
            if (!$orderExists) {
                DB::table('orders')->insert([
                    'date' => $first->created_at ?? now(),
                    'user_id' => $userMap[$first->userid] ?? 6,
                    'orderid' => $orderid,
                    'cusuni_id' => $first->cusuni_id,
                    'mainstatus' => $first->mainstatus,
                    'discount' => $first->discount ?? 0,
                    'total' => $items->sum(fn($i) => $i->price * intval($i->quantity)),
                    'net_total' => $items->sum(fn($i) => $i->price * intval($i->quantity)) - (($first->discount ?? 0) / 100 * $items->sum(fn($i) => $i->price * intval($i->quantity))),
                    'save' => $first->save == '1' ? 1 : 0,
                    'clnstatus' => $first->clnstatus,
                    'clntime' => $first->clntime,
                    'seenby' => $adminMap[$first->seenby] ?? null,
                    'delivered_at' => $first->delivery_date,
                    'recieved_at' => $first->recieveddate,
                    'nepmonth' => $first->nepmonth,
                    'nepyear' => $first->nepyear,
                    'othersname' => $first->othersname,
                    'cartoons' => $first->cartoons,
                    'transport' => $first->transport,
                    'user_remarks' => $first->userremarks,
                    'deleted_at' => $first->deleted_at,
                    'created_at' => $first->created_at,
                    'updated_at' => $first->updated_at,
                ]);
            }

            // Insert each order item
            foreach ($items as $old) {
                $productId = $products[$old->produni_id] ?? null;

                DB::table('order_items')->insert([
                    'orderid' => $orderid,
                    'product_id' => $productId,
                    'price' => $old->price,
                    'quantity' => intval($old->quantity),
                    'approvedquantity' => $old->approvedquantity,
                    'status' => $old->status ?? 'pending',
                    'offer' => $old->offer,
                    'actualprice' => $old->price,
                    'created_at' => $old->created_at,
                    'updated_at' => $old->updated_at,
                ]);

                // Insert remarks if present
                if (!empty($old->remarks)) {
                    DB::table('order_remarks')->insert([
                        'orderid' => $orderid,
                        'remark' => $old->remarks,
                        'remarks_by' => $adminMap[$old->seenby] ?? null,
                        'created_at' => $old->created_at,
                        'updated_at' => $old->updated_at,
                    ]);
                }
            }
        }
    }
}
