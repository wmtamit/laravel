<?php

namespace App\Traits;

use App\Utils\AppConstant;

trait ApiResponse
{
    protected $meta;
    protected $data;
    protected $paginate;
    protected $response;

    protected function setMeta($key, $value)
    {
        $this->meta[$key] = $value;
    }

    protected function setData($key, $value)
    {
        $this->data[$key] = $value;
    }

    protected function setPaginate($value)
    {
        $this->paginate = $value;
    }

    protected function setResponse()
    {
//        $this->response['meta'] = $this->meta;
        if ($this->data !== null) {
            $this->response['data'] = $this->data;
        }
        if ($this->paginate !== null) {
            $this->response['pagination'] = $this->paginate;
        }
//        $this->meta = array();
        $this->data = array();
        $this->paginate = array();
        return $this->response;
    }


    protected function setQueryExceptionResponse($message = '')
    {
        if ($message === '')
            $message = __('auth.server_error');

        $this->meta = array();
        $this->data = array();
        $this->paginate = array();

        $this->meta['status'] = AppConstant::STATUS_FAIL;
        $this->meta['message'] = $message;

        $this->response['meta'] = $this->meta;

        $this->meta = array();
        $this->data = array();
        $this->paginate = array();

        return $this->response;
    }
}
