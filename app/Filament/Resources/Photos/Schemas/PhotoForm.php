<?php

namespace App\Filament\Resources\Photos\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PhotoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->maxLength(255)
                    ->required(fn (?string $operation): bool => $operation === 'edit')
                    ->hiddenOn('create'),
                FileUpload::make('url')
                    ->label('Photos')
                    ->image()
                    ->multiple(fn (?string $operation): bool => $operation === 'create')
                    ->acceptedFileTypes(['image/jpeg', 'image/png'])
                    ->required()
                    ->directory('photos')
                    ->disk('public')
                    ->storeFileNamesIn('uploaded_file_names'),
                Textarea::make('description')
                    ->maxLength(65535),
                Select::make('type')
                    ->options([
                        'Passport' => 'Passport',
                        'Landscape' => 'Landscape',
                    ])->default('Passport'),
            ]);
    }
}
