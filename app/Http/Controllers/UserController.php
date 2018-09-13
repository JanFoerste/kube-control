<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * @var Request $request
     */
    private $request;

    /**
     * UserController constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function createUser(): Response
    {
        $this->validate($this->request, [
            'username' => 'required|unique:user|max:32',
            'email' => 'required|email|unique:user|max:255',
            'password' => 'required|min:8|max:128|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])/',
            'role' => 'required|in:viewer,editor,admin'
        ], [
            'password.regex' => 'The password must contain at least one upper-case character, one lower-case character, one number and one of the following special characters: !@$%^&*'
        ]);

        DB::table('user')
            ->insert([
                'username' => $this->request->input('username'),
                'email' => $this->request->input('email'),
                'password' => Hash::make($this->request->input('password')),
                'role' => $this->request->input('role')
            ]);

        return response(null, 201)
            ->header('Location', route('getUser', ['username' => $this->request->input('username')]));
    }

    /**
     * @param string $username
     * @return JsonResponse
     */
    public function getUser(string $username): JsonResponse
    {
        $user = $this->findUserByName($username);

        if (!$user) {
            return response()->json([
                'error' => 'Not found.'
            ], 404);
        }

        unset($user->password);

        return response()->json($user);
    }

    /**
     * @param string $username
     * @return JsonResponse|Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function modifyUser(string $username)
    {
        $user = $this->findUserByName($username);

        if (!$user) {
            return response()->json([
                'error' => 'Not found.'
            ], 404);
        }

        if (Gate::denies('edit-user', $user)) {
            return response()->json([
                'error' => 'You are not authorized to perform this action.'
            ], 403);
        }

        $this->validate($this->request, [
            'email' => 'email|unique:user|max:255',
            'password' => 'min:8|max:128|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])/',
            'role' => 'in:viewer,editor,admin'
        ]);

        $updates = [];
        if ($this->request->has('email')) {
            $updates['email'] = $this->request->input('email');
        }

        if ($this->request->has('password')) {
            $updates['password'] = Hash::make($this->request->input('password'));
        }

        if ($this->request->has('role')) {

            if ($this->request->user()->role !== 'admin') {
                return response()->json([
                    'error' => 'You are not authorized to change user roles.'
                ], 403);
            }

            $updates['role'] = $this->request->input('role');
        }

        DB::table('user')
            ->where('username', '=', $username)
            ->update($updates);

        return response(null, 204);
    }

    /**
     * @param string $username
     * @return JsonResponse|Response
     */
    public function deleteUser(string $username)
    {
        $user = $this->findUserByName($username);

        if (!$user) {
            return response()->json([
                'error' => 'Not found.'
            ], 404);
        }

        if (Gate::denies('edit-user', $user)) {
            return response()->json([
                'error' => 'You are not authorized to perform this action.'
            ], 403);
        }

        DB::table('user')
            ->delete($user->id);

        return response(null, 204);
    }

    /**
     * @param string $username
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|null|object
     */
    private function findUserByName(string $username)
    {
        return DB::table('user')
            ->where('username', '=', $username)
            ->first();
    }
}