<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{

    public function __construct(){
        $this->middleware('api.auth',['except'  =>  ['index','show']]);
    }

    public function index(){
        $categories = Category::all();
        return response()->json([
            'code'          =>  200,
            'status'        =>  'success',
            'categories'    =>  $categories    
        ]);
    }

    public function show($id){
        $category = Category::find($id);

        if(is_object($category)){
            $data = [
                'code'      =>  200,
                'status'    =>  'success',
                'category'  =>  $category
            ];
        }else{
            $data = [
                'code'      =>  404,
                'message'   =>  'no se ha encontrado la categoria',
                'status'    =>  'error'   
            ];
        }

        return response()->json($data,$data['code']);
        
    }

    public function store(Request $request){
        //RECOGER DATOS POR POST
        
        $json = $request->input('json',null);
        $params_array = json_decode($json,true);

        //VALIDAR LOS DATOS
        if(!empty($params_array)){
            $validate = \Validator::make($params_array, [
                'name'      =>  'required'
            ]);
    
            //GUARDAR LA CATEGORIA
    
            if($validate->fails()){
                $data = [
                    'code'      =>  400,
                    'message'   =>  'No se ha guardado la categoria',
                    'status'    =>  'error'   
                ];
            }else{
                $category = new Category();
                $category -> name = $params_array['name'];
                $category->save();
                $data = [
                    'code'      =>  200,
                    'status'    =>  'Success',
                    'category'  =>  $category
                ];
            }
        }else{
            $data = [
                'code'      =>  400,
                'message'   =>  'No has enviado niguna categoria',
                'status'    =>  'error'   
            ];
        }
        
        
        //DECOLVER RESULTADO
        return response()->json($data,$data['code']);
    }

    public function update($id,Request $request){

        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if(!empty($params_array)){
            $validate = \Validator::make($params_array, [
                'name'      =>  'required'
            ]);
            unset($params_array['id']);
            unset($params_array['created_at']);

            $category_update = Category::where('id', $id)->update($params_array);

            $data = [
                'code'      =>  200,
                'status'    =>  'Success',
                'category'  =>  $params_array
            ];

        }else{
            $data = [
                'code'      =>  400,
                'message'   =>  'No has enviado niguna categoria',
                'status'    =>  'error'   
            ];
        }

        return response()->json($data,$data['code']);

    }

}
