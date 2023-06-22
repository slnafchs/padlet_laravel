<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use DateTime;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    //UserTable mit Daten befüllt -> diese werden in die Datenbank gesendet (db seed)

    public function run()
    {

        //fillables von Model "User"
        $user = new \App\Models\User;
        $user->firstname = "Selina";
        $user->lastname = "Fuchs";
        $user->email = "fuchs@gmail.com";
        $user->password = bcrypt("Fuchs12345!");
        $user->image = "https://i.pinimg.com/236x/b3/30/16/b330168ba0ef3b1f4b86200b111d913b.jpg";
        $user->created_at = new DateTime;
        $user->updated_at = new DateTime;
        $user->save();

        $user2 = new \App\Models\User;
        $user2->firstname = "David";
        $user2->lastname = "Reiter";
        $user2->email = "reiter@gmail.com";
        $user2->password = bcrypt("Reiter12345!");
        $user2->image = "https://i.pinimg.com/236x/b3/30/16/b330168ba0ef3b1f4b86200b111d913b.jpg";
        $user2->created_at = new DateTime;
        $user2->updated_at = new DateTime;
        $user2->save();

    }
}
