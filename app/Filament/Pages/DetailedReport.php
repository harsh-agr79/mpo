<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\User;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Carbon;
use Filament\Forms\Form;
use Filament\Forms\Components\{Grid, Select};

class DetailedReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationGroup = 'Analytics';
    protected static string $view = 'filament.pages.detailed-report';

    public ?int $customerId = null;
    public ?int $startMonth = null;
    public ?int $endMonth = null;
    public ?int $startYear = null;
    public ?int $endYear = null;

    // For view-related display
    public $customers;
    public $customer;

    public function getTitle(): string
    {
        return ''; // Disable default title rendering
    }
    public function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(4)->schema([
                Select::make('startMonth')
                    ->label('Start Month')
                    ->required()
                    ->options([1=>1, 2=>2, 3=>3, 4=>4, 5=>5, 6=>6, 7=>7, 8=>8, 9=>9, 10=>10, 11=>11, 12=>12])
                    ->live(),

                Select::make('startYear')
                    ->label('Start Year')
                    ->required()
                    ->options([2077 => 2077, 2078 => 2078, 2079 => 2079, 2080 => 2080, 2081 => 2081, 2082 => 2082, 2083 => 2083, 2084 => 2084])
                    ->live(),

                Select::make('endMonth')
                    ->label('End Month')
                    ->required()
                    ->options([1=>1, 2=>2, 3=>3, 4=>4, 5=>5, 6=>6, 7=>7, 8=>8, 9=>9, 10=>10, 11=>11, 12=>12])
                    ->live(),

                Select::make('endYear')
                    ->label('End Year')
                    ->required()
                    ->options([2077 => 2077, 2078 => 2078, 2079 => 2079, 2080 => 2080, 2081 => 2081, 2082 => 2082, 2083 => 2083, 2084 => 2084])
                    ->live(),

                Select::make('customerId')
                    ->label('Select Customer')
                    ->options(User::pluck('name', 'id'))
                    ->searchable()
                    ->nullable()
                    ->live(),
            ]),
        ]);
    }
}
