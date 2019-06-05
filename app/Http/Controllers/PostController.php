<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Helpers\JWTAuth;
use Validator;
use App\Post;

class PostController extends Controller
{
    public function __construct() {
        $this->middleware('api.auth', ['except' => [
            'index', 'show', 'getImage', 'getPostsByCategory', 'getPostsByUser'
            ]]);
    }
    

    public function index() {
        $posts = Post::all()->load('category');
        return response()->json([
            'status'    =>  'success',
            'code'      =>  200,
            'posts'     =>  $posts,
            'total'     =>  $posts->count()
        ], 200);
    }

    public function getPostsByCategory($id) {

        $posts = Post::where('category_id', $id)->get();
        if (!empty($posts)) {
            $res = array(
                'status'    =>  'success',
                'code'      =>  200,
                'posts'     =>  $posts,
                'total'     =>  $posts->count()
            );
        } else {
            $res = array(
                'status'    =>  'error',
                'code'      =>  404,
                'message'   =>  'No existen entradas para la categoria señalada'
            );
        }

        return response()->json($res, $res['code']);
    }

    public function getPostsByUser($id) {
        $posts = Post::where('user_id', $id)->get();
        if(!empty($posts)) {
            $res = array(
                'status'    =>  'success',
                'code'      =>  200,
                'posts'     =>  $posts,
                'total'     =>  $posts->count()
            );
        } else {
            $res = array(
                'status'    =>  'error',
                'code'      =>  404,
                'message'   =>  'No existen entradas publicadas por este usuario'
            );
        }

        return response()->json($res, $res['code']);
    }

    public function show($id) {
        $post = Post::find($id)->load('category');

        if (is_object($post)) {
            $res = array(
                'status'    =>  'success',
                'code'      =>  200,
                'post'      =>  $post
            );
        } else {
            $res = array(
                'status'    =>  'error',
                'code'      =>  404,
                'message'   =>  'No existe entrada'
            );
        }

        return response()->json($res, $res['code']);
    }

    public function store(Request $request) {
        //  Recoger los datos desde el request
        $json = $request->input('json', null);
        //  decodificar el json y pasar la data a un array
        $post_array = json_decode($json, true);
        //  Validar que la data no venga vacia, malformada o que no venga
        if (!empty($post_array)) {
            //  Obtener el token desde la cabecera Authorization
            $user = $this->getUserAuth($request);
            $validate = Validator::make($post_array, [
                'title'         =>  'required',
                'content'       =>  'required',
                'category_id'   =>  'required',
                'image'         =>  'required'
            ]);
            //  Comprobar errores en las validaciones
            if ($validate->fails()) {
                $res = array(
                    'status'    =>  'error',
                    'code'      =>  400,
                    'message'   =>  'Error al crear entrada, informacion no es valida',
                    'errors'    =>  $validate->errors()
                );
            } else {

                //  Crear objeto post
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id  =   $post_array['category_id'];
                $post->title = $post_array['title'];
                $post->content = $post_array['content'];
                $post->image = $post_array['image'];
                //  Guardar post en DB
                $post->save();
                //  Retornar respuesta y post creado   
                $res = array(
                    'status'    =>  'success',
                    'code'      =>  201,
                    'post'      =>  $post
                );
            }
        } else {
            $res = array(
                'status'    =>  'error',
                'code'      =>  400,
                'message'   =>  'Error al crear entrada, informacion no enviada'
            );
        }

        return response()->json($res, $res['code']);
    }

