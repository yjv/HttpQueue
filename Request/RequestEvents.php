<?php
namespace Yjv\HttpQueue\Request;

class RequestEvents
{
    const ERROR = 'request.error';
    const CURL_ERROR = 'request.curl_error';
    const COMPLETE = 'request.complete';
    const PROGRESS = 'request.progress';
}
