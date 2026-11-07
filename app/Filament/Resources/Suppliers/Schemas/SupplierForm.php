<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Business')->columns(2)->columnSpanFull()->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),
                    TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true),
                    TextInput::make('market_location')->placeholder('Ariaria International Market'),
                    TextInput::make('address'),
                    Toggle::make('is_verified')->label('Verified by AbaTradeHub'),
                    Toggle::make('is_active')->label('Active')->default(true),
                ]),
                Section::make('Contact (internal only)')->columns(2)->columnSpanFull()->schema([
                    TextInput::make('contact_person'),
                    TextInput::make('phone')->tel(),
                    TextInput::make('whatsapp')->tel()->helperText('International format without +, e.g. 2348031234567'),
                    TextInput::make('email')->email(),
                    Textarea::make('notes')->rows(3)->columnSpanFull()->helperText('Never shown on the public site.'),
                ]),
            ]);
    }
}