<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function viewProfile(){
        $data = User::findorFail(auth()->user()->id);
        return view('account.profile', compact('data'));
    }


    public function updateProfile(Request $request){
        $validatedData = $request->validate([
            'email' => ['required', 'unique:users,email,' . auth()->user()->id],
            'name' => ['required'],
            'image' => ['nullable', 'mimes:jpg,bmp,png', 'max:2500'],
        ]);

        DB::beginTransaction();
        try {
            $data = User::find(auth()->user()->id);
            $data->email = $request->email;
            $data->name = $request->name;

            if ($file = $request->file('image')) {
                $data->image = ImageHelper::handleUpdatedUploadedImage($file, '/storage', $data, 'image');
            }

            $data->save();

            DB::commit();
            return back()->with('success', __('Profile Updated successfully'));

        } catch (\Throwable $th) {
            DB::rollback();
            Log::error($th->getMessage());
            return back()->with('error', __('An error occured! Try Again'));
        }

    }



    public function showPasswordForm(){
        return view('account.change_password');
    }


    public function updatePassword(Request $request){
        $validatedData = $request->validate([
            'password' => 'required|confirmed|min:8',
        ]);

        DB::beginTransaction();

        try {
            $data = User::find(Auth()->user()->id);
            if(!$data){
                Auth::guard()->logout();
                $request->session()->invalidate();

                $request->session()->regenerateToken();

                return redirect()->route('login');
            }

            $data->password = Hash::make($request->password);
            $data->save();
            DB::commit();

            return back()->with('success', __('Password changed successfully'));
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error($th->getMessage());
            return back()->with('error', 'An Error occured. Try again after sometimes');
        }
    }

}
