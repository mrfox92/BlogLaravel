<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;
use App\Category;

class CategoryController extends Controller
{
    public function __construct() {
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }
    //  Listar categorias
    public function index() {
        $categories = Category::all();

        return response()->json([
            'status'        =>  'success',
            'code'          =>  200,
            'categories'    =>  $categories
        ]);
    }
    //  mostrar categoria
    public function show($id) {

        $category = Category::find($id);

        if (is_object($category) && !empty($category)) {
            $res = array(
                'status'    =>  'success',
                'code'      =>  200,
                'categoria' =>  $category
            );
        } else {
            $res = array(
                'status'    =>  'error',
                'code'      =>  404,
                'message'   =>  'La categoria no existe'
            );
        }

        return response()->json($res, $res['code']);
    }
    //  crear categoria
    public function store(Request $request) {
        //  recoger datos por post
        $json = $request->input('json', null);
        //  Obtener array con la data
        $params_array = json_decode($json, true);
        
        if (!empty($params_array)) {
            //  validar los datos
            $validate = Validator::make($params_array, [
                'name'  =>  'required'
            ]);
            //  manejar errores en validaciones
            if ($validate->fails()) {
                $res = array(
                    'status'    =>  'error',
                    'code'      =>  400,
                    'message'   =>  'No se ha podido crear categoria',
                    'errors'    =>  $validate->errors()
                );
            } else {
                
                //  Guardar categoria
                $category = new Category();
                $category->name = $params_array['name'];
                $category->save();
                
                //  Devolver respuesta
                $res = array(
                    'status'    =>  'success',
                    'code'      =>  201,
                    'categoria' =>  $category
                );
            }
        } else {
            $res = array(
                'status'    =>  'error',
                'code'      =>  400,
                'message'   =>  'No se ha enviado ninguna categoria'
            );
        }

        return response()->json($res, $res['code']);
    }
    //  actualizar categoria
    public function update($id, Request $request) {
        //  Recoger datos por put
        $json = $request->input('json', null);
        //  Obtenemos la data en un array
        $params_array = json_decode($json, true);
        if (!empty($params_array)) {
            //  Validar los datos
            $validate = Validator::make($params_array,[
                'name'  =>  'required'
            ]);

            //  evaluamos si hay errores en la validacion
            if ($validate->fails()) {
                $res = array(
                    'status'    =>  'error',
                    'code'      =>  400,
                    'message'   =>  'Error al actualizar categoria',
                    'errors'    =>  $validate->errors()
                );
            } else {
                //  Quitar lo que no se desea actualizar
                unset($params_array['created_at']);
                unset($params_array['updated_at']);
                //  Actualizar el registro( categoria )
                $category_updated = Category::where('id', $id)->update($params_array);
                //  Obtener categoria de la base de datos actualizada
                $category = Category::find($id);
                //  preparamos la data de la respuesta
                $res = array(
                    'status'    =>  'success',
                    'code'      =>  200,
                    'categoria' =>  $category
                );
                
            }

        } else {
            $res = array(
                'status'    =>  'error',
                'code'      =>  400,
                'message'   =>  'No se ha enviado ninguna categoria para actualizar'
            );
        }
        //  devolver respuesta y data

        return response()->json($res, $res['code']);
    }
    //  eliminar categoria
    public function destroy($id) {

    }
}
