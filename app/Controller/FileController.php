<?php

declare(strict_types=1);

namespace App\Controller;

use App\Constants\ErrorCode;
use App\Support\Auth;
use App\Model\Message;
use App\Model\Room;
use Carbon\Carbon;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\Middleware;
use Swoole\Constant;
use App\Middleware\AuthMiddleware;

/**
 * @Controller(prefix="api/file")
 */
class FileController extends AbstractController
{
    /**
     * 上传图片
     * @PostMapping(path="uploadimg")
     * @Middlewares({
     *     @Middleware(AuthMiddleware::class)
     * })
     */
    public function uploadImage()
    {
        if (!$this->request->hasFile('file') || !$this->request->file('file')->isValid() ) {
            return $this->response->json([
                'msg' => '无效的参数（图片文件为空或者无效）',
            ])->withStatus(ErrorCode::INVALID_PARAM);
        }
        $image = $this->request->file('file');
        $time = time();
        $filename = md5($time . mt_rand(0, 10000)) . '.' . $image->getExtension();
        $path = '/storage/images/' . date('Y/m/d', $time);
        $realPath = config('server.settings.' . Constant::OPTION_DOCUMENT_ROOT) . $path;
        if (is_dir($realPath) || mkdir($realPath, 0777, true)) {
            $image->moveTo($realPath . '/' . $filename);
            chmod($realPath . '/' . $filename, 0777);
        }
        if ($image->isMoved()) {
            return $this->response->json([
                'success' => true, 
                'url' => 'http://127.0.0.1:9501' . $path . '/' . $filename,  // TODO: 暂时写死域名
            ]);
        } else {
            return $this->response->json([
                'success' => false,
                'msg' => '文件上传失败，请重试'
            ]);
        }
    }


    /**
     * 上传头像
     * @PostMapping(path="avatar")
     * @Middlewares({
     *     @Middleware(AuthMiddleware::class)
     * })
     */
    public function avatar()
    {
        if (!$this->request->hasFile('file') || !$this->request->file('file')->isValid() ) {
            return $this->response->json([
                'msg' => '无效的参数（图片文件为空或者无效）',
            ])->withStatus(ErrorCode::INVALID_PARAM);
        }
        $image = $this->request->file('file');
        $time = time();
        $filename = md5($time . mt_rand(0, 10000)) . '.' . $image->getExtension();
        $path = '/storage/avatars/' . date('Y/m/d', $time);
        $realPath = config('server.settings.' . Constant::OPTION_DOCUMENT_ROOT) . $path;
        if (is_dir($realPath) || mkdir($realPath, 0777, true)) {
            $image->moveTo($realPath . '/' . $filename);
            chmod($realPath . '/' . $filename, 0777);
        }
        if ($image->isMoved()) {
            $user = Auth::user();
            $user->avatar = 'http://127.0.0.1:9501' . $path . '/' . $filename;
            $user->save();
            return $this->response->json([
                'success' => true,
                'url' => $user->avatar,
            ]);
        } else {
            return $this->response->json([
                'success' => false,
                'msg' => '文件上传失败，请重试'
            ]);
        }
    }

}
