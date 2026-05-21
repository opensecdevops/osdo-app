<?php

namespace App\Http\Controllers\Cli;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * CliAuthController — Gestiona la autenticación del CLI OSDO con la App.
 *
 * Emite y revoca Sanctum Personal Access Tokens con los abilities:
 *   - cli:read   — lectura de paquetes y deployments
 *   - cli:deploy — registro de deployments
 *
 * Los tokens anteriores del CLI se revocan al emitir uno nuevo para
 * evitar la acumulación de tokens obsoletos en base de datos.
 */
class CliAuthController extends Controller
{
    /**
     * Emitir un Personal Access Token de Sanctum para el CLI OSDO.
     * Equivale a "login" desde el CLI.
     *
     * POST /api/cli/auth
     *
     * Body:
     *   { "email": "usuario@ejemplo.com", "password": "contraseña" }
     *
     * Response 200:
     *   { "token": "1|abcdef...", "user": { "id": 1, "name": "...", "email": "..." } }
     */
    public function token(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas no son válidas.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        // Revocar tokens CLI previos para evitar acumulación
        $user->tokens()->where('name', 'osdo-cli')->delete();

        $token = $user->createToken('osdo-cli', ['cli:read', 'cli:deploy']);

        return response()->json([
            'token' => $token->plainTextToken,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Revocar el token CLI del usuario actualmente autenticado.
     *
     * DELETE /api/cli/auth
     *
     * Response 200:
     *   { "message": "Sesión cerrada correctamente." }
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }
}
