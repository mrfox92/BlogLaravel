<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers\JwtAuth;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //  comprobar si el usuario está autenticado
        //  Obtenemos el valor de Authorization desde la cabecera de la peticion http
        $token = $request->header('Authorization');
        //  Creamos una instancia de nuestro helper de autenticacion
        $jwtAuth = new JwtAuth();
        /* verificamos si el token es válido, opcionalmente recibe una bandera(true, false)
        en caso de querer retornar la data del usuario decodificada */
        if($jwtAuth->vericarToken($token)) {
            //  Retorna un next para seguir con las proximas instrucciones y envia el request
            return $next($request);
        } else {
            //  Error message
            $res = array(
                'status'    =>  'error',
                'code'      =>  401,
                'message'   =>  'Accion no autorizada'
            );
            return response()->json($res, $res['code']);
        }
    }
}
