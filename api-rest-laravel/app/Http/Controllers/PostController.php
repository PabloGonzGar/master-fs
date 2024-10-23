<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Post;
use App\Helpers\JwtAuth;

class PostController extends Controller
{

    /**
     * 
     * CONSTRUCTOR
     */
    public function __construct(){
        $this->middleware('api.auth',['except'  =>  ['index','show',
                                        'getImage','getPostByCategory','getPostByUser']]);
    }

    /**
     * 
     * 
     * INDEX
     * 
     * 
     */

    public function index(){
        $post = Post::all()->load('post');
        return response()->json([
            'code'          =>  200,
            'status'        =>  'success',
            'post'    =>  $post    
        ],200);
    }

    /**
     * 
     * 
     * SHOW
     * 
     * 
     */

    public function show($id){
        $post = Post::find($id)->load('post');

        if(is_object($post)){
            $data = [
                'code'      =>  200,
                'status'    =>  'success',
                'post'  =>  $post
            ];
        }else{
            $data = [
                'code'      =>  404,
                'message'   =>  'no se ha encontrado el post',
                'status'    =>  'error'   
            ];
        }

        return response()->json($data,$data['code']);
    }

    /**
     * 
     * 
     * STORE
     * 
     * 
     */

    public function store(Request $request){
        //RECOGER DATOS POR POST
        
        $json = $request->input('json',null);
        $params = json_decode($json);
        $params_array = json_decode($json,true);

        //VALIDAR LOS DATOS
        if(!empty($params_array)){
            //CONSEGUIR USER IDENTIFICADO

            $user = $this->getIdentity($request);
    

            //VALIDAR DATOS
            $validate = \Validator::make($params_array, [
                'title'      =>  'required',
                'content'    =>  'required',
                'category_id'=>  'required',
                'image'      =>  'required'
            ]);
    
            //GUARDAR POST
    
            if($validate->fails()){
                $data = [
                    'code'      =>  400,
                    'message'   =>  'No se ha guardado el post,faltan datos',
                    'status'    =>  'error'   
                ];
            }else{
                $post = new Post();
                $post -> user_id=$user->sub;
                $post -> category_id=$params->category_id;
                $post -> title=$params->title;
                $post -> content=$params->content;
                $post -> image=$params->image;
                $post->save();

                $data = [
                    'code'      =>  200,
                    'status'    =>  'Success',
                    'post'      =>  $post
                ];
            }
        }else{
            $data = [
                'code'      =>  400,
                'message'   =>  'No has enviado nigun post',
                'status'    =>  'error'   
            ];
        }
        
        
        //DECOLVER RESULTADO
        return response()->json($data,$data['code']);
    }

    /**
     * 
     * 
     * UPDATE
     * 
     * 
     */

    public function update($id,Request $request){

        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        

        $data = [
            'code'      =>  400,
            'message'   =>  'No has enviado ningun post',
            'status'    =>  'error'   
        ];

        if(!empty($params_array)){
            $validate = \Validator::make($params_array, [
                'title'      =>  'required',
                'content'    =>  'required',
                'category_id'=>  'required'
            ]);

            if($validate->fails()){
                $data['errors'] = $validate->errors();
                return response()->json($data,$data['code']);
            }

            unset($params_array['id']);
            unset($params_array['user_id']);
            unset($params_array['created_at']);
            unset($params_array['user']);

            $user = $this->getIdentity($request);
            
            $post = Post::where('id',$id)
                    ->where('user_id',$user->sub)
                    ->first();

            if(!empty($post) && is_object($post)){

                $post -> update($params_array);

                $data = [
                    'code'      =>  200,
                    'status'    =>  'Success',
                    'post'      =>  $post,
                    'changes'   =>  $params_array
                ];
            }
        }

        return response()->json($data,$data['code']);
    }

    /**
     * 
     * 
     * DESTROY
     * 
     * 
     */

    public function destroy($id,Request $request){

        $user = $this->getIdentity($request);

        $post = Post::where('id',$id)
                    ->where('user_id',$user->sub)
                    ->first();

        if($post){
            $post->delete();

            $data = [
                'code'      =>  200,
                'status'    =>  'Success',
                'post'      =>  $post
            ];
        }else{
            $data = [
                'code'      =>  400,
                'message'   =>  'No se ha encontrado dicho post',
                'status'    =>  'error'   
            ];
        }

        return response()->json($data,$data['code']);
    }

    /**
     * 
     * 
     * IDENTITTY
     * 
     * 
     */

    private function getIdentity(Request $request){
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $user = $jwtAuth->checkToken($token,true);

        return $user;
    }

    /**
     * 
     * 
     * UPLOAD
     * 
     * 
     */

    public function upload(Request $request){
        //RECOGER IMAGEN POR PETICION

        $image =  $request->file('file0');

        //VALIDAR IMAGEN

        $validate = \Validator::make($request->all(), [
            'file0'     => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        //GUARDAR LA IMAGEN

        if(!$image || $validate->fails()){
            $data =  array (
                'status'    => 'error',
                'code'      => 400,
                'message'   => 'Error al subir imagen'
            );
        }else{
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('images')->put($image_name, \File::get($image));

            $data = array(
                'code'      =>  200,
                'status'    => 'success',
                'image'     =>  $image_name
            );
        }

        //DEVOLVER DATOS
        return response()->json($data,$data['code']);
    }

    /**
     * 
     * 
     * GET IMAGE
     * 
     * 
     */

    public function getImage($filename) {

        $isset = \Storage::disk('images')->exists($filename);

        if($isset){
            $file = \Storage::disk('images')->get($filename);
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

    /**
     * 
     * 
     * GET POST BY CATEGORY
     * 
     * 
     */

    public function getPostByCategory($id){
        $posts = Post::where('category_id',$id)->get();

        if(is_object($posts)){
            $data = array(
                'code'      =>  200,
                'status'    => 'success',
                'posts'     =>  $posts
            );
        }else {
            $data =  array (
                'status'    => 'error',
                'code'      => 400,
                'message'   => 'Esa categoria no existe'
            );
        }
        return response()->json($data,$data['code']);
    }

    /**
     * 
     * 
     * GET POST BY USER
     * 
     * 
     */

     public function getPostByUser($id){
        $posts = Post::where('user_id',$id)->get();

        if(is_object($posts)){
            $data = array(
                'code'      =>  200,
                'status'    => 'success',
                'posts'     =>  $posts
            );
        }else {
            $data =  array (
                'status'    => 'error',
                'code'      => 400,
                'message'   => 'Esa categoria no existe'
            );
        }
        return response()->json($data,$data['code']);
    }

}

