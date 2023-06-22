<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        //Datenbankseeder aufrufen und Daten in die Datenbank füllen
        //Daten in die "users"-Tabelle einfügen
        $this->call(UsersTableSeeder::class);

        //$this->call(PadletsTableSeeder::class);
        //$this->call(UserrightsTableSeeder::class);
    }
}
