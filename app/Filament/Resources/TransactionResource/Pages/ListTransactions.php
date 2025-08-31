<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Contracts\View\View;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all_status' => Tab::make('All Status')
                ->badge(fn() => $this->getModel()::count()),

            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'pending'))
                ->badge(fn() => $this->getModel()::where('status', 'pending')->count()),

            'approved' => Tab::make('Approved')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'approved'))
                ->badge(fn() => $this->getModel()::where('status', 'approved')->count()),

            'rejected' => Tab::make('Rejected')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'rejected'))
                ->badge(fn() => $this->getModel()::where('status', 'rejected')->count()),

            'all_type' => Tab::make('All Types')
                ->badge(fn() => $this->getModel()::count()),

            'income' => Tab::make('Income')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'in'))
                ->badge(fn() => $this->getModel()::where('type', 'in')->count()),

            'expense' => Tab::make('Expense')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'out'))
                ->badge(fn() => $this->getModel()::where('type', 'out')->count()),
        ];
    }
}
