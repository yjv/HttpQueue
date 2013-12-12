<?php
namespace Yjv\HttpQueue\Response;

use Yjv\HttpQueue\Request\RequestInterface;

use Yjv\HttpQueue\Queue\QueueInterface;

class StatusLineRecievedEvent extends ResponseEvent
{
    protected $code;
    protected $phrase;
    
    public function __consrtuct(QueueInterface $queue, RequestInterface $request, ResponseInterface $response, $code, $phrase)
    {
        parent::__construct($queue, $request, $response);
        $this->code = $code;
        $this->phrase = $phrase;
    }
    
    public function getCode()
    {
        return $this->code;
    }
    
    public function getPhrase()
    {
        return $this->phrase;
    }
}
