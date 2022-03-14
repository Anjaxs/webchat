<?php

declare(strict_types=1);

namespace App\Support;

use App\Model\User;
use Hyperf\HttpServer\Contract\RequestInterface;

class Auth
{
    /**
     * 获取登录用户
     * @param RequestInterface $request
     * @return User
     */
    public static function user(RequestInterface $request)
    {
        $token = $request->input('api_token');

        if (empty($token) && $header = $request->header('Authorization', '')) {
            Str::startsWith($header, 'Bearer ') && $token = Str::substr($header, 7);
        }

        $user = User::query()->where('api_token', $token)->first();

        return $user;
    }

    /**
     * 用户登录
     * @param User $user 用户
     * @return User
     */
    public static function login(User $user)
    {
        $user->api_token = Str::random(60);
        $user->save();

        return $user;
    }

    /**
     * 退出登录
     * @param RequestInterface $request
     */
    public static function logout(RequestInterface $request)
    {
        $user = static::user($request);
        $user->api_token = Str::random(60);
        $user->save();
    }

    /**
     * 检查该请求是否有登录用户信息
     * @param RequestInterface $request
     * @return bool
     */
    public static function check(RequestInterface $request)
    {
        $user = static::user($request);
        return !is_null($user);
    }
}