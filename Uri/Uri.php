<?php
namespace Yjv\HttpQueue\Uri;

class Uri
{
    protected $scheme = '';
    protected $host = '';
    protected $port = '';
    protected $username = '';
    protected $password = '';
    protected $path;
    protected $query;
    protected $fragment = '';
    
    public static function createFromString($uriString)
    {
        $uriParts = parse_url($uriString);
        
        if ($uriParts === false) {
            
            throw new \InvalidArgumentException('there was an error parsing the url string.');
        }
        
        $uriParts = array_merge(array(
            'scheme' => '',
            'port' => '',
            'user' => '',
            'pass' => '',
            'host' => '',
            'path' => '',
            'query' => '',
            'fragment' => '',
        ), $uriParts);
        
        $uri = new static();
        $uri
            ->setScheme($uriParts['scheme'])
            ->setPort($uriParts['port'])
            ->setUsername($uriParts['user'])
            ->setPassword($uriParts['pass'])
            ->setHost($uriParts['host'])
            ->setPath(Path::createFromString($uriParts['path']))
            ->setQuery(Query::createFromString($uriParts['query']))
            ->setFragment($uriParts['fragment'])
        ;
        return $uri;
    }
    
    public function getScheme()
    {
        return $this->scheme;
    }
    
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;
        return $this;
    }
    
    public function getHost()
    {
        return $this->host;
    }
    
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }
    
    public function getPort()
    {
        return $this->port;
    }
    
    public function setPort($port)
    {
        $this->port = (int)$port;
        return $this;
    }
    
    public function getUsername()
    {
        return $this->username;
    }
    
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }
    
    public function getPassword()
    {
        return $this->password;
    }
    
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }
    
    public function getPath()
    {
        if (!$this->path) {
            
            $this->path = new Path();
        }
        
        return $this->path;
    }
    
    public function setPath(Path $path)
    {
        $this->path = $path;
        return $this;
    }
    
    public function getQuery()
    {
        if (!$this->query) {
            
            $this->query = new Query();
        }
        
        return $this->query;
    }
    
    public function setQuery(Query $query)
    {
        $this->query = $query;
        return $this;
    }
    
    public function getFragment()
    {
        return $this->fragment;
    }
    
    public function setFragment($fragment)
    {
        $this->fragment = $fragment;
        return $this;
    }
    
    public function __toString()
    {
        $url = $this->scheme ? rawurlencode($this->scheme) . '://' : '';
        
        if ($this->username) {
            
            $url .= rawurlencode($this->username);
            $url .= $this->password ? ':' . rawurlencode($this->password) : '';
            $url .= '@';
        }
        
        $url .= rawurlencode($this->host);
        $url .= $this->port ? ':' . $this->port : '';
        $url .= $this->path;
        $query = (string)$this->query;
        $url .= $query ? '?' . $query : '';
        $url .= $this->fragment ? '#' . $this->fragment : '';
        return $url;
    }
}
