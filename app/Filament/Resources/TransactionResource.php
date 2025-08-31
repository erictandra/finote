<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Transaction';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->default(now()),

                        Forms\Components\Select::make('wallet_id')
                            ->label('Wallet')
                            ->relationship('wallet', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('type')
                            ->options([
                                'in' => 'Income',
                                'out' => 'Expense',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set) {
                                $set('category_id', null);
                            }),

                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->relationship(
                                name: 'category',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query, Forms\Get $get) {
                                    $type = $get('type');
                                    if ($type) {
                                        return $query->where('type', $type);
                                    }
                                    return $query;
                                }
                            )
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0)
                            ->disabled(function (Forms\Get $get) {
                                $status = $get('status');
                                return $status && $status !== 'pending';
                            }),

                        Forms\Components\Hidden::make('status')
                            ->default('pending'),

                        Forms\Components\Textarea::make('remark')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('wallet.name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'success' => 'in',
                        'danger' => 'out',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'in' => 'Income',
                        'out' => 'Expense',
                        default => $state,
                    }),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),

                Tables\Columns\TextColumn::make('formatted_amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('amount', $direction);
                    }),

                Tables\Columns\TextColumn::make('remark')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter by Date Range
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('date_from')
                            ->label('From Date'),
                        DatePicker::make('date_to')
                            ->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),

                // Filter by Wallet
                SelectFilter::make('wallet_id')
                    ->label('Wallet')
                    ->relationship('wallet', 'name')
                    ->searchable()
                    ->preload(),

                // Filter by Category (with dynamic options based on type)
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                // Filter by Amount
                Filter::make('amount_filter')
                    ->form([
                        Forms\Components\Select::make('operation')
                            ->options([
                                'none' => 'None',
                                '>' => 'Greater than (>)',
                                '<' => 'Less than (<)',
                                '>=' => 'Greater than or equal (>=)',
                                '<=' => 'Less than or equal (<=)',
                            ])
                            ->default('none')
                            ->live(),
                        Forms\Components\TextInput::make('value')
                            ->numeric()
                            ->visible(fn(Forms\Get $get): bool => $get('operation') !== 'none')
                            ->required(fn(Forms\Get $get): bool => $get('operation') !== 'none'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!isset($data['operation']) || $data['operation'] === 'none' || !isset($data['value'])) {
                            return $query;
                        }

                        return match ($data['operation']) {
                            '>' => $query->where('amount', '>', $data['value']),
                            '<' => $query->where('amount', '<', $data['value']),
                            '>=' => $query->where('amount', '>=', $data['value']),
                            '<=' => $query->where('amount', '<=', $data['value']),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                // Approve Action
                Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(Transaction $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Transaction')
                    ->modalDescription('Are you sure you want to approve this transaction? This will update the wallet balance.')
                    ->action(function (Transaction $record) {
                        DB::transaction(function () use ($record) {
                            $record->update(['status' => 'approved']);

                            $wallet = $record->wallet;
                            if ($record->type === 'in') {
                                $wallet->increment('balance', $record->amount);
                            } else {
                                $wallet->decrement('balance', $record->amount);
                            }
                        });

                        Notification::make()
                            ->success()
                            ->title('Transaction Approved')
                            ->body('Transaction has been approved and wallet balance updated.')
                            ->send();
                    }),

                // Reject Action
                Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(Transaction $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Reject Transaction')
                    ->modalDescription('Are you sure you want to reject this transaction?')
                    ->action(function (Transaction $record) {
                        $record->update(['status' => 'rejected']);

                        Notification::make()
                            ->success()
                            ->title('Transaction Rejected')
                            ->body('Transaction has been rejected.')
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make()
                    ->before(function (Transaction $record) {
                        // Adjust wallet balance if deleting approved transaction
                        if ($record->status === 'approved') {
                            $wallet = $record->wallet;
                            if ($record->type === 'in') {
                                $wallet->decrement('balance', $record->amount);
                            } else {
                                $wallet->increment('balance', $record->amount);
                            }
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            // Adjust wallet balances for approved transactions being deleted
                            foreach ($records as $record) {
                                if ($record->status === 'approved') {
                                    $wallet = $record->wallet;
                                    if ($record->type === 'in') {
                                        $wallet->decrement('balance', $record->amount);
                                    } else {
                                        $wallet->increment('balance', $record->amount);
                                    }
                                }
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
            RelationManagers\TransactionProofsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'view' => Pages\ViewTransaction::route('/{record}'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->whereNull('deleted_at');
    }
}
