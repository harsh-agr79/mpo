<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class UpdateCustomerBalances extends Command {
    protected $signature = 'customers:update-balances';
    protected $description = 'Update balances for all customers';

    /**
    * Execute the console command.
    */

    public function handle() {
        $users = User::with( [ 'orders', 'payments', 'salesReturns', 'expenses' ] )->get();
        $now = now();

        foreach ( $users as $user ) {
            // 1. Opening balance adjustment
            $opening = $user->open_balance ?? 0;
            $openingType = $user->open_balance_type ?? 'debit';
            $adjustedOpening = $openingType === 'debit' ? $opening : -$opening;

            // 2. Totals from transactions
            $totalSales = $user->orders()
            ->where( 'mainstatus', 'approved' )
            ->whereNull( 'deleted_at' )
            ->where( 'save', 0 )
            ->sum( 'net_total' );

            $totalPayments = $user->payments()
            ->whereNull( 'deleted_at' )
            ->sum( 'amount' );

            $totalReturns = $user->salesReturns()->sum( 'net_total' );
            $totalExpenses = $user->expenses()->sum( 'amount' );

            $totalDebit = $totalSales + $totalExpenses;
            $totalCredit = $totalPayments + $totalReturns;

            $hasNoTransactions = (
                $totalSales == 0 &&
                $totalPayments == 0 &&
                $totalReturns == 0 &&
                $totalExpenses == 0 &&
                ( $opening == 0 || is_null( $opening ) )
            );

            if ( $hasNoTransactions ) {
                $user->balance = 0;
                $user->current_balance_type = 'debit';
            } else {
                $rawBalance = $adjustedOpening + $totalDebit - $totalCredit;

                $user->balance = abs( $rawBalance );
                $user->current_balance_type = $rawBalance >= 0 ? 'debit' : 'credit';
            }

            // 3. Bill count and activity
            $billCount = $user->orders()
            ->where( 'mainstatus', 'approved' )
            ->whereNull( 'deleted_at' )
            ->where( 'save', 0 )
            ->whereDate( 'date', '>=', now()->subMonths( 6 ) )
            ->count();

            $user->bill_count = $billCount;
            $user->activity = match ( true ) {
                $billCount > 3 => 'regular',
                $billCount >= 1 => 'occasional',
                default => 'inactive',
            }
            ;

            // 4. Aging logic: thirdays, fourdays, sixdays, nindays
            $user->thirdays = 0;
            $user->fourdays = 0;
            $user->sixdays = 0;
            $user->nindays = 0;

            if ( $user->current_balance_type === 'debit' && $user->balance > 0 ) {
                $remaining = $user->balance;

                $daysMap = [
                    'thirdays' => 30,
                    'fourdays' => 45,
                    'sixdays'  => 60,
                    'nindays'  => 90,
                ];

                foreach ( $daysMap as $field => $days ) {
                    $fromDate = $now->copy()->subDays( $days );
                    $sales = $user->orders()
                    ->where( 'mainstatus', 'approved' )
                    ->whereNull( 'deleted_at' )
                    ->where( 'save', 0 )
                    ->whereDate( 'date', '>=', $fromDate )
                    ->sum( 'net_total' );

                    $remainingBalance = $remaining - $sales;
                    $user->$field = $remainingBalance > 0 ? $remainingBalance : 0;

                    if ( $remainingBalance <= 0 ) {
                        // No further aging buckets needed
                        foreach ( array_keys( $daysMap ) as $skipField ) {
                            if ( !isset( $user->$skipField ) ) {
                                $user->$skipField = 0;
                            }
                        }
                        break;
                    }
                }
            }

            $user->save();
        }

        $this->info( 'User balances, activity, bill count, and aging buckets updated.' );
    }

}
