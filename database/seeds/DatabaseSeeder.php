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
        array('nama'=>'Chairul Fatikhin Putra', 'password'=>bcrypt('tiqin'),'email'=>'cfatikhinp@bps.go.id','username'=>'tiqin','user_tg'=>'veykin','aktif'=>'1','created_at'=>NOW(),'updated_at'=>NOW()),
        array('nama'=>'Wahyudi Septiawan', 'password'=>bcrypt('wahyudi'),'email'=>'wahyudi.septiawan@bps.go.id','username'=>'wahyudi','user_tg'=>'wahyud15','aktif'=>'1','created_at'=>NOW(),'updated_at'=>NOW()),
        array('nama'=>'Rimassatya Pawestri', 'password'=>bcrypt('rimas'),'email'=>'rimas@bps.go.id','username'=>'rimas','user_tg'=>'rimassatya','aktif'=>'1','created_at'=>NOW(),'updated_at'=>NOW()),
        array('nama'=>'Ahmad Sukri', 'password'=>bcrypt('asukri'),'email'=>'asukri@bps.go.id
        ','username'=>'asukri','user_tg'=>'sukratos','aktif'=>'1','created_at'=>NOW(),'updated_at'=>NOW()),
         ));
    }
}
