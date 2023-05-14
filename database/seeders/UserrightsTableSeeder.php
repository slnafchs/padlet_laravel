<?php

namespace Database\Seeders;

use App\Models\Userright;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use DateTime;

class UserrightsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userrights = new Userright();
        $userrights->user_id = 1;
        $userrights->padlet_id = 1;
        $userrights->read = true;
        $userrights->edit = true;
        $userrights->delete = true;
        $userrights->save();
    }
}
