<?php
namespace Yjv\HttpQueue\Event;

class RequestEvents
{
    const PRE_CREATE_HANDLE = 'request.pre_create_handle';
    const POST_CREATE_HANDLE = 'request.post_create_handle';
    const PRE_SEND = 'request.pre_send';
    const HANDLE_EVENT = 'request.handle_event';
    const COMPLETE = 'request.complete';
}
