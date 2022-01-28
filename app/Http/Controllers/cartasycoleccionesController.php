<?php

namespace App\Http\Controllers;

use App\Models\Carta;
use App\Models\carta_coleccion;
use App\Models\Coleccion;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class cartasycoleccionesController extends Controller
{
    public function registrarCartas(Request $req){
        $answer = ['status' => 1, 'msg' => ''];
        
        $datosCarta = $req -> getContent();

        // Lo escribo en la base de datos
        try {

            // Valido los datos recibidos del json
            $datosCarta = json_decode($datosCarta);

            // Para guardar la colección compruebo que previamente exista, si no existe creo una nueva colección
            $coleccion = DB::table('colecciones')->where('id', $datosCarta -> coleccione_id)->first();
            
            if($coleccion) {

                // Creo una nueva carta con los datos correspondientes
                $carta = new Carta();
                $carta -> nombre = $datosCarta -> nombre;
                $carta -> descripcion = $datosCarta -> descripcion;
                $carta -> save();

                // Registro la carta y la colección en la tabla de cartas_coleccion
                $cartaColeccion = new carta_coleccion();
                $cartaColeccion -> carta_id = $carta->id;
                $cartaColeccion -> coleccione_id = $coleccion->id;
                $cartaColeccion->save();

                $answer['msg'] = "Card registered succesfully!";

            } else {

                $answer['msg'] = "Collection doesn´t exist";
            }
            
        } catch(\Exception $e) {
            $answer['msg'] = $e -> getMessage();
            $answer['status'] = 0;
        }

        return response()-> json($answer);
    }

    public function registrarColecciones(Request $req){
        $answer = ['status' => 1, 'msg' => ''];
        
        $datosColecciones = $req -> getContent();

        // Valido el campo del nombre para que no se pueda repetir y cree dos colecciones iguales.
        $validator = Validator::make(json_decode($datosColecciones, true), [
            'nombre' => 'required|unique:colecciones'
        ]);

        if ($validator->fails()) {
            $answer['msg'] = "Error: " . $validator->errors()->first();
        } else {

            // Lo escribo en la base de datos
            try {

                // Valido los datos recibidos del json
                $datosColecciones = json_decode($datosColecciones);

                // Creo una nueva colección con los datos correspondientes
                $coleccion = new Coleccion();
                $coleccion -> nombre = $datosColecciones -> nombre;
                $coleccion -> simbolo = $datosColecciones -> simbolo;
                $coleccion -> fecha = date('Y-m-d');
                $coleccion -> save();

                $answer['msg'] = "Collection registered correctly";

                // Compruebo si la carta introducida existe
                $cartaExiste = DB::table('cartas')->where('id', $datosColecciones -> carta_id)->first();

                if ($cartaExiste) {

                    // Registro la carta y la colección en la tabla de cartas_coleccion (Añado la carta a la colección)
                    $cartaColeccion = new carta_coleccion();
                    $cartaColeccion -> carta_id = $cartaExiste->id;
                    $cartaColeccion -> coleccione_id = $coleccion->id;
                    $cartaColeccion->save();

                } else {

                    // Creo una nueva carta default con los datos correspondientes ya que la colección no puede estar vacía
                    $carta = new Carta();
                    $carta -> nombre = "Default card";
                    $carta -> descripcion = "Default description";
                    $carta -> save();

                    $answer['msg'] = "Collection and card registered correctly";

                    // Registro la carta y la colección en la tabla de cartas_coleccion
                    $cartaColeccion = new carta_coleccion();
                    $cartaColeccion -> carta_id = $carta->id;
                    $cartaColeccion -> coleccione_id = $coleccion->id;
                    $cartaColeccion->save();

                }

            } catch(\Exception $e) {
                $answer['msg'] = $e -> getMessage();
                $answer['status'] = 0;
            }
        }

        return response()-> json($answer);

    }


    public function subirCartasColecciones(Request $req){

        $answer = ['status' => 1, 'msg' => ''];
       // $user = $req->user;
        $carta = $req->getContent();
        $carta = json_decode($carta);



        $idCarta = $req->input('idCarta');
        $idColeccion =$req->input('idColeccion');

        $cartaAñadir = DB::table('cartas')->where('id', $idCarta)->first();   
        $coleccion = DB::table('colecciones')->where('id', $idColeccion)->first();


        $cartaId = $cartaAñadir -> id;
        $coleccionId = $coleccion-> id;

        $comprobar = DB::table('cartas_colecciones')
                                ->select('carta_id', 'coleccione_id')
                                ->where('carta_id', $cartaId)
                                ->where('coleccione_id',$coleccionId)
                                ->first();
     
    try{
        if($cartaAñadir && $coleccion){
            if($comprobar){
                $answer['msg'] = 'The card is already in this collection';

            }else{
                $cartaColeccion = new carta_coleccion();
                $cartaColeccion -> carta_id = $cartaAñadir->id;
                $cartaColeccion -> coleccione_id = $coleccion->id;
                $cartaColeccion->save();
                $answer['msg'] = 'The card has been added';
            }
        }else{
            $answer['msg'] = 'The card or collection doesn`t exist';
        }

    }catch(\Exception $e) {
        $answer['msg'] = $e -> getMessage();
        $answer['status'] = 0;
    }


return response()-> json($answer);

    }

public function vender(Request $req){
     $answer = ['status' => 1, 'msg' => ''];
     $user = $req->user;
     $datosVenta = $req->getContent();
     $datosVenta = json_decode($datosVenta);

     $validator = Validator::make(json_decode($req -> getContent(),true),[ 
        'numero_cartas'=> 'required',
        'precio' => 'required',
        'idCarta'=> 'required'
     ]);

     $idCartaPostman = $datosVenta -> idCarta;
     $comprobarCarta = DB::table('cartas')-> where('id',$idCartaPostman)->first();

     try{
         if($validator -> fails()){
            $answer['msg'] = 'There is and error' .$validator->errors();
         }else{
             if($comprobarCarta){
                $venta = new Venta();
                $venta -> numero_cartas = $datosVenta -> numero_cartas;
                $venta -> precio = $datosVenta -> precio;
                $venta -> carta_id = $idCartaPostman;
                $venta -> user_id = $user -> id;
                $venta -> save();
                $answer['msg'] = 'The sell has been added';
            
             }
         }

     }catch(\Exception $e) {
        $answer['msg'] = $e -> getMessage();
        $answer['status'] = 0;
    }


return response()-> json($answer);





}


public function busquedaVenta(Request $req){
    $answer = ['status' => 1, 'msg' => ''];
    $busqueda = $req->input('nombre');
    $busquedaResultado = DB::table('cartas')
    ->where('cartas.nombre','like','%'.$busqueda.'%')
    ->select(
        'cartas.id',
        'cartas.nombre'
    )
    ->get();

    try{
        if($busquedaResultado){
            $answer['msg'] = 'This are your results';
            $answer['data'] = $busquedaResultado;
        }

    }catch(\Exception $e) {
        $answer['msg'] = $e -> getMessage();
        $answer['status'] = 0;
    }


return response()-> json($answer);
}


public function busquedaCompra(Request $req){
    $answer = ['status' => 1, 'msg' => ''];
    $busqueda = $req->input('nombre');

    try{
        if($busqueda){
             $answer['msg'] = 'This are your results';
             $resultadoFinal['data'] = DB::table('ventas')
             ->join('cartas','ventas.carta_id', '=', 'cartas.id')
             ->join('users','ventas.user_id','=','users.id')
             ->select('cartas.nombre as Nombre_carta','ventas.numero_cartas','ventas.precio','users.NombreUsuario')
             ->where('cartas.nombre', 'like', '%'.$busqueda.'%')
             ->orderBy('precio','asc')
             ->get();

    $answer['data'] = $resultadoFinal;   

        }else{
            $answer['msg'] = 'You must introduce a word';
        }

    }catch(\Exception $e) {
        $answer['msg'] = $e -> getMessage();
        $answer['status'] = 0;
    }


return response()-> json($answer);
}





}





