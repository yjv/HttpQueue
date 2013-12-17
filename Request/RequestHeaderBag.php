<?php
namespace Yjv\HttpQueue\Request;

use Symfony\Component\HttpFoundation\HeaderBag;

use Symfony\Component\HttpFoundation\Cookie;

class RequestHeaderBag extends HeaderBag
{
    const COOKIES_STRING           = 'string';
    const COOKIES_ARRAY          = 'array';

    /**
     * @var array
     */
    protected $cookies              = array();

    /**
     * @var array
     */
    protected $headerNames          = array();

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $cookies = '';
        foreach ($this->getCookies() as $cookie) {
            $cookies .= 'Set-Cookie: '.$cookie."\r\n";
        }

        ksort($this->headerNames);

        return parent::__toString().$cookies;
    }

    /**
     * Returns the headers, with original capitalizations.
     *
     * @return array An array of headers
     */
    public function allPreserveCase()
    {
        return array_combine($this->headerNames, $this->headers);
    }
    
    public function allPreserveCaseFlattened()
    {
        return array_map(function(array $value)
        {
            return implode(';', $value);
        }, $this->allPreserveCase());
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function replace(array $headers = array())
    {
        $this->headerNames = array();

        parent::replace($headers);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function set($key, $values, $replace = true)
    {
        parent::set($key, $values, $replace);

        $uniqueKey = strtr(strtolower($key), '_', '-');
        $this->headerNames[$uniqueKey] = $key;
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function remove($key)
    {
        parent::remove($key);

        $uniqueKey = strtr(strtolower($key), '_', '-');
        unset($this->headerNames[$uniqueKey]);
    }

    /**
     * Sets a cookie.
     *
     * @param Cookie $cookie
     *
     * @api
     */
    public function setCookie(Cookie $cookie)
    {
        $this->cookies[$cookie->getName()] = $cookie;
        return $this;
    }

    /**
     * Removes a cookie from the array, but does not unset it in the browser
     *
     * @param string $name
     * @param string $path
     * @param string $domain
     *
     * @api
     */
    public function removeCookie($name)
    {
        unset($this->cookies[$name]);
        return $this;
    }

    /**
     * Returns an array with all cookies
     *
     * @param string $format
     *
     * @throws \InvalidArgumentException When the $format is invalid
     *
     * @return array
     *
     * @api
     */
    public function getCookies($format = self::COOKIES_ARRAY)
    {
        if (!in_array($format, array(self::COOKIES_STRING, self::COOKIES_ARRAY))) {
            throw new \InvalidArgumentException(sprintf('Format "%s" invalid (%s).', $format, implode(', ', array(self::COOKIES_STRING, self::COOKIES_ARRAY))));
        }

        if (self::COOKIES_ARRAY === $format) {
            return $this->cookies;
        }
        
        return implode('; ', array_map(function(Cookie $cookie)
        {
            return sprintf('%s=%s', $cookie->getName(), $cookie->getValue());
        }, $this->cookies));
    }

    /**
     * Clears a cookie in the browser
     *
     * @param string $name
     * @param string $path
     * @param string $domain
     *
     * @api
     */
    public function clearCookie($name)
    {
        $this->setCookie(new Cookie($name));
    }
}
