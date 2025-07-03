<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Columns\SelectColumn;
// use Filament\Tables\Filters\TextInputFilter;


class CustomerSheet extends Page implements HasTable
{
     use InteractsWithTable;
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static string $view = 'filament.pages.customer-sheet';

    protected static ?string $navigationGroup = 'Analytics';

    public function getTitle(): string
    {
        return '';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query())
            ->paginated(false)
            ->columns([
                // Metrics
                TextColumn::make('name'),

                TextColumn::make('activity')
                    ->label('Activity')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'regular' => 'success',
                        'occasional' => 'info',
                        'inactive' => 'danger'
                    })
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                 TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'retailer' => 'info',
                        'wholesaler' => 'warning',
                        'dealer' => 'danger'
                    })
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                
                TextColumn::make('balance')
                    ->label('Current Balance')
                    ->money('NPR')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->formatStateUsing(function ($state, $record) {
                        $color = $record->current_balance_type === 'debit' ? 'text-red-600' : 'text-green-600';
                        return "<span class='{$color}'>{$state}</span>";
                    })
                    ->html(),

                

                TextColumn::make('thirdays')
                ->money('NPR')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('fourdays')
                ->money('NPR')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('sixdays')
                ->money('NPR')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('nindays')
                ->money('NPR')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('bill_count')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Balances
                TextColumn::make('open_balance')
                ->money('NPR')
                    ->label('Open Balance')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(function ($state, $record) {
                        $color = $record->open_balance_type === 'debit' ? 'text-red-600' : 'text-green-600';
                        return "<span class='{$color}'>{$state}</span>";
                    })
                    ->html(),
                SelectColumn::make('invoice_permission')
                    ->label('Invoice Permission')
                    // ->required()
                    ->options([
                        'Allowed' => 'Allowed',
                        'Payment Alert' => 'Payment Alert',
                        'Block Invoice' => 'Block Invoice',
                    ]),
              

                // Contact Info
                TextColumn::make('contact')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                // Address Info
                TextColumn::make('shop_name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('address')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('area')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('district')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('state')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('area')
                    ->options(
                        User::query()->distinct()->pluck('area', 'area')->filter()->toArray()
                    )
                    ->searchable(),

                SelectFilter::make('district')
                    ->label('District')
                    ->options(
                        DB::table('districts')->pluck('name', 'id')->toArray()
                    )
                    ->searchable(),

                SelectFilter::make('state')
                    ->label('State')
                    ->options(
                        DB::table('provinces')->pluck('name', 'id')->toArray()
                    )
                    ->searchable(),

                // TextInputFilter::make('address')
                //     ->label('Address Contains')
                //     ->placeholder('Enter address fragment')
                //     ->query(fn ($query, $value) => $query->where('address', 'like', "%{$value}%")),
            ])
            ->actions([
                ActionGroup::make([
                    // EditAction::make()->color('primary'),
                    Action::make('Edit')
                        ->label('Edit Customer')
                        ->icon('heroicon-o-pencil-square')
                        ->color('primary')
                        ->url(fn ($record): string => url("/admin/users/{$record->id}/edit")),
                    Action::make('View Statement')
                        ->label('Customer Statement')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->color('info')
                        ->url(fn ($record): string => url("/admin/customer-statement?customerId={$record->id}")),
                ]),
            ])
            ;
    }

}
