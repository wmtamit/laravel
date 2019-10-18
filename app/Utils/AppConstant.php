<?php

namespace App\Utils;

class AppConstant
{
    //  Pagination
    const PAGINATION = 15;

    const WIDTH_POPUP = '500';
    const DISTANCE_RADIUS = 3000;
    //  Message Status
    const DEFAULT_COUNTRY_CODE = '+1';
    const DEFAULT_ZERO = 0;
    const DEFAULT_ONE = 1;
    const STATUS_FAIL = 'fail';
    const STATUS_OK = 'ok';
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const MALE = 1;
    const FEMALE = 2;


    // API status codes
    const OK = 200;
    const CREATED = 201;
    const NOT_IN_RANGE = 416;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const UNPROCESSABLE_REQUEST = 422;
    const INTERNAL_SERVER_ERROR = 500;
    const TOKEN_INVALID = 503;

    const OS_TYPE = ['android', 'ios'];

    const OS_ANDROID = "android";
    const OS_IOS = "ios";
    const DEVICE_ALL = 1;

    //  Image Constant
    const USER_IMAGE = 'common/user.png';
    const ADMIN_IMAGE = 'common/admin.png';
    const LOCATION_IMAGE = 'common/location.png';
    const PRODUCT_IMAGE = 'common/location.png';

    //  App Versioning
    const FORCE_UPDATE = 1;
    const NOT_FORCE_UPDATE = 0;

    //  Date Format
    const CARBON_FORMAT = 'm-d-Y';
    const REVENUE_FORMAT = 'M. d, Y';
    const DATEPICKER_FORMAT = 'MM-DD-YYYY';
    const SYSTEM_FORMAT = 'Y-m-d';

}
