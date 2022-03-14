<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\User;
use App\Request\AuthRequest;
use App\Support\Auth;
use App\Support\Str;
use App\Middleware\AuthMiddleware;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\Middleware;

/**
 * @Controller()
 */
class AuthController extends AbstractController
{
    /**
     * @RequestMapping(path="register", method="post")
     */
    public function register() 
    {
        $request = $this->container->get(AuthRequest::class);
        $request->scene('register')->validateResolved();

        return User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => md5($request->input('password')),
            'api_token' => Str::random(60)
        ]);
    }

    /**
     * @RequestMapping(path="login", method="post")
     */
    public function login() 
    {
        $request = $this->container->get(AuthRequest::class);
        $request->scene('login')->validateResolved();

        $user = User::query()->where('email', $request->input('email'))->first();

        if ($user && md5($request->input('password')) == $user->password) {
            $user = Auth::login($user);
            return $this->response->json(['user' => $user, 'success' => true]);
        }

        return $this->response->json(['success' => false]);
    }

    /**
     * @RequestMapping(path="logout", method="post")
     * @Middlewares({
     *     @Middleware(AuthMiddleware::class)
     * })
     */
    public function logout()
    {
        Auth::logout($this->request);

        return $this->response->json(['success' => true]);
    }
}
