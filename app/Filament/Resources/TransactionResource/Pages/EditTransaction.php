<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->before(function () {
                    // Adjust wallet balance if deleting approved transaction
                    if ($this->record->status === 'approved') {
                        $wallet = $this->record->wallet;
                        if ($this->record->type === 'in') {
                            $wallet->decrement('balance', $this->record->amount);
                        } else {
                            $wallet->increment('balance', $this->record->amount);
                        }
                    }
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // If transaction was approved and amount/type changed, adjust wallet balance

        $amount = isset($data['amount']) ? $data['amount'] : $this->record->amount;
        $type = isset($data['type']) ? $data['type'] : $this->record->type;
        if (
            $this->record->status === 'approved' &&
            ($this->record->amount != $amount || $this->record->type != $type)
        ) {

            DB::transaction(function () use ($data) {
                $wallet = $this->record->wallet;

                // Reverse the old transaction
                if ($this->record->type === 'in') {
                    $wallet->decrement('balance', $this->record->amount);
                } else {
                    $wallet->increment('balance', $this->record->amount);
                }

                // Apply the new transaction
                if ($data['type'] === 'in') {
                    $wallet->increment('balance', $data['amount']);
                } else {
                    $wallet->decrement('balance', $data['amount']);
                }
            });
        }

        return $data;
    }
}
