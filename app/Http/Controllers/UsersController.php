<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UsersController extends Controller
{
    public function Customer() 
    {
        $select = [
            'users.id AS userId',
            'customer.id AS id',
            'customer.name AS name',
            'users.email AS email',
            'customer.address AS address',
            'customer.phone_num AS phone_num',
            'customer.gender AS gender',
            'customer.birthday AS birthday',
            'customer.agency_status AS agency_status',
            'customer.image_name AS image_name',
            'users.created_at AS created_at',
            'users.updated_at AS updated_at'
        ];
        $users = DB::table('users')->select($select)
                    ->join('customer', 'customer.id', '=', 'users.customer_id')
                    ->get();
        return $users;
    }

    public function Officer()
    {
        $select = [
            'users.id AS userId',
            'officer.id AS id',
            'officer.nip AS NIP',
            'officer.name AS name',
            'users.email AS email',
            'officer.address AS address',
            'officer.phone_num AS phone_num',
            'officer.birthday AS birthday',
            'officer.start_working AS start_working',
            'officer.image_name AS image_name',
            'users.created_at AS created_at',
            'users.updated_at AS updated_at'
        ];
        $users = DB::table('users')->select($select)
                    ->join('officer', 'officer.id', '=', 'users.officer_id')
                    ->get();
        return $users;
    }

    public function Index() 
    {
        $validasiCustomer = 'CASE WHEN users.role = "customer" THEN customer.';
        $validasiOfficer = 'WHEN users.role = "officer" THEN officer.';

        $users = DB::table('users')
                    ->leftJoin('customer', 'customer.id', '=', 'users.customer_id')
                    ->leftJoin('officer', 'officer.id', '=', 'users.officer_id')
                    ->select(
                        'users.id',
                        DB::raw('CASE '.$validasiOfficer.'nip ELSE NULL END AS NIP'),
                        DB::raw($validasiCustomer.'name '.$validasiOfficer.'name ELSE NULL END AS name'),
                        'users.email',
                        DB::raw($validasiCustomer.'address '.$validasiOfficer.'address ELSE NULL END AS address'),
                        DB::raw($validasiCustomer.'phone_num '.$validasiOfficer.'phone_num ELSE NULL END AS phone_num'),
                        DB::raw($validasiCustomer.'gender ELSE NULL END AS gender'),
                        DB::raw($validasiCustomer.'birthday '.$validasiOfficer.'birthday ELSE NULL END AS birthday'),
                        DB::raw($validasiCustomer.'agency_status ELSE NULL END AS agency_status'),
                        DB::raw('CASE '.$validasiOfficer.'start_working ELSE NULL END AS start_working'),
                        DB::raw($validasiCustomer.'image_name '.$validasiOfficer.'image_name ELSE NULL END AS image_name'),
                        'users.created_at',
                        'users.updated_at'
                    )->where(function($query){
                        $query->where('role', 'customer')
                            ->orWhere('role', 'officer');
                    })->get();

        if ($users->isEmpty()) {
            return response()->json([
                'code'=>400,
                'message'=>'Data tidak tersedia'
            ],400);
        }

        return response()->json([
            'code'=>200,
            'message'=>'Data berhasil terbaca',
            'data'=>$users
        ], 200);
    }

    public function Image($request, $role)
    {
        $imageName = null;
        if ($request->hasFile('image')) {
            $this->validate($request,[
                'image' => 'image|mimes:jpeg,jpg,png'
            ]);

            $image = $request->file('image');
            $imageName = date('Y-m-d') . '-' . DB::table('users')->count() . $role . '-' . $image->getClientOriginalName();
            $image->storeAs('images/profile', $imageName);
        }
        return $imageName;
    }

    public function Store(Request $request, $role)
    {
        $role = ($role === 'customer') ? 'customer' : 'officer' ;
        if (!$role) {
            return response()->json([
                'code'=>400,
                'message'=>'Data tidak dapat dibuat!'
            ],400);
        }

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|email:dns|max:255|unique:users,email',
            'passowrd' => 'required|digits_between:6,255',
            'address' => 'required',
            'phone_num' => 'required|numeric|digits_between:10,13',
            'birthday' => 'required|date'
        ]);

        $imageName = $this->Image($request, $role);

        $data = [
            'name'=>$request->name,
            'address'=>$request->address,
            'phone_num'=>$request->phone_num,
            'birthday'=>$request->birthday,
            'image_name'=>$imageName
        ];

        if ($role === 'customer') {
            $this->validate($request, [
                'gender' => 'required|in:Male,Female',
                'agency_status' => 'required|max:255'
            ]);
            $data['gender'] = $request->gender;
            $data['agency_status'] = $request->agency_status;
        } else {
            $this->validate($request, [
                'start_working' => 'required|date'
            ]);
            $data['start_working'] = $request->start_working;
        }

        $users = DB::table($role)->insertGetId($data);
        DB::table('users')->insert([
            'email'=>$request->email,
            'password'=>$request->password,
            'role'=>$role,
            $role.'_id'=>$users
        ]);

        return response()->json([
            'code'=>200,
            'message'=>'Data '.$role.' Berhasil disave ke database'
        ], 200);
    }

    public function Edit($id)
    {
        $users = DB::table('users')
                    ->where('id', $id)
                    ->where(function($query){
                        $query->where('role', 'customer')
                            ->orWhere('role', 'officer');
                    })->fisrt();

        if (!$users) {
            return response()->json([
                'code'=>400,
                'message'=>'Data users tidak'
            ],400);
        }
        
        $data = ($users->role === 'customer') ? $this->Customer()->where('usersId', $id)->first() : $this->Officer()->where('userId', $id)->first();

        return response()->json([
            'code'=>200,
            'message'=>'Data '.$users->role.' berhasil terbaca',
            'data'=>$data
        ], 200);
    }

    public function Update(Request $request, $id)
    {
        $role = ($request->role === 'customer') ? 'customer' : 'officer';
        if (!$role) {
            return response()->json([
                'code'=>400,
                'message'=>'Nilai input role tidak ada'
            ], 400);
        }

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|email:dns|max:255|unique:users,email',
            'passowrd' => 'required|digits_between:6,255',
            'address' => 'required',
            'phone_num' => 'required|numeric|digits_between:10,13',
            'birthday' => 'required|date'
        ]);

        $dataUsers = [
            'email' => $request->email
        ];

        if ($request->passowrd != null) {
            $this->validate($request, [
                'password' => 'required|min:6'
            ]);
            $dataUsers['password'] = $request->password;
        }

        $users = DB::table('users')
                    ->where('id', $id)
                    ->where(function($query){
                        $query->where('role', 'customer')
                            ->orWhere('role', 'officer');
                    })->first();

        $imageName = ($role === 'customer') ? $this->Customer()->select('image_name')->where('id', $users->customer_id)->first() : $this->Officer()->select('image_name')->where('id', $users->officer_id)->first();

        if ($request->image != null) {
            $imagePath = 'images/profile'.$imageName->image_name;
            if (Storage::exists($imagePath)) {
                Storage::delete($imagePath);
                $imageName = $this->Image($request, $role);
            } 
        }

        $data = [
            'name'=>$request->name,
            'address'=>$request->address,
            'phone_num'=>$request->phone_num,
            'birthday'=>$request->birthday,
            'image_name'=>$imageName
        ];

        if ($role === 'customer') {
            $this->validate($request, [
                'gender' => 'required|in:Male,Female',
                'agency_status' => 'required|max:255'
            ]);
            $data['gender'] = $request->gender;
            $data['agency_status'] = $request->agency_status;
        } else {
            $this->validate($request, [
                'start_working' => 'required|date'
            ]);
            $data['start_working'] = $request->start_working;
        }

        DB::table($role)->where('id', $users->$role.'_id')->update($data);
        DB::table('users')->where('id', $id)->update($dataUsers);

        return response()->json([
            'code'=>200,
            'message'=>'Data '.$role.' berhasil diperbaharui'
        ],200);
    }

    public function Destroy($id)
    {
        $users = DB::table('users')
                    ->where('id', $id)
                    ->where(function($query){
                        $query->where('role', 'customer')
                            ->orWhere('role', 'officer');
                    })->first();
        $validasi = ($users->role === 'customer') ? $this->Customer()->where('id', $users->customer_id)->first() : $this->Officer()->where('id', $users->officer_id)->first();
        $role = ($users->role === 'customer') ? 'customer' : 'officer';
        $imagePath = 'images/profile/'.$validasi->image_name;
        if (Storage::exists($imagePath)) {
            Storage::delete($imagePath);
        }
        DB::table($role)->where('id', $users->$role.'_id')->delete();
    }
}