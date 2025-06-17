<?php

namespace App\Filament\Pages;

use App\Models\Task;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Builder;
use Relaticle\Flowforge\Filament\Pages\KanbanBoardPage;
use Filament\Actions\Action;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;

class TasksBoardBoardPage extends KanbanBoardPage
{
    protected static ?string $navigationIcon = 'heroicon-o-view-columns';
    protected static ?string $navigationLabel = 'Tasks';
    protected static ?string $title = 'Task Board';

   public function getSubject(): Builder
    {
       $user = auth()->user();

        if ($user->hasPermissionTo('View All Tasks')) {
            return Task::query(); // No restrictions
        }

        return Task::query()
            ->where(function ($query) use ($user) {
                $query->where('assigned_by', $user->id)
                    ->orWhereJsonContains('assigned_to', $user->id);
            });
        }

    public function mount(): void
    {
        $this
            ->titleField('title')
            ->orderField('order_column')
            ->columnField('status')
            ->columns([
                'todo' => 'To Do',
                // 'started' => 'Started',
                'in_progress' => 'In Progress',
                'completed' => 'Completed',
            ])
            ->columnColors([
                'todo' => 'blue',
                // 'started' => 'purple',
                'in_progress' => 'yellow',
                'completed' => 'green',
            ])
            ->titleField('title')
 
        // Optional configuration
        ->descriptionField('description')
        ->cardLabel('Task')
        ->pluralCardLabel('Tasks')
        ->cardAttributes([
            'due_date' => 'Due Date',
            'assigned_admin_names' => 'Assigned To',
            'assignedBy.name' => 'Assigned By',
            'priority' => 'Priority'
        ])
        ->cardAttributeColors([
            'due_date' => 'red',
            'assigned_admin_names' => 'yellow',
            'assignedBy.name' => 'green',
            'priority' => 'purple'
        ])
        ->cardAttributeIcons([
            'due_date' => 'heroicon-o-calendar',
            'assigned_admin_names' => 'heroicon-o-user',
            'assignedBy.name' => 'heroicon-o-user',
        ]);
        //  ->initialCardsCount(15)
        // ->cardsIncrement(10);
    }

    public function createAction(Action $action): Action
    {
        return $action
            ->iconButton()
            ->icon('heroicon-o-plus')
            ->modalHeading('Create Task')
            ->modalWidth('xl')
            ->form(function (Forms\Form $form) {
                return $form->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->placeholder('Enter task title')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('description')
                        ->columnSpanFull(),
                    Forms\Components\Hidden::make('assigned_by')
                        ->default(auth()->id())
                        ->dehydrated(),
                     Forms\Components\Select::make('assigned_to')
                    ->label('Assigned To')
                    ->multiple()
                    ->options(
                        Admin::pluck('name', 'id')
                            ->toArray()
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull(),
                     Forms\Components\DatePicker::make('due_date')
                        ->label('Due Date')
                        ->required()
                        ->minDate(now()) // Optional: disallow past dates
                        ->native(false),
                    Forms\Components\Select::make('priority')
                        ->label('Priority')
                        ->options([
                            'low' => 'Low',
                            'medium' => 'Medium',
                            'high' => 'High',
                            'urgent' => 'Urgent',
                        ])
                        ->default('medium')
                        ->required()
                        ->native(false),
                    // Add more form fields as needed
                ]);
            });
    }

     public function editAction(Action $action): Action
    {
        return $action
            ->iconButton()
            ->icon('heroicon-o-plus')
            ->modalHeading('Create Task')
            ->modalWidth('xl')
            ->hidden(fn () => !auth()->user()->hasPermissionTo('Edit Tasks'))
            ->form(function (Forms\Form $form) {
                return $form->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->placeholder('Enter task title')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('description')
                        ->columnSpanFull(),
                    Forms\Components\Hidden::make('assigned_by')
                        ->default(auth()->id())
                        ->dehydrated(),
                     Forms\Components\Select::make('assigned_to')
                    ->label('Assigned To')
                    ->multiple()
                    ->options(
                        Admin::pluck('name', 'id')
                            ->toArray()
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull(),
                     Forms\Components\DatePicker::make('due_date')
                        ->label('Due Date')
                        ->required()
                        ->minDate(now()) // Optional: disallow past dates
                        ->native(false),
                    Forms\Components\Select::make('priority')
                        ->label('Priority')
                        ->options([
                            'low' => 'Low',
                            'medium' => 'Medium',
                            'high' => 'High',
                            'urgent' => 'Urgent',
                        ])
                        ->default('medium')
                        ->required()
                        ->native(false),
                    // Add more form fields as needed
                ]);
            });
    }
}
