<?php
namespace Yjv\HttpQueue\Response;

class ResponseEvents
{
    const RECEIVE_STATUS_LINE = 'response.recieve_status_line';
    const HEADER_RECEIVED = 'response.header_received';
    const WRITE_BODY = 'response.write_body';
}
