<?php
namespace Yjv\HttpQueue\Request;

class RequestEvents
{
    const ERROR = 'request.error';
    const CREATE_HANDLE = 'request.create_handle';
    const COMPLETE = 'request.complete';
    const HANDLE_EVENT = 'request.handle_event';
}
