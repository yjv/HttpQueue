<?php
namespace Yjv\HttpQueue\Event;

class RequestEvents
{
    const PRE_CREATE_HANDLE = 'request.pre_create_handle';
    const POST_CREATE_HANDLE = 'request.post_create_handle';
    const COMPLETE = 'request.complete';
    const HANDLE_EVENT = 'request.handle_event';
}
