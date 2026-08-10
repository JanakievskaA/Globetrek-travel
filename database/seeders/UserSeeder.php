<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /** Staff accounts — the first is the demo admin login. */
    private const STAFF = [
        ['name' => 'Admin', 'email' => 'admin@globetrek.test', 'role' => UserRole::Admin, 'country' => 'United Kingdom'],
        ['name' => 'Manager', 'email' => 'manager@globetrek.test', 'role' => UserRole::Manager, 'country' => 'Sweden'],
    ];

    /** Customers, reused by the review and booking seeders. */
    public const CUSTOMERS = [
        // The demo customer login. The name stays a real one because it is
        // published as a review author on the homepage.
        ['name' => 'Kathryn Murphy', 'email' => 'customer@globetrek.test', 'country' => 'United States'],
        ['name' => 'Devon Lane', 'email' => 'devon.lane@example.com', 'country' => 'Canada'],
        ['name' => 'Hana Kobayashi', 'email' => 'hana.kobayashi@example.com', 'country' => 'Japan'],
        ['name' => 'Marco Ferretti', 'email' => 'marco.ferretti@example.com', 'country' => 'Italy'],
        ['name' => 'Sofia Almeida', 'email' => 'sofia.almeida@example.com', 'country' => 'Portugal'],
        ['name' => 'Liam O’Connell', 'email' => 'liam.oconnell@example.com', 'country' => 'Ireland'],
        ['name' => 'Yasmin Haddad', 'email' => 'yasmin.haddad@example.com', 'country' => 'United Arab Emirates'],
        ['name' => 'Nils Andersen', 'email' => 'nils.andersen@example.com', 'country' => 'Norway'],
        ['name' => 'Chloé Dubois', 'email' => 'chloe.dubois@example.com', 'country' => 'France'],
        ['name' => 'Ravi Deshmukh', 'email' => 'ravi.deshmukh@example.com', 'country' => 'India'],
        ['name' => 'Emma Whitfield', 'email' => 'emma.whitfield@example.com', 'country' => 'Australia'],
        ['name' => 'Jonas Weber', 'email' => 'jonas.weber@example.com', 'country' => 'Germany'],
        ['name' => 'Beatriz Costa', 'email' => 'beatriz.costa@example.com', 'country' => 'Brazil'],
        ['name' => 'Aiden Clarke', 'email' => 'aiden.clarke@example.com', 'country' => 'New Zealand'],
        ['name' => 'Lucía Navarro', 'email' => 'lucia.navarro@example.com', 'country' => 'Spain'],
        ['name' => 'Thabo Nkosi', 'email' => 'thabo.nkosi@example.com', 'country' => 'South Africa'],
        ['name' => 'Ingrid Bakker', 'email' => 'ingrid.bakker@example.com', 'country' => 'Netherlands'],
        ['name' => 'Omar Farouk', 'email' => 'omar.farouk@example.com', 'country' => 'Egypt'],
    ];

    public function run(): void
    {
        foreach (self::STAFF as $index => $member) {
            User::updateOrCreate(['email' => $member['email']], [
                'name' => $member['name'],
                'role' => $member['role'],
                'country' => $member['country'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'phone' => '+44 20 7946 '.str_pad((string) (100 + $index), 4, '0', STR_PAD_LEFT),
                'avatar' => 'assets/images/teams/user-0'.($index + 1).'.jpg',
                'status' => 'active',
            ]);
        }

        foreach (self::CUSTOMERS as $index => $customer) {
            User::updateOrCreate(['email' => $customer['email']], [
                'name' => $customer['name'],
                'role' => UserRole::Customer,
                'country' => $customer['country'],
                'password' => Hash::make('password'),
                'email_verified_at' => now()->subDays(random_int(20, 700)),
                'phone' => '+1 555 0'.str_pad((string) (100 + $index), 3, '0', STR_PAD_LEFT),
                'avatar' => 'assets/images/teams/user-'.str_pad((string) (($index % 12) + 1), 2, '0', STR_PAD_LEFT).'.jpg',
                'status' => $index % 9 === 8 ? 'suspended' : 'active',
                'created_at' => now()->subDays(random_int(20, 700)),
            ]);
        }
    }
}
