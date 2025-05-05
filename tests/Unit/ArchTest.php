<?php

use App\Services\BankParsers\Concretes\AcmeBankParser;
use App\Services\BankParsers\Concretes\FoodicsBankParser;
use App\Services\BankParsers\Contracts\BankParserContract;
use App\Services\BankParsers\Contracts\MapLineToTransactionContract;

test('globals')
    ->expect(['dd', 'dump', 'die'])
    ->not->toBeUsed();

test('foodics bank parser')
    ->expect(FoodicsBankParser::class)
    ->toBeClass()
    ->toImplement(BankParserContract::class)
    ->toImplement(MapLineToTransactionContract::class);

test('acme bank parser')
    ->expect(AcmeBankParser::class)
    ->toBeClass()
    ->toImplement(BankParserContract::class)
    ->toImplement(MapLineToTransactionContract::class);

test('traits')
    ->expect('App\Traits')
    ->toBeTraits();

test('models')
    ->expect('App\Models')
    ->toBeClasses()
    ->toExtend('Illuminate\Database\Eloquent\Model');

test('enums')
    ->expect('App\Enums')
    ->toBeEnums();
