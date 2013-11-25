<?php
namespace Yjv\HttpQueue\Request;

class RequestEvents
{
    const ERROR = 'request.error';
    const COMPLETE = 'request.complete';
    const RECEIVE_STATUS_LINE = 'request.receive.status_line';
}
