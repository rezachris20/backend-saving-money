<?php

namespace App\Http\Controllers\API;

use App\Actions\Fortify\PasswordValidationRules;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\FamilyPortofolio;
use App\Models\User;
use App\Models\UserFamily;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use PasswordValidationRules;

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required','string','max:255'],
                'email' => ['required','string','email','max:255','unique:users'],
                'password' => $this->passwordRules()
            ]);

            // Cek Jika Status 1 = Suami
            if($request->status == 1){

                User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'address' => $request->address,
                    'houseNumber' => $request->houseNumber,
                    'phoneNumber' => $request->phoneNumber,
                    'password' => Hash::make($request->password)
                ]);
    
                $user = User::where('email', $request->email)->first();
                
                $uniqid = uniqid();

                Family::create([
                    'name' => $request->family_name,
                    'code'  => $uniqid
                ]);

                $family = Family::where('code', $uniqid)->first();

                UserFamily::create([
                    'user_id' => $user->id,
                    'family_id' => $family->id,
                    'core_status_id' => $request->status
                ]);

                FamilyPortofolio::create([
                    'family_id' => $family->id,
                    'portofolio_id' => 1,
                    'target' => 0,
                    'is_active' => 1,
                    'is_achievement' => 0
                ]);
                $familyId = $family->id;

                $codeFamily = Family::find($familyId)->code;
            } else {
                $kode = Family::where('code', $request->code_familiy)->first();
                
                if($kode){
                    User::create([
                        'name' => $request->name,
                        'email' => $request->email,
                        'address' => $request->address,
                        'houseNumber' => $request->houseNumber,
                        'phoneNumber' => $request->phoneNumber,
                        'password' => Hash::make($request->password)
                    ]);
        
                    $user = User::where('email', $request->email)->first();

                    UserFamily::create([
                        'user_id' => $user->id,
                        'family_id' => $kode->id,
                        'core_status_id' => $request->status
                    ]);
                } else {
                    return ResponseFormatter::error([
                        'message' => 'Something went wrong',
                        'error' => 'Kode tidak ditemukan',
                    ], 'Authentication Failed', 500);
                }
                $familyId = $kode->id;
                $codeFamily = Family::find($familyId)->code;
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type'    => 'Bearer',
                'user' => $user,
                'family_id' => $familyId,
                'code_family' => $codeFamily
            ]);
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error
            ], 'Authentication Failed', 500);
        }
    }

    public function login(Request $request)
    {
        try{
            $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);

            // Cek credential
            $credentials = request(['email','password']);
            if(!Auth::attempt($credentials)){
                return ResponseFormatter::error([
                    'message' => 'Unauthorized',
                ], 'Authentication Failed', 500);
            }

            // Cek hash password
            $user = User::where('email', $request->email)->first();
            if (!Hash::check($request->password, $user->password, [])) {
                throw new \Exception('Invalid Credentials');
            }

            $familyId = UserFamily::where('user_id',$user->id)->first()->family_id;
            $codeFamily = Family::find($familyId)->code;
            // Jika suskes
            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user,
                'family_id' => $familyId,
                'code_family' => $codeFamily
            ], 'Authenticated');
        }catch(Exception $e){
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 'Authentication Failed', 500);

        }
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();

        return ResponseFormatter::success($token, 'Token Revoked');
    }

    public function fetch(Request $request)
    {
        return ResponseFormatter::success($request->user(),'Data Profile Berhasil di ambil');
    }

    public function updateProfile(Request $request)
    {
        $data = $request->all();

        $user = Auth::user();
        $user->update($data);

        return ResponseFormatter::success($user,'Profile Updated');
    }

    public function uploadPhoto(Request $request)
    {
        $validaor = Validator::make($request->all(),[
            'file'  =>  'required|image|max:2048'
        ]);

        if($validaor->fails())
        {
            return ResponseFormatter::error([
                'error' => $validaor->errors()
            ], 'Update photo fails', 401);
        }

        if($request->file('file'))
        {
            $file = $request->file->store('assets/user', 'public');

            // Save URL
            $user = Auth::user();
            $user->profile_photo_path = $file;
            $user->update();

            return ResponseFormatter::success([$file], 'File successfully uploaded');
        }
    }

    public function generateKodeFamily(Request $request, $id)
    {
        $family = Family::find($id);
        $family['code'] = uniqid();
        $family->save();

        return ResponseFormatter::success($family, 'Kode Barhasil di update');
    }
}
