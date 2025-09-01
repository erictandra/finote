<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Actions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use App\Models\Wallet;
use App\Models\Category;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class TransactionReport extends Page implements HasForms
{
    use InteractsWithForms;
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Transaction Report';
    protected static ?string $title = 'Transaction';
    protected static ?string $navigationGroup = 'Reports';

    protected static string $view = 'filament.pages.transaction-report';

    public ?array $data = [];
    public ?string $reportUrl = null;

    public function mount(): void
    {
        $this->form->fill([
            'start_date' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'end_date' => Carbon::now()->format('Y-m-d'),
            'wallet_id' => null,
            'type' => null,
            'category_id' => null,
            'status' => 'approved',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('start_date')
                    ->label('Start Date')
                    ->required()
                    ->default(Carbon::now()->startOfMonth())
                    ->maxDate(fn(callable $get) => $get('end_date')),

                DatePicker::make('end_date')
                    ->label('End Date')
                    ->required()
                    ->default(Carbon::now())
                    ->minDate(fn(callable $get) => $get('start_date')),

                Select::make('wallet_id')
                    ->label('Wallet')
                    ->placeholder('All Wallets')
                    ->options(Wallet::pluck('name', 'id'))
                    ->searchable(),

                Select::make('type')
                    ->label('Transaction Type')
                    ->placeholder('All Types')
                    ->options([
                        'in' => 'Income',
                        'out' => 'Expense',
                    ])
                    ->reactive()
                    ->afterStateUpdated(fn(callable $set) => $set('category_id', null)),

                Select::make('category_id')
                    ->label('Category')
                    ->placeholder('All Categories')
                    ->options(function (callable $get) {
                        $type = $get('type');
                        if (!$type) {
                            return Category::pluck('name', 'id');
                        }
                        return Category::where('type', $type)->pluck('name', 'id');
                    })
                    ->searchable()
                    ->reactive(),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'all' => 'All Status',
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('approved')
                    ->required(),

                Actions::make([
                    Action::make('preview')
                        ->label('Preview Report')
                        ->icon('heroicon-o-eye')
                        ->color('primary')
                        ->action('generateReport'),

                    Action::make('export')
                        ->label('Export PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action('exportReport'),
                ])
            ])
            ->statePath('data')
            ->columns(3);
    }

    public function generateReport(): void
    {
        $this->validate();

        $params = http_build_query(array_filter([
            'start_date' => $this->data['start_date'],
            'end_date' => $this->data['end_date'],
            'wallet_id' => $this->data['wallet_id'],
            'type' => $this->data['type'],
            'category_id' => $this->data['category_id'],
            'status' => $this->data['status'],
        ]));

        $this->reportUrl = route('reports.transactions') . '?' . $params;

        Notification::make()
            ->title('Report Generated')
            ->success()
            ->send();
    }

    public function exportReport(): void
    {
        $this->validate();

        $params = http_build_query(array_filter([
            'start_date' => $this->data['start_date'],
            'end_date' => $this->data['end_date'],
            'wallet_id' => $this->data['wallet_id'],
            'type' => $this->data['type'],
            'category_id' => $this->data['category_id'],
            'status' => $this->data['status'],
            'export' => 'pdf'
        ]));

        $exportUrl = route('reports.transactions') . '?' . $params;

        $this->js("window.open('$exportUrl', '_blank')");
    }

    public function getFormActions(): array
    {
        return [];
    }
}
