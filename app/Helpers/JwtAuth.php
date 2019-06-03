<?php
namespace App\Helpers;
//  incluimos la libreria JWT
use Firebase\JWT\JWT;
//  incluimos la clase DB para hacer consultas sobre la base de datos
use Illuminate\Support\Facades\DB;
use App\User;

class JWTAuth {

    private $key;

    public function __construct() {
        $this->key = 'esto es una llave privada';
    }



    public function signUp($email, $password, $getToken = false) {

        //  Buscar si existe el usuario con sus credenciales (email)
        $user = User::where([
            'email'     =>  $email
        ])->first();
        if (is_object($user)) {
            //comprobamos la contraseña
            if (password_verify($password, $user->password)) {
                //  Generamos el payload del token
                $payload = array(
                    'sub'       =>      $user->id,
                    'email'     =>      $user->email,
                    'name'      =>      $user->name,
                    'surname'   =>      $user->surname,
                    'iat'       =>      time(),
                    'exp'       =>      time()  + (7 * 24 * 60 * 60)
                );
                //  Generamos el token
                $token = JWT::encode($payload, $this->key, 'HS256');

                // si getToken es true enviamos el token
                if ($getToken) {
                    return array(
                        'status'        =>  'success',
                        'code'          =>  200,
                        'usuario'       =>  $user,
                        'token'         =>  $token,
                        'id'            =>  $user->id
                    );
                }  else {
                    //  Decodificamos el token(enviamos el payload)
                    $decoded = JWT::decode($token, $this->key, ['HS256']);
                    return array(
                        'status'        =>  'success',
                        'code'          =>  200,
                        'usuario'       =>  $decoded,
                        'id'            =>  $decoded->sub
                    );
                }
            } else {
                return array(
                    'status'        =>  'error',
                    'code'          =>  400,
                    'message'       =>  'Credenciales no válidas - password'
                );
            }
        } else {

            return array(
                'status'        =>  'error',
                'code'          =>  400,
                'message'       =>  'Credenciales no válidas - email'
            );
        }
        //  return $data;
    }

    public function vericarToken($token, $getIdentity = false) {
        $auth = false;
        try {
            $token = str_replace('"', '', $token);
            $payload = JWT::decode($token, $this->key, ['HS256']);
        } catch(\UnexpectedValueException $ex) {
            //  Lanza una excepción si un valor no coincide con un grupo de valores
            $auth = false;              
        } catch(\DomainException $ex){
            //  Excepción lanzada si un valor no se adhiere a un dominio definido de datos válidos.
            $auth = false;
        }

        //  Si el token paylo$payload tiene data, es un objeto y esta asignada la propiedad sub, la atenticacion será correcta
        if (!empty($payload) && is_object($payload) && isset($payload->sub)) {
            $auth = true;
            
            if ($getIdentity) {
                return $payload;
            }
        } else {
            $auth = false;
        }

        return $auth;
    }
}