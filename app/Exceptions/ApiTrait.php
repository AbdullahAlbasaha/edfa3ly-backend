<?php
namespace App\Exceptions;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait ApiTrait{

    public function apiRequest($request,$exception)
    {
        if($this->isModel($exception))
            return $this->modelException();

        if($this->isHttp($exception))
            return $this->httpException();
        return parent::render($request, $exception);
    }

    protected function isModel($e){
        return $e instanceof ModelNotFoundException;
    }
    protected function modelException(){
        return response()->json(' not found in database!',404);
    }
    protected function isHttp($e){
        return $e instanceof HttpException;
    }
    protected function httpException(){
        return response()->json('route not found !',404);

    }
}
