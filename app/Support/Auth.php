<?php

declare(strict_types=1);

namespace App\Support;

use App\Model\User;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\Str;

class Auth
{
    /**
     * 获取登录用户
     * @return User
     */
    public static function user()
    {
        $container = \Hyperf\Utils\ApplicationContext::getContainer();
        $request = $container->get(RequestInterface::class);

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
     */
    public static function logout()
    {
        $user = static::user();
        $user->api_token = null;
        $user->save();
    }

    /**
     * 检查该请求是否有登录用户信息
     * @return bool
     */
    public static function check()
    {
        $user = static::user();
        return !is_null($user);
    }
}