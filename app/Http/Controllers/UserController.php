<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\UserCreateRequest;
use App\Helpers\JwtAuth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Validator;
use App\User;
class UserController extends Controller
{
    public function pruebas(Request $request) {
        return 'User controller works';
    }

    public function register(Request $request) {
        //  Recoger la data del usuario que es enviada en formato json
        $json = $request->input('json', null);
        //  Decodificamos el json para obtener los parametros del json
        $params = json_decode($json);// Nos devuelve un objeto
        //  Podemos convertir el objeto en un array
        $params_array = json_decode($json, true);// Nos devuelve un array
        //  Validamos que el objeto y el array no vengan vacios o que el json venga con errores de sintaxis
        if (!empty($params) && !empty($params_array)) {
            //  Limpiar la Data
            $params_array = array_map('trim', $params_array);
            //  Validar la Data
            $validate = Validator::make($params_array, [
                'name'  =>  'required|alpha',
                'surname'   =>  'required|alpha',
                'email' =>  'required|email|unique:users',
                'password'  =>  'required'
            ]);
            //  Comprobar si hay fallos en las validaciones
            if ( $validate->fails() ) {
                $data = array(
                    'status'    =>  'error',
                    'code'      =>  400,
                    'message'   =>  'El usuario no ha podido ser registrado',
                    'errors'    =>  $validate->errors()
                );
            } else {
                //  Todo OK
                //  Cifrar contraseña
                /* password hash recibe la contraseña, el algoritmo de cifrado y un array que
                recibe parametros como; cost que es la cantidad de veces que ciframos la contraseña */
                $pwd = password_hash($params->password, PASSWORD_BCRYPT, ['cost' => 4]);
                //  Comprobar si el usuario existe (usuario duplicado)
                //  Crear el usuario
                $user = new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role = 'USER_ROLE';

                //  Guardar el usuario
                $user->save();
                //  Respuesta
                $data = array(
                    'status'    =>  'success',
                    'code'      =>  201,
                    'message'   =>  'Usuario creado exitosamente',
                    'usuario'   =>  $user
                );
            }
        } else {
            $data = array(
                'status'    =>  'error',
                'code'      =>  400,
                'message'   =>  'Los datos enviados no son correctos'
            );
        }

        //  Retornamos la respuesta en formato json
        return response()->json( $data, $data['code'] );
    }

    public function login(Request $request) {
        $jwtAuth = new JwtAuth();
        //  Recibir la data por post
        $json = $request->input('json', null);
        //  obtenemos el objeto con la data
        $params = json_decode($json);
        //  obtenemos array con la data
        $params_array = json_decode($json, true);
        //  Validamos que el elemento json no venga vacio o mal formado
        if (!empty($params) && !empty($params_array)) {
            //  limpiamos la data
            $params_array = array_map('trim', $params_array);
            //  Validar los datos
            $validate = Validator::make($params_array, [
                'email'     =>  'email|required',
                'password'  =>  'required'
            ]);
            //  respuesta desde el usuario
            if ($validate->fails()) {
                $data = array(
                    'status'        =>  'error',
                    'code'          =>  400,
                    'message'       =>  'El usuario no se ha podido identificar',
                    'errors'        =>  $validate->errors()
                );
            } else {

                if (!empty($params->getToken) && $params->getToken === true) {
                    $data = $jwtAuth->signUp($params->email, $params->password, $params->getToken );
                } else {
                    //  llamamos a nuestro metodo de autenticacion
                    $data = $jwtAuth->signUp($params->email, $params->password);
                }
            }
        } else {
            $data = array(
                'status'    =>  'error',
                'code'      =>  400,
                'message'   =>  'Los datos enviados no son correctos'
            );
        }
        //  Devolver respuesta json
        return response()->json( $data, $data['code'] );
    }

    public function update(Request $request) {
        //  comprobar si el usuario está autenticado para ello utilizamos un middleware
        //  Obtener el json con la data
        $json = $request->input('json', null);
        //  Recoger los datos desde el json y pasarlos a un array
        $params_array = json_decode($json, true);
        //  Validamos que venga la data
        if(!empty($params_array)) {
            //  obtener usuario identificado
            $user = $jwtAuth->vericarToken($token, true);
            //  Validar los datos, en el email unique declaramos el id de usuario como excepcion
            $validate = Validator::make($params_array, [
                'name'  =>  'required|alpha',
                'surname'   =>  'required|alpha',
                'email' =>  'required|email|unique:users,email,'.$user->sub
            ]);
            //  Comprobamos si existe algun error de validacion
            if ( $validate->fails() ) {
                $res = array(
                    'status'    =>  'error',
                    'code'      =>  400,
                    'message'   =>  'El usuario no ha podido ser actualizado',
                    'errors'    =>  $validate->errors()
                );
            } else {

                //  Quitar campos que no se desean actualizar
                unset($params_array['id']);
                unset($params_array['role']);
                unset($params_array['password']);
                unset($params_array['created_at']);
                unset($params_array['updated_at']);
                unset($params_array['deleted_at']);
                unset($params_array['remember_token']);
                //  Actualizar usuario en la DB
                $user_update = User::where('id', $user->sub)->update($params_array);
                //  Devolver usuario actualizado en la respuesta http
                $usuario = User::find($user->sub);
                $res = array(
                    'status'    =>  'success',
                    'code'      =>  200,
                    'usuario'   =>  $usuario
                );
            }
        } else {
            //  Error message
            $res = array(
                'status'    =>  'error',
                'code'      =>  400,
                'message'   =>  'La informacion no es válida'
            );
        }
        //  Enviamos la respuesta en formato json
        return response()->json( $res, $res['code'] );
    }

    public function upload(Request $request ) {
        //  Recoger datos de la peticion
        $image = $request->file('file0');
        //  Validar archivos subidos
        $validate = Validator::make($request->all(),[
            'file0' =>  'required|image|mimes:jpg,jpeg,png,gif'
        ]);
        //  Comprobamos si tenemos errores en nuestra validacion o si image viene vacia
        if (!$image || $validate->fails()) {
            $res = array(
                'status'    =>  'error',
                'code'      =>  400,
                'message'   =>  'Error al subir imagen',
                'erros'     =>  $validate->errors()
            );
            
        } else {
            $image_name = time().$image->getClientOriginalName();
            //  Utilizamos storage y el disco en donde guardaremos la imagen
            Storage::disk('users')->put($image_name, File::get($image));
            //  si todo va bien mostramos la respuesta
            $res = array(
                'status'    =>  'success',
                'code'      =>  201,
                'imagen'    =>  $image_name
            );
        }

        return response()->json( $res, $res['code'] );
    }

    public function getImage($filename) {
        //  Obtener la imagen desde el disco
        $fileExist = Storage::disk('users')->exists($filename);
        if ($fileExist) {
            $file = Storage::disk('users')->get($filename);
            return new Response($file, 200);
        } else {
            $res = array(
                'status'    =>  'error',
                'code'      =>  404,
                'message'   =>  'La imagen buscada no existe'
            );
            return response()->json( $res, $res['code'] );
        }
    }

    public function profile($id) {
        $user = User::find($id);
        if (is_object($user)) {
            $res = array(
                'status'    =>  'success',
                'code'      =>  200,
                'usuario'   =>  $user
            );
        } else {
            $res = array(
                'status'    =>  'error',
                'code'      =>  404,
                'message'   =>  'Usuario no existe'
            );
        }

        return response()->json($res, $res['code']);
    }
}
