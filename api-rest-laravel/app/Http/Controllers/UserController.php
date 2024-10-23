<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use App\Helpers\JwtAuth;

class UserController extends Controller
{

    public function register(REQUEST $request){

        //recoger los datos del usuario por post 

        $json = $request->input('json',null);
        $params = json_decode($json); // transforma json en objeto
        $params_array = json_decode($json, true); // transforma json en array

        if(!empty($params) && !empty($params_array)){
                //limpiar datos

            $params_array = array_map('trim', $params_array);

            //validar datos

            $validate = \Validator::make($params_array,[
                'name'      => 'required|alpha',
                'surname'   => 'required|alpha',
                'email'     => 'required|email|unique:users',   //comprobar existe ya (duplicado)
                'password'  => 'required'
            ]);

            if($validate->fails()){

                $data = array (
                    'status'    => 'error',
                    'code'      => 404,
                    'message'   => 'El usuario no se ha creado correctamente',
                    'errors'    => $validate->errors()
                );
            }else{

                //validacion correcta

                //cifrar la password
                $pwd = hash('sha256', $params->password);

                //crear el usuario
                $user =  new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role ='ROLE_USER';

                //guardaar el usuario
                $user->save();

                $data = array (
                    'status'    => 'success',
                    'code'      =>  200,
                    'message'   => 'El usuario se ha creado correctamente',
                    'user'      =>  $user
                );
            }

            
        }else{

            $data = array (
                'status'    => 'error',
                'code'      => 404,
                'message'   => 'Los datos no se han enviado correctamente'
            );
        }
       
        return response()->json($data, $data['code']);
    }


    public function login(REQUEST $request){

        $jwtAuth = new \JwtAuth();


        //RECIBIR DATOS POR POST 
        $json = $request->input('json',null);
        $params = json_decode($json);
        $params_array = json_decode($json,true);

        $validate = \Validator::make($params_array,[
            'email'      => 'required|email',
            'password'   => 'required'
        ]);

        if($validate->fails()){
        //VALIDAR ESOS DATOS
            $signup = array (
                'status'    => 'error',
                'code'      => 404,
                'message'   => 'El usuario no se ha encontrado',
                'errors'    => $validate->errors()
            );
        }else{

            // CIFRAR LA PASSWORD
            $pwd = hash('sha256', $params->password);
            //DEVOLVER TOKEN O DATOS
            $signup = $jwtAuth->signup($params->email,$pwd);
            if(!empty($params->gettoken)){
                $signup = $jwtAuth->signup($params->email,$pwd,true);
            }
        }

        return response()->json($signup,200);

    }



    public function update(REQUEST $request){
        /**
         * COMPROBAR QUE EL USUARIO ESTA IDENTIFICADO
         */
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

         //RECOGER DATOS POR POST

         $json = $request->input('json', null);
         $params_array = json_decode($json, true);

        if($checkToken && !empty($params_array)){

            //ACTUALIZAR AL USUARIO

            //SACAR USER IDENTIFICADO

            $user = $jwtAuth->checkToken($token, true);

            //VALIDAR LOS DATOS

            $validate = \Validator::make($params_array, [
                'name'      => 'required|alpha',
                'surname'   => 'required|alpha',
                'email'     => 'required|email|unique:users,'.$user->sub
            ]);

            //QUITAR CAMPOS QUE NO QUIERO ACTUALIZAR

            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);

            //ACTUALIZAR DATOS EN LA BBDD

            $user_update = User::where('id', $user->sub)->update($params_array);

            //DEVOLVER UN ARRAY

            $data = array (
                'status'    => 'success',
                'code'      => 200,
                'user'      => $user,
                'changes'   => $params_array
            );

        }else{
            
            $data = array (
                'status'    => 'error',
                'code'      => 400,
                'message'   => 'El usuario no se esta identificado'
            );
        }

        return response()->json($data,$data['code']);
    }

    public function upload(Request $request){

        /**
         * RECOGER DATOS DE LA PETICION
         */
        $image = $request->file('file0');

        /**
         * Validacion de imagen
         */
        $validate = \Validator::make($request->all(), [
            'file0'     => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);



         /**
          * GUARDAR LA IMAGEN
          */

        if(!$image || $validate->fails()){
            $data =  array (
                'status'    => 'error',
                'code'      => 400,
                'message'   => 'Error al subir imagen'
            );
        }else{
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));

            $data = array(
                'code'      =>  200,
                'status'    => 'success',
                'image'     =>  $image_name
            );
        }
        return response()->json($data, $data['code']);
    }

    public function getImage($filename) {

        $isset = \Storage::disk('users')->exists($filename);

        if($isset){
            $file = \Storage::disk('users')->get($filename);
            return new Response($file,200);
        }else{
            $data =  array (
                'status'    => 'error',
                'code'      => 400,
                'message'   => 'La imagen no existe'
            );
            return response()->json($data, $data['code']);
        }

        
    }

    public function detail($id){
        $user = User::find($id);

        if(is_object($user)){
            $data = array(
                'code'      =>  200,
                'status'    => 'success',
                'user'      =>  $user
            );
        }   else {
            $data =  array (
                'status'    => 'error',
                'code'      => 400,
                'message'   => 'El usuario no existe'
            );
        }

        return response()->json($data,$data['code']);
    }
}

?>