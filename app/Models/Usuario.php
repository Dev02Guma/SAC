<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class Usuario extends Model {
    protected $table = "users";

    public function Detalles(){
        return $this->hasMany('App\Models\UsuarioRutas','id_usuario','id');
    }
    public function RolName(){
        return $this->hasOne('App\Models\Roles','id','id_rol');
    }


    public static function getUsuarios()
    {
        // Activos e inactivos (activo = 'E' es un usuario eliminado y no se lista)
        return Usuario::with(['Detalles', 'RolName'])
            ->whereIn('activo', ['S', 'N'])
            ->whereNotIn('id_rol', ['11'])
            ->orderBy('nombre')
            ->get();
    }
    public static function getUsuarioVendedor()
    {
        return UsuarioVendedor::where('activo','S')->where('username', 'LIKE','F%')->get();
    }

    public static function getUsuariosSAC()
    {
        return Usuario::where('activo','S')->where('id_rol',9)->get();
    }

    /**
     * Crea (Estado = 0) o actualiza (Estado = id) un usuario.
     * En la edición la contraseña solo cambia si se envía; 'activo' acepta S o N.
     */
    public static function SaveUsuario(Request $request) {
        try {
            $id       = (int) $request->input('Estado');
            $usuario  = strtoupper(trim((string) $request->input('usuario')));
            $nombre   = trim((string) $request->input('nombre'));
            $password = (string) $request->input('passwprd');
            $idRol    = (int) $request->input('id_rol');
            $activo   = $request->input('activo') === 'N' ? 'N' : 'S';

            if ($usuario === '' || $nombre === '' || $idRol <= 0) {
                return ['ok' => false, 'mensaje' => 'Nombre, usuario y rol son obligatorios.'];
            }
            if ($password !== '' && strlen($password) < 8) {
                return ['ok' => false, 'mensaje' => 'La contraseña debe tener al menos 8 caracteres.'];
            }
            $duplicado = Usuario::where('username', $usuario)->where('activo', '<>', 'E')
                ->when($id > 0, function ($q) use ($id) { $q->where('id', '<>', $id); })->exists();
            if ($duplicado) {
                return ['ok' => false, 'mensaje' => 'Ya existe un usuario con ese nombre de usuario.'];
            }

            if ($id === 0) {
                if ($password === '') {
                    return ['ok' => false, 'mensaje' => 'La contraseña es obligatoria para un usuario nuevo.'];
                }
                $obj = new Usuario();
                $obj->username = $usuario;
                $obj->nombre   = $nombre;
                $obj->password = Hash::make($password);
                $obj->id_rol   = $idRol;
                $obj->activo   = $activo;
                $obj->save();
                $id = $obj->id;
            } else {
                $datos = ['username' => $usuario, 'nombre' => $nombre, 'id_rol' => $idRol, 'activo' => $activo];
                if ($password !== '') {
                    $datos['password'] = Hash::make($password);
                }
                Usuario::where('id', $id)->update($datos);
            }

            return ['ok' => true, 'id' => $id];
        } catch (\Throwable $e) {
            return ['ok' => false, 'mensaje' => 'No se pudo guardar el usuario: ' . $e->getMessage()];
        }
    }

    /**
     * Elimina un usuario (borrado lógico: activo = 'E') y libera sus rutas.
     */
    public static function DeleteUsuario(Request $request)
    {
        try {
            $id = (int) $request->input('id');
            Usuario::where('id', $id)->update(['activo' => 'E']);
            UsuarioRutas::where('id_usuario', $id)->delete();
            return ['ok' => true];
        } catch (\Throwable $e) {
            return ['ok' => false, 'mensaje' => 'No se pudo eliminar el usuario: ' . $e->getMessage()];
        }
    }
}
