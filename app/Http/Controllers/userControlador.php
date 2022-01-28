<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class userControlador extends Controller
{
    //Punto Crear usuarios en la base de datos.
    public function create(Request $req)
    {
        $answer = ['status' => 1, "msg" => ""];
        $data = $req->getContent();
        $data = json_decode($data);
        $Email_pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";

        $Pass_pattern = "/^\S*(?=\S{6,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/";


        $user = new User();

        try {

            $user->NombreUsuario = $data->NombreUsuario;
            if (preg_match($Email_pattern, $data->email)) {
                $user->email = $data->email;
            } else {
                $answer['error'] = 'The Email is wrong';
            }

            if (preg_match($Pass_pattern, $data->Contrasena)) {
                $user->Contrasena = Hash::make($data->Contrasena );
                
            } else {
                $answer['password error'] = 'The password is not secure';
            }

            $user->roles = $data->roles;
            $user->save();
            $answer['msg'] = 'User save with id:' . $user->id;
        } catch (\Exception $e) {
            $answer['msg'] = $e->getMessage();
            $answer['status'] = 0;
        }
        return response()->json($answer);
    }

    //Punto 2
    public function login(Request $req)
    {
        $answer = ['status' => 1, 'msg' => ''];

        if ($req->has('NombreUsuario')) {
            $users = User::where('NombreUsuario', $req->input('NombreUsuario'))->first();
        } else {
            $answer['info'] = 'Introduce the email for continue';
        }
        if ($users) {
            if (Hash::check($req->input('Contrasena'), $users->Contrasena)) {
                try {
                    do {
                        $token = Hash::make(now() . $users->id);

                        $users->api_token = $token;

                        $users->save();

                        $exit = User::where('NombreUsuario', $req->input('NombreUsuario'))->value('api_token');
                    } while (!$exit);

                    $answer['msg'] = "Sesion start code: " . $users->api_token;
                } catch (\Exception $e) {
                    $answer['msg'] = $e->getMessage();
                    $answer['status'] = 0;
                }
            } else {
                $answer['Error'] = 'Password incorrect';
            }
        } else {
            $answer['Error'] = 'doesn`t exist users with this email';
        }

        return response()->json($answer);
    }

    //Punto 3 - Recuperar contraseña
    public function recoveryPassword(Request $req) {

        $answer = ['status' => 1, 'msg' => ''];

        $email = $req->input('email');

        $password_pattern = '/^\S*(?=\S{6,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/';

        try {

            if($req->has('email')) {

                $user = User::where('email', $email)->first();

                if ($user) {

                    do {
                        $newPass = Str::random(6);
                    } while(!preg_match($password_pattern, $newPass));

                    $user->contrasena = Hash::make($newPass);
                    $user->save();

                    $answer['contrasena'] = "Your new password: ".$newPass;

            } else {
                $answer['msg'] = "Please enter an email to continue";
            }
            }

        } catch (\Exception $e){
            $answer['status'] = 0;
            $answer['msg'] = "An error has occurred: ".$e->getMessage();
        }

        return response()->json($answer);

    }

    

}
