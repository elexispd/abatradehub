<?php

namespace App\Filament\Resources\Enquiries\Schemas;

use App\Enums\EnquiryStatus;
use App\Models\Product;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class EnquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer')->columns(2)->columnSpanFull()->schema([
                    TextInput::make('reference')->disabled()->dehydrated(false)->placeholder('Generated on save'),
                    Select::make('status')->options(EnquiryStatus::class)->default(EnquiryStatus::New->value)->required(),
                    TextInput::make('name')->required()->maxLength(255),
                    TextInput::make('phone')->tel()->required(),
                    TextInput::make('email')->email(),
                    TextInput::make('company'),
                    TextInput::make('country'),
                    TextInput::make('city'),
                    Select::make('source')
                        ->options([
                            'catalog' => 'Catalog Product Enquiry',
                            'quote_form' => 'Instant Quote Request',
                            'whatsapp' => 'WhatsApp',
                            'custom_photo' => 'Photo Request',
                            'form' => 'Website form (General)',
                        ])
                        ->default('catalog')
                        ->required(),
                    DateTimePicker::make('contacted_at')->native(false)->placeholder('Not yet contacted'),
                    Textarea::make('message')->rows(3)->columnSpanFull(),
                ]),
                Section::make('Enquired Products & Order Items')
                    ->description('Products attached to this wholesale enquiry. Staff can add more items if the buyer requested additional products during phone or WhatsApp follow-up.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->columns(4)
                            ->defaultItems(0)
                            ->addActionLabel('Add Item to Quote')
                            ->schema([
                                Placeholder::make('product_preview')
                                    ->label('Product Photo & Link')
                                    ->content(function ($get, $record) {
                                        $productId = $get('product_id') ?? $record?->product_id;
                                        if (! $productId) {
                                            return new HtmlString('<span class="text-xs text-gray-400">Select a product to view photo and specs</span>');
                                        }

                                        $product = Product::find($productId);
                                        if (! $product) {
                                            return new HtmlString('<span class="text-xs text-gray-400">Product not found in catalog</span>');
                                        }

                                        $url = $product->getFirstMediaUrl('images', 'thumb') ?: $product->getFirstMediaUrl('images');
                                        $publicUrl = $product->url;

                                        $imgHtml = $url
                                            ? '<img src="'.$url.'" class="w-16 h-16 rounded-md object-cover border border-gray-200 shadow-sm" alt="Product photo" />'
                                            : '<div class="w-16 h-16 rounded-md bg-gray-100 flex items-center justify-center text-xs text-gray-400 border border-gray-200">No photo</div>';

                                        return new HtmlString('
                                            <div class="flex items-center gap-3 p-2 bg-gray-50 rounded-lg border border-gray-100">
                                                '.$imgHtml.'
                                                <div class="text-xs">
                                                    <div class="font-bold text-gray-900">'.$product->name.'</div>
                                                    <div class="text-gray-500">MOQ: '.$product->moq.' pcs | SKU: '.$product->sku.'</div>
                                                    <a href="'.$publicUrl.'" target="_blank" class="text-amber-800 underline font-medium mt-0.5 inline-block hover:text-amber-900">Open product page &rarr;</a>
                                                </div>
                                            </div>
                                        ');
                                    })
                                    ->columnSpan(4),

                                Select::make('product_id')
                                    ->label('Product')
                                    ->options(fn () => Product::query()->orderBy('name')->pluck('name', 'id'))
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        if ($product = Product::find($state)) {
                                            $set('product_name', $product->name);
                                            $set('product_sku', $product->sku);
                                            $set('quantity', $product->moq);
                                        }
                                    })
                                    ->columnSpan(2),
                                TextInput::make('quantity')->numeric()->minValue(1)->required(),
                                TextInput::make('product_sku')->label('SKU'),
                                TextInput::make('product_name')->label('Product name')->required()->columnSpan(2),
                                TextInput::make('note')->label('Buyer notes / variations')->columnSpan(2),
                            ]),
                    ]),
                Section::make('Reference Photos')
                    ->description('Photos submitted by the customer as style references.')
                    ->columnSpanFull()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('reference_photos')
                            ->label('')
                            ->collection('reference_photos')
                            ->multiple()
                            ->image()
                            ->imagePreviewHeight('120')
                            ->panelLayout('grid')
                            ->disabled(),
                    ]),
                Section::make('Staff & Negotiation Notes')
                    ->description('Private internal workspace. Customers and website visitors never see this information.')
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('admin_notes')
                            ->label('Internal staff notes')
                            ->rows(4)
                            ->helperText('Log phone/WhatsApp discussions, supplier confirmations at Ariaria Market, negotiated discounts, payment milestones, or logistics notes.'),
                    ]),
            ]);
    }
}