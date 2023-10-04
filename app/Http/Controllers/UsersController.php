<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class usersController extends Controller
{
    public function Index()
    {
        $users = DB::table('users')
            ->where('role', 'pelanggan')
            ->orWhere('role', 'petugas')
            ->get();

        if (empty($users)) {
            return response()->json([
                'code'      => 400,
                'message'   => 'Data users tidak ada'
            ]);
        }

        $response = [];

        foreach ($users as $user) {
            switch ($user->role) {
                case 'pelanggan':
                    $response['pelanggan'] = $this->Pelanggan();
                    break;
                case 'petugas':
                    $response['petugas'] = $this->Petugas();
                    break;
            }
        }

        return response()->json([
            'code'      => 200,
            'message'   => 'Data users berhasil terbaca dengan baik',
            'data'      => $response
        ], 200);
    }

    public function Pelanggan()
    {
        $users =  DB::table('users')
            ->select('users.id', 'users.pelanggan_id', 'pelanggan.nama', 'users.email', 'pelanggan.no_hp', 'pelanggan.alamat', 'pelanggan.kelamin', 'pelanggan.nama_file', 'users.role', 'users.created_at', 'users.updated_at')
            ->join('pelanggan', 'pelanggan.id', '=', 'users.pelanggan_id')
            ->get();

        return $users;
    }

    public function Petugas()
    {
        $users = DB::table('users')
            ->select('users.id', 'users.petugas_id', 'petugas.nama', 'users.email', 'petugas.no_hp', 'petugas.alamat', 'petugas.kelamin', 'petugas.nama_file', 'users.role', 'users.created_at', 'users.updated_at')
            ->join('petugas', 'petugas.id', '=', 'users.petugas_id')
            ->get();

        return $users;
    }

    public function Image($request) {
        if ($request->hasFile('image')) {
            $this->validate($request, [
                'image' => 'image|mimes:jpeg,jpg,png'
            ], [
                'image.image' => 'Image harus berupa gambar',
                'image.mimes' => 'Image harus jpeg, jpg, png'
            ]);

            $image = $request->file('image');
            $imageName = DB::table('users')->count() . '-' . $image->getClientOriginalName();
            $image->storeAs('images/profile', $imageName);
        } else {
            $imageName = null;
        }
        return $imageName;
    }

    public function create(Request $request, $role)
    {
        $roleUser = ($role == 'pelanggan') ? 'pelanggan' : 'petugas';

        if (!$roleUser) {
            return response()->json([
                'code' => 404,
                'message' => 'Role tidak dapat di ubah'
            ], 404);
        }

        $this->validate($request, [
            'nama' => 'required|string|max:255',
            'email' => 'required|email:dns|unique:users,email',
            'password' => 'required|min:6',
            'no_hp' => 'required|numeric|digits_betwen:10,13',
            'alamat' => 'required',
            'kelamin' => 'required|in:Laki-laki,Perempuan'
        ], [
            'required' => 'Data input harus disi',
            'nama.string' => 'Nama harus berupa teks',
            'nama.max' => 'Nama tidak boleh lebih dari 255',
            'email.email' => 'Email harus memiiki domain name system (DNS)',
            'email.unique' => 'Email sudah ada',
            'password.min' => 'Password minimal harus ada 6 karakter',
            'no_hp.numeric' => 'Nomor Handphone harus berisikan nomer',
            'no_hp.digits_between' => 'Nomor Handphone minimal harus 10 dan maksimal harus 13',
            'kelamin.in' => 'Kelamin hanya ada Laki-laki dan Perempuan'
        ]);
        
        $imageName = $this->Image($request);
        
        $data = [
            'nama' => $request->nama,
            'no_hp' => $request->no_hp,
            'alamat' => $request->alamat,
            'kelamin' => $request->kelamin,
            'nama_file' => $imageName
        ];
        
        if ($role == 'pelanggan') {
            $this->validate($request, [
                'tanggal_lahir' => 'required|date'
            ], [
                'tanggal_lahir.date' => 'Tanggal lahir harus bernilai tanggal'
            ]);
            $data['tanggal_lahir'] = $request->tanggal_lahir;
        }

        $userId = DB::table($roleUser)->insertGetId($data);

        DB::table('users')->insert([
            'email' => $request->email,
            'password' => $request->password,
            'role' => $roleUser,
            $roleUser.'_id' => $userId
        ]);

        return response()->json([
            'code'      => 200,
            'message'   => 'Data ' . $roleUser .  ' berhasil di buat'
        ], 200);
    }

    public function show($id) {
        $users = DB::table('users')
            ->where('id', $id)
            ->where(function($query){
                $query->where('role', 'pelanggan')
                    ->orWhere('role', 'petugas');
            })
            ->first();

        if (!$users) {
            return response()->json([
                'code' => 404,
                'message' => 'Data user tidak ada'
            ], 404);
        }

        $data = ($users->role === 'pelanggan') ? $this->Pelanggan()->where('id', $id)->first() : $this->Petugas()->where('id', $id)->first();
        return response()->json([
            'code' => 200,
            'message' => 'Data user berhasil di temukan',
            'data' => $data
        ], 200);
    }

    public function update(Request $request, $id) {

        $this->validate($request, [
            'nama' => 'required|string|max:255',
            'email' => 'required|email:dns|unique:users,email',
            'no_hp' => 'required|numeric|digits_between:10,13',
            'alamat' => 'required',
            'kelamin' => 'required|in:Laki-laki,Perempuan',
        ], [
            'required' => 'Data input harus disi',
            'nama.string' => 'Nama harus berupa teks',
            'nama.max' => 'Nama tidak boleh lebih dari 255',
            'email.email' => 'Email harus memiiki domain name system (DNS)',
            'email.unique' => 'Email sudah ada',
            'no_hp.numeric' => 'Nomor Handphone harus berisikan nomer',
            'no_hp.digits_between' => 'Nomo(r Handphone minimal harus 10 dan maksimal harus 13',
            'kelamin.in' => 'Kelamin hanya ada Laki-laki dan Perempuan',
        ]);
        
        if ($request->passowrd != null) {
            $this->validate($request, [
                'password' => 'required|min:6'
            ], [
                'password.min' => 'Password minimal harus ada 6 karakter'
            ]);
        }

        $users = DB::table('users')
        ->where('id', $id)
        ->where(function($query){
            $query->where('role', 'pelanggan')
                ->orWhere('role', 'petugas');
        })
        ->first();

        if (!$users) {
            return response()->json([
                'code' => 404,
                'message' => 'Data user tidak ada'
            ], 404);
        }

        $validasi = ($users->role === 'pelanggan') ? $users->pelanggan_id : $users->petugas_id;
        $roleUser = ($users->role === 'pelanggan') ? 'pelanggan' : 'petugas';
        $validasiImage = ($users->role === 'pelanggan') ? $this->Pelanggan()->where('id', $id)->first() : $this->Petugas()->where('id', $id)->first();
        $imageName = $validasiImage->nama_file;

        if ($request->image != null) {
            $imagePath = 'images/profile/'.$validasiImage->nama_file;
            if (Storage::exists($imagePath)) {
                Storage::delete($imagePath);
                $imageName =  $this->Image($request);
                return response()->json([
                    'code'      => 200,
                    'message'   => 'File gambar berhasil di ubah'
                ],200);
            }
            return response()->json([
                'code'      => 400,
                'message'   => 'Path file tidak berhasil di temukan'
            ], 400);
        }


        $data = [
            'nama' => $request->nama,
            'no_hp' => $request->no_hp,
            'alamat' => $request->alamat,
            'kelamin' => $request->kelamin,
            'nama_file' => $imageName
        ];
        
        if ($roleUser == 'pelanggan') {
            $this->validate($request, [
                'tanggal_lahir' => 'required|date'
            ], [
                'tanggal_lahir.date' => 'Tanggal lahir harus bernilai tanggal'
            ]);
            $data['tanggal_lahir'] = $request->tanggal_lahir;
        }

        DB::table($roleUser)->where('id', $validasi)->update($data);
        DB::table('users')
            ->where('id', $id)
            ->update([
                'email' => $request->email,
                'password' => $request->password
            ]);

        return response()->json([
            'code'      => 200,
            'message'   => 'Data '. $roleUser .' berhasil di perbaharui'
        ], 200);
    }
}
