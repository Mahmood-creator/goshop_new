<?php

namespace App\Exceptions;

use App\Helpers\ResponseError;
use App\Traits\ApiResponse;
use GuzzleHttp\Client;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponse;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     * @throws Throwable
     */
    public function report(Throwable $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @param Throwable $exception
     * @return Response
     *
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        return $this->handleException($request, $exception);
    }



    public function handleException($request, Throwable $exception)
    {

//        if ($exception instanceof RouteNotFoundException || $exception instanceof NotFoundHttpException) {
//            return $this->errorResponse(404,$exception->getMessage(), Response::HTTP_NOT_FOUND);
//        }
//        if ($exception instanceof AuthorizationException) {
//            return $this->errorResponse(403,$exception->getMessage(), Response::HTTP_FORBIDDEN);
//        }
//        if ($exception instanceof ModelNotFoundException || $exception instanceof NotFoundHttpException) {
//            return $this->errorResponse(404,$exception->getMessage(), Response::HTTP_NOT_FOUND);
//        }
//        if (\App::environment(['production'])) {
//            $this->sendError($exception);
//        }

        if ($exception instanceof ValidationException) {

            $items = $exception->validator->errors()->getMessages();

            return $this->requestErrorResponse(
                ResponseError::ERROR_400,
                trans('errors.' . ResponseError::ERROR_400, [], request()->lang),
                $items, Response::HTTP_BAD_REQUEST);
        }
        return $this->errorResponse(Response::HTTP_INTERNAL_SERVER_ERROR,$exception->getMessage().' in '.$exception->getFile().":".$exception->getLine());

    }

    public function sendError($exception)
    {
        $bot_token = '5751635920:AAHQBN0xwn_q4qoNjYAJhxxgVmKqfFM9wgQ';
        $bot_chatID = '537129580';
        $bot_message = "‼️‼️‼️Ошибка 500 \n File: " . $exception->getFile() . " \n Line: " . $exception->getLine() . " \n Message: " . $exception->getMessage();

        if (auth('sanctum')->check()) {
            $bot_message .= "\n User ID: " . auth()->user()->id . " \n User full name: " . auth('sanctum')->user()->full_name;
        }

        $bot_message .= "\n IP:" . request()->ip();

        $send_text = 'https://api.telegram.org/bot' . $bot_token . '/sendMessage?chat_id=' . $bot_chatID . '&parse_mode=HTML&text=' . $bot_message;
        $client = new Client();

        $response = $client->get($send_text);
    }
}
