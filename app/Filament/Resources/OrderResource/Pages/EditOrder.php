<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\OrderResource\Widgets\OrderSummary;
use App\Filament\Resources\OrderResource\Widgets\categorySummary;
use App\Models\Order;
use App\Filament\Resources\OrderResource\RelationManagers;
use Filament\Forms\Components\{Select, DatePicker, TextInput};
use App\Models\User;
use Filament\Notifications\Notification;


class EditOrder extends EditRecord {
    protected static string $resource = OrderResource::class;

    public function getTitle(): string {
        return '';
        // Ensure nothing is rendered
    }
    protected function getHeaderWidgets(): array {
        return [
            OrderSummary::class,
        ];
    }

    protected function getFooterWidgets(): array {
        return [
            categorySummary::class,
        ];
    }

   

    protected function getFormActions(): array
    {
        return [
            // Default Save / Delete / Cancel already included by Filament

            Action::make('editOrderSpecification')
                ->label('Edit Order Specification')
                ->modalHeading('Update Order Specification')
                ->form([
                    Select::make('user_id')
                        ->label('Customer')
                        ->options(User::query()->pluck('name', 'id'))
                        ->required(),

                    DatePicker::make('date')
                        ->label('Order Date')
                        ->required(),

                    TextInput::make('cartoons'),
                    TextInput::make('transport'),
                ])
                ->fillForm(function () {
                    return $this->record->only(['user_id', 'date', 'cartoons', 'transport']);
                })
                ->action(function (array $data) {
                    $this->record->update($data);

                    Notification::make()
                        ->title('Order Specification Updated')
                        ->success()
                        ->send();
                }),
        ];
    }


    // protected function getRedirectUrl(): string
    // {
    //     return $this->getResource()::getUrl('index');
    // }
    

    public function mount( $record ): void {
        parent::mount( $record );

        $user = Auth::user();

        if (
            $user->hasPermissionTo( 'Order View First' ) &&
            is_null( $this->record->seenby )
        ) {
            $this->record->seenby = $user->id;
            $this->record->save();
        }
    }
}
