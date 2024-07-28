<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = User::orderBy('id', 'DESC')->paginate(5);

        return response()->json([
            'status' => 'success',
            'results' => $data
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users',
            'password' => 'required',
        ]);

        $imageName = null;

        if ($request->image) {
            $imageName = time(). '.' . $request->file('image')->extension();
            $request->image->storeAs('public/images', $imageName);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'image' => $request->image
        ]);

        return response()->json([
            'status' => 'success',
            'result'=> $user
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $decrypt = Crypt::decryptString($id);
        $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email,' . $decrypt,
            'password' => 'required',
        ]);

        try {
            $user = User::findOrFail($decrypt);
            if ($request->image) {
                $imageName = time(). '.' . $request->file('image')->extension();
                $request->image->storeAs('public/images', $imageName);

                $path = storage_path('app/public/images/'. $user->image);
                if (File::exists($path)) {
                    File::delete($path);
                }

                $request->image = $imageName;
            }

            $user->name = $request->name;
            $user->email = $request->email;
            if ($request->password != '') {
                $user->password = Hash::make($request->password);
            }

            $user->update();

            return response()->json(['status' => 'success', 'result' => $user]);
        } catch (DecryptException $e) {
            return response()->json([
                'status'    => 'error',
                'result'    => $e,
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $decryptId = Crypt::decryptString($id);
            $user = User::findOrFail($decryptId);
            $user->delete();

            return response()->json([
                'status'    => 'success',
                'result'    => 'Data berhasil dihapus',
            ]);
        }
        catch(Exception $e){
            return response()->json([
                'status'    => 'error',
                'result'    => $e,
            ]);
        }
    }
}
