<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
// use Filament\Pages\Actions\Action;
use App\Services\OrderExportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
// use Filament\Actions;
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


class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        return '';
        // Ensure nothing is rendered
    }
    protected function getHeaderWidgets(): array
    {
        return [
            OrderSummary::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            categorySummary::class,
        ];
    }

    protected function getFooterActions(): array
    {
        return [
            Actions\DeleteAction::make(),
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
            Action::make('download_pdf')
                ->label('Download PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->action(function () {
                    $record = $this->record;

                    $record->load(['items.product']);

                    $pdf = Pdf::loadView('pdf.order', ['order' => $record]);

                    return response()->streamDownload(
                        fn() => print ($pdf->output()),
                        'order-' . $record->id . '.pdf'
                    );
                }),
            Action::make('download_png')
                ->label('Download PNG')
                ->icon('heroicon-o-photo')
                ->color('success')
                ->url(fn(Order $record) => route('png.order', $record))
                ->openUrlInNewTab(),
            Action::make('generate_ind_pdf_with_images')
                ->label('PDF with Images')
                ->icon('heroicon-o-document-arrow-down')
                ->color('warning')
                ->action(function ($record) {
                    // Eager load relations manually
                    $record->load(['items.product']);

                    // Use an array of one order to reuse the same Blade template
                    $pdf = Pdf::loadView('pdf.orderImg', ['orders' => collect([$record])]);

                    $filename = 'order-' . $record->orderid . '.pdf';
                    $path = storage_path("app/public/{$filename}");

                    file_put_contents($path, $pdf->output());

                    return response()->download($path)->deleteFileAfterSend(true);
                }),
            Action::make('download_png_with_image')
                ->label('PNG with Product Images')
                ->icon('heroicon-o-photo')
                ->color('warning')
                ->url(fn(Order $record) => route('png.orderImg', $record))
                ->openUrlInNewTab(),
        ];
    }


    // protected function getRedirectUrl(): string
    // {
    //     return $this->getResource()::getUrl('index');
    // }


    public function mount($record): void
    {
        parent::mount($record);

        $user = Auth::user();

        if (
            $user->hasPermissionTo('Order View First') &&
            is_null($this->record->seenby)
        ) {
            $this->record->seenby = $user->id;
            $this->record->save();
        }
    }
}
