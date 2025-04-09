<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Services\EventPublisherService;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        //     'cpf' => '099.999.999-99'
        // ]);
        $commonExists = User::where('email', '=', 'comum@example.com')->get()->first();
        if(!$commonExists){
            $common = User::create([
                'name' => 'Usuário Comum',
                'cpf' => '12345678901',
                'email' => 'comum@example.com',
                'password' => Hash::make('123456'),
                'type' => 'common',
            ]);
        }
        
        $merchantExists = User::where('email', '=', 'lojista@example.com')->get()->first();
        if(!$merchantExists){
            $merchant = User::create([
                'name' => 'Lojista',
                'cpf' => '98765432100',
                'email' => 'lojista@example.com',
                'password' => Hash::make('123456'),
                'type' => 'merchant',
            ]);
        }
        

        $publisher = new EventPublisherService();
            $publisher->publish('users', [
                'id' => $commonExists->id ?? $common->id,
                'name' => 'Usuário Comum',
                'cpf' => '12345678901',
                'email' => 'comum@example.com',
                'password' => Hash::make('123456'),
                'type' => 'common',
            ]
        );

        $publisher->publish('users', 
            [
                'id' => $merchantExists->id ?? $merchant->id,
                'name' => 'Lojista',
                'cpf' => '98765432100',
                'email' => 'lojista@example.com',
                'password' => Hash::make('123456'),
                'type' => 'merchant',
            ]
        );

        // $dep1 = Wallet::create(['user_id' => $commonExists->id ?? $common->id, 'balance' => 100000]);
        // $dep2 = Wallet::create(['user_id' => $merchantExists->id ?? $merchant->id, 'balance' => 1900]);

        // $publisher = new EventPublisherService();
        // $publisher->publish('wallets', 
        //     ['user_id' => $commonExists->id ?? $common->id, 'balance' => 100000],
        //     ['user_id' => $merchantExists->id ?? $merchant->id, 'balance' => 1900]
        // );
        // dd(User::all(), Wallet::all());
    }
}
