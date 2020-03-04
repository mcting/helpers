<?php


namespace Mcting\Helpers;

use Exception;
use Illuminate\Support\MessageBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiException extends HttpException
{
    private $errors;

    /**
     * ApiException constructor.
     * @param int|null $code
     * @param string|null $message
     * @param array|null $errors
     * @param Exception|null $previous
     * @param array $headers
     */
    public function __construct(int $code = null, string $message = null, array $errors = null, Exception $previous = null, array $headers = [])
    {
        $code = $code ?: Response::HTTP_BAD_REQUEST;
        $statusCode = intval(substr($code, 0, 3));
        if (is_null($errors)) {
            $this->errors = new MessageBag;
        } else {
            $this->errors = is_array($errors) ? new MessageBag($errors) : $errors;
        }
        $message = $message ?: Response::$statusTexts[$statusCode];
        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }

    /**
     * Get the errors message bag.
     * @return MessageBag|null
     * @author herry.yao<yao.yuandeng@qianka.com>
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return bool
     * @author herry.yao<yao.yuandeng@qianka.com>
     */
    public function hasErrors()
    {
        return !$this->errors->isEmpty();
    }
}
