<?php
namespace Yjv\HttpQueue\Response;

class ResponseEvents
{
    const RECEIVE_STATUS_LINE = 'response.recieve_status_line';
    const WRITE_HEADER = 'response.write_header';
    const WRITE_BODY = 'response.write_body';
}
