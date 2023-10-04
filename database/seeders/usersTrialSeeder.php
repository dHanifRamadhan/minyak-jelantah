<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class usersTrialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = DB::table('admin')->insertGetId([
            'nama' => 'hanifAdmin'
        ]);
        
        DB::table('users')->insert([
            'email' => 'admin@gmial.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'admin_id' => $admin
        ]);

        $pelanggan1 = DB::table('pelanggan')->insertGetId([
            'nama' => 'hanif',
            'no_hp' => '123456789',
            'kelamin' => 'Laki-laki',
            'tanggal_lahir' => Carbon::createFromFormat('Y/m/d', '2004/10/28'),
            'alamat' => 'jl.Lama'
        ]);
        $pelanggan2 = DB::table('pelanggan')->insertGetId([
            'nama' => 'heru',
            'no_hp' => '123456789',
            'kelamin' => 'Laki-laki',
            'tanggal_lahir' => Carbon::createFromFormat('Y/m/d', '2004/10/10'),
            'alamat' => 'jl.Baru'
        ]);

        DB::table('users')->insert([
            'email' => 'pelanggan1@gmail.com',
            'password' => Hash::make('pelanggan123'),
            'role' => 'pelanggan',
            'pelanggan_id' => $pelanggan1
        ]);
        DB::table('users')->insert([
            'email' => 'pelanggan2@gmail.com',
            'password' => Hash::make('pelanggan123'),
            'role' => 'pelanggan',
            'pelanggan_id' => $pelanggan2
        ]);

        $petugas1 = DB::table('petugas')->insertGetId([
            'nama' => 'hanif',
            'no_hp' => '123456789',
            'alamat' => 'jl.Lama',
            'kelamin' => 'Laki-laki'
        ]);
        $petugas2 = DB::table('petugas')->insertGetId([
            'nama' => 'hanif',
            'no_hp' => '123456789',
            'alamat' => 'jl.Baru',
            'kelamin' => 'Laki-laki'
        ]);

        DB::table('users')->insert([
            'email' => 'petugas1@gmail.com',
            'password' => Hash::make('petugas123'),
            'role' => 'petugas',
            'petugas_id' => $petugas1
        ]);
        DB::table('users')->insert([
            'email' => 'petugas2@gmail.com',
            'password' => Hash::make('petugas123'),
            'role' => 'petugas',
            'petugas_id' => $petugas2
        ]);
    }
}