    public function update($id, Request $request) {
        //  Obtenemos la data json desde put
        $json = $request->input('json', null);
        //  Obtener data del json en un array
        $post_array = json_decode($json, true);
        //  validar que el array contiene la data
        if (!empty($post_array)) {
            //  Validar los datos que contiene el array ( Reglas de validacion)
            $validate = Validator::make($post_array, [
                'title'         =>  'required',
                'content'       =>  'required',
                'category_id'   =>  'required'
            ]);
            //  Evaluar si hay errores en las validaciones
            if ($validate->fails()) {
                $res = array(
                    'status'    =>  'error',
                    'code'      =>  400,
                    'message'   =>  'Error al actualizar entrada, la informacion enviada no es válida',
                    'errors'    =>  $validate->errors()
                );
            } else {

                //  Eliminar lo que no queremos actualizar
                unset($post_array['id']);
                unset($post_array['user_id']);
                unset($post_array['created_at']);
                unset($post_array['updated_at']);
                unset($post_array['deleted_at']);

                //  Obtener usuario autenticado que realiza la actualizacion
                $user = $this->getUserAuth($request);
                //  Actualizar post solo si quien actualiza es el autor del post
                $post_updated = Post::where('id', $id)->where('user_id', $user->sub)->update($post_array);
                //  Comprobamos si se realiza la consulta de forma correcta o no
                if (!$post_updated) {
                    $res = array(
                        'status'    =>  'error',
                        'code'      =>  401,
                        'message'   =>  'Accion no autorizada, el post no es de su autoria'
                    );
                } else {
                    //  Devolver registro actualizado
                    $post = Post::find($id);
                    //  Responder peticion
                    $res = array(
                        'status'    =>  'success',
                        'code'      =>  200,
                        'post'      =>  $post
                    );
                }
            }
        } else {
            $res = array(
                'status'    =>  'error',
                'code'      =>  400,
                'message'   =>  'Error al actualizar entrada, informacion no enviada'
            );
        }

        return response()->json($res, $res['code']);
    }

    public function destroy($id, Request $request) {
        //  Conseguimos usuario autenticado
        $usuario = $this->getUserAuth($request);
        //  Buscar el registro (Permitir borrar unicamente si es el usuario dueño del post)
        $post = Post::where('id', $id)->where('user_id', $usuario->sub)->first();
        if (!empty($post) && is_object($post)) {
            //  Eliminar el registro(borrado lógico para mantener la integridad referencial)
            $post->delete();
            //  Retornar respuesta
            $res = array(
                'status'    =>  'success',
                'code'      =>  200,
                'post'      =>  $post
            );
        } else {
            $res = array(
                'status'    =>  'error',
                'code'      =>  404,
                'message'   =>  'Error al eliminar, entrada no existe o no posee permisos para eliminar entrada'
            );
        }

        return response()->json($res, $res['code']);
    }

    public function upload(Request $request) {
        //  Obtener la imagen
        $image = $request->file('file0');
        //  Validamos que la imagen file0 venga
        if (!empty($image)) {
            //  Validar que el archivo a subir sea una imagen
            $validate = Validator::make($request->all(), [
                'file0'     =>  'required|image|mimes:jpg,jpeg,png,gif'
            ]);
            //  evaluar si hay errores en la validacion
            if ($validate->fails()) {
                $res = array(
                    'status'    =>  'error',
                    'code'      =>  400,
                    'message'   =>  'Error al subir imagen',
                    'errors'    =>  $validate->errors()
                );    
            } else {
                //  personalizamos el nombre de la imagen
                $image_name = time().$image->getClientOriginalName();
                //  Guardar en disco la imagen
                Storage::disk('images')->put($image_name, File::get($image));
                //  Responder a la peticion
                $res = array(
                    'status'    =>  'success',
                    'code'      =>  200,
                    'image'     =>  $image_name
                );
            }
        } else {
            $res = array(
                'status'    =>  'error',
                'code'      =>  400,
                'message'   =>  'Error al subir imagen, no ha subido ninguna imagen'
            );
        }

        return response()->json($res, $res['code']);
        
    }

    public function getImage($filename) {
        //  Comprobamos si existe la imagen
        $fileExist = Storage::disk('images')->exists($filename);
        //  Evaluamos la respuesta del metodo exists
        if ($fileExist) {
            //  Si existe entonces Conseguimos la imagen
            $file = Storage::disk('images')->get($filename);
            //  Devolvemos la imagen
            return new Response($file, 200);
        } else {
            //  Si no existe entonces mostramos un codigo de error 404
            $res = array(
                'status'    =>  'error',
                'code'      =>  404,
                'message'   =>  'Imagen no existe'
            );
            return response()->json($res, $res['code']);
        }
    }
    
    private function getUserAuth($request) {
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $usuario = $jwtAuth->vericarToken($token, true);

        return $usuario;
    }
}
