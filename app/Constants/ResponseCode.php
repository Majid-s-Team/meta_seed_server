<?php

namespace App\Constants;

class ResponseCode
{
    const SUCCESS = 200;
    const CREATED = 201;
    const UPDATED = 202;


    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const VALIDATION_ERROR = 422;

    const INTERNAL_SERVER_ERROR = 500;
}
