<?php

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
        // $this->call(UserSeeder::class);
        //add superadmin
        DB::table('users')->delete();
        //insert some dummy records
        DB::table('users')->insert(array(
        array('nama'=>'Mika', 'password'=>bcrypt('super'),'email'=>'admin@bpsntb.id','username'=>'admin','user_tg'=>'blimika','aktif'=>'1','created_at'=>NOW(),'updated_at'=>NOW()),
        array('nama'=>'Anang Zakaria', 'password'=>bcrypt('anangz'),'email'=>'anangz@bps.go.id','username'=>'anangz','user_tg'=>'AnangZak','aktif'=>'1','created_at'=>NOW(),'updated_at'=>NOW()),
         ));
    }
}
