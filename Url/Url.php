<?php
namespace Yjv\HttpQueue\Url;

class Url
{
    protected $scheme;
    protected $host;
    protected $port;
    protected $username;
    protected $password;
    protected $path;
    protected $query;
    protected $fragment;
    
    public static function createFromString($urlString)
    {
        $url = parse_url($url);
        return new static(
            isset($url['scheme']) ? $url['scheme'] : '',
            isset($url['port']) ? $url['port'] : '',
            isset($url['user']) ? $url['user'] : '',
            isset($url['pass']) ? $url['pass'] : '',
            isset($url['host']) ? $url['host'] : '',
            Path::createFromString(isset($url['path']) ? $url['path'] : ''),
            Query::createFromString(isset($url['query']) ? $url['query'] : ''),
            isset($url['fragment']) ? $url['fragment'] : ''
        );
    }
    
    public function __construct(
        $scheme, 
        $port, 
        $username, 
        $password, 
        $host, 
        Path $path, 
        Query $query, 
        $fragment
    ) {
        $this->scheme = $scheme;
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->path = $path;
        $this->query = $query;
        $this->fragment = $fragment;
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
        $this->port = $port;
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
        return $this->path;
    }
    
    public function setPath(Path $path)
    {
        $this->path = $path;
        return $this;
    }
    
    public function getQuery()
    {
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
        
        $url .= $this->host ? rawurlencode($this->host) : '';
        $url .= $this->port ? (int)$this->port : '';
        $url .= $this->path;
        $query = (string)$this->query;
        $url .= $query ? '?' . $query : '';
        $url .= $this->fragment ? '#' . $this->fragment : '';
        return $url;
    }
}
