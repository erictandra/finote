<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            // Approve Action
            Action::make('approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn(): bool => $this->record->status === 'pending')
                ->requiresConfirmation()
                ->modalHeading('Approve Transaction')
                ->modalDescription('Are you sure you want to approve this transaction? This will update the wallet balance.')
                ->action(function () {
                    DB::transaction(function () {
                        $this->record->update(['status' => 'approved']);

                        $wallet = $this->record->wallet;
                        if ($this->record->type === 'in') {
                            $wallet->increment('balance', $this->record->amount);
                        } else {
                            $wallet->decrement('balance', $this->record->amount);
                        }
                    });

                    Notification::make()
                        ->success()
                        ->title('Transaction Approved')
                        ->body('Transaction has been approved and wallet balance updated.')
                        ->send();

                    return redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),

            // Reject Action
            Action::make('reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn(): bool => $this->record->status === 'pending')
                ->requiresConfirmation()
                ->modalHeading('Reject Transaction')
                ->modalDescription('Are you sure you want to reject this transaction?')
                ->action(function () {
                    $this->record->update(['status' => 'rejected']);

                    Notification::make()
                        ->success()
                        ->title('Transaction Rejected')
                        ->body('Transaction has been rejected.')
                        ->send();

                    return redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),
        ];
    }
}
