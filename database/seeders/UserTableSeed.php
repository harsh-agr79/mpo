<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTableSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $oldUsers = DB::table('old_customers')->get();

        foreach ($oldUsers as $oldUser) {
            $bal = explode('|', $oldUser->balance);
            DB::table('users')->insert([
                'id' => $oldUser->id,
                'name' => $oldUser->name,
                'email' => $oldUser->email ?? $oldUser->id. '@gmail.com',
                'password' => $oldUser->password,
                'userid' => $oldUser->user_id,
                'contact' => $oldUser->contact,
                'type' => $oldUser->type ?? 'retailer',
                'disabled' => $oldUser->disabled ?? 0,
                'shop_name' => $oldUser->shopname,
                'address' => $oldUser->address,
                'area' => $oldUser->area,
                'state' => $oldUser->state,
                'district' => $oldUser->district,
                'marketer_id' => $oldUser->refid,
                'open_balance' => $oldUser->openbalance ?? 0,
                'balance' => $bal[1] ?? 0,
                'profile_image' => NULL,
                'secondary_contact' => $oldUser->contact2,
                'dob' => $oldUser->DOB,
                'tax_type' => $oldUser->taxtype,
                'tax_no' => $oldUser->taxnum,
                'open_balance_type' => $oldUser->obtype,
                'current_balance_type' => $bal[0] == 'red' ? 'credit' : 'debit',
                'thirdays' => $oldUser->thirdays,
                'fourdays' => $oldUser->fourdays,
                'sixdays' => $oldUser->sixdays,
                'nindays' => $oldUser->nindays,
                'activity' => $oldUser->activity,
                'bill_count' => $oldUser->billcnt,
                'invoice_permission' => $oldUser->invoice_perm,
            ]);
        }
    }
}
