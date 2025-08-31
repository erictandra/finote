<?php

namespace App\Filament\Resources\TransactionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionProofsRelationManager extends RelationManager
{
    protected static string $relationship = 'TransactionProofs';

    protected static ?string $title = 'Transaction Proofs';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('file')
                    ->required()
                    ->directory('transaction-proofs')
                    ->acceptedFileTypes(['image/*', 'application/pdf', '.doc', '.docx'])
                    ->maxSize(5120) // 5MB
                    ->downloadable()
                    ->previewable()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\ImageColumn::make('file')
                    ->label('File')
                    ->disk('public')
                    ->visibility('private')
                    ->size(60)
                    ->defaultImageUrl(fn($record) => $record->is_image ? null : asset('images/file-icon.png')),

                Tables\Columns\TextColumn::make('file_extension')
                    ->label('Type')
                    ->badge()
                    ->color(fn(string $state): string => match (strtolower($state)) {
                        'pdf' => 'danger',
                        'doc', 'docx' => 'info',
                        'jpg', 'jpeg', 'png', 'gif', 'webp' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                Tables\Actions\Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn($record) => $record->file_url)
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
