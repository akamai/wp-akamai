<?php
/**
 * Akamai {OPEN} EdgeGrid Auth for PHP
 *
 * @author Davey Shafik <dshafik@akamai.com>
 * @copyright Copyright 2016 Akamai Technologies, Inc. All rights reserved.
 * @license Apache 2.0
 * @link https://github.com/akamai-open/AkamaiOPEN-edgegrid-php
 * @link https://developer.akamai.com
 * @link https://developer.akamai.com/introduction/Client_Auth.html
 */
namespace Akamai\Open\EdgeGrid;

use Akamai\Open\EdgeGrid\Authentication\Nonce;
use Akamai\Open\EdgeGrid\Authentication\Timestamp;
use Akamai\Open\EdgeGrid\Authentication\Exception\ConfigException;
use Akamai\Open\EdgeGrid\Authentication\Exception\SignerException\InvalidSignDataException;

/**
 * Akamai {OPEN} EdgeGrid Request Signer
 *
 * @package \Akamai\Open\EdgeGrid\Authentication
 */
class Authentication
{
    /**
     * @var array Authentication tokens
     */
    protected $auth;

    /**
     * @var string HTTP method
     */
    protected $httpMethod;

    /**
     * @var string HTTP host
     */
    protected $host;

    /**
     * @var array Guzzle config
     */
    protected $config = array();

    /**
     * @var string Request path
     */
    protected $path;

    /**
     * @var Timestamp Request timestamp
     */
    protected $timestamp;

    /**
     * @var Nonce Request nonce
     */
    protected $nonce;

    /**
     * @var int Maximum body size for signing
     */
    protected $max_body_size = 131072;

    /**
     * @var array A list of headers to be included in the signature
     */
    protected $headers_to_sign = array();

    /**
     * Create the Authentication header
     * @return string
     * @throws \Akamai\Open\EdgeGrid\Authentication\Exception\SignerException\InvalidSignDataException
     * @link https://developer.akamai.com/introduction/Client_Auth.html
     */
    public function createAuthHeader()
    {
        if ($this->timestamp === null) {
            $this->setTimestamp();
        }

        if (!$this->timestamp->isValid()) {
            throw new InvalidSignDataException('Timestamp is invalid. Too old?');
        }

        if ($this->nonce === null) {
            $this->nonce = new Nonce();
        }

        $auth_header =
            'EG1-HMAC-SHA256 ' .
            'client_token=' . $this->auth['client_token'] . ';' .
            'access_token=' . $this->auth['access_token'] . ';' .
            'timestamp=' . $this->timestamp . ';' .
            'nonce=' . $this->nonce . ';';

        return $auth_header . 'signature=' . $this->signRequest($auth_header);
    }

    /**
     * Set request HTTP method
     *
     * @param string $method
     * @return Authentication
     */
    public function setHttpMethod($method)
    {
        $this->httpMethod = strtoupper($method);
        return $this;
    }

    /**
     * Get request HTTP method
     *
     * @return string
     */
    public function getHttpMethod()
    {
        return $this->httpMethod;
    }

    /**
     * Set request host
     *
     * @param mixed $host
     * @return Authentication
     */
    public function setHost($host)
    {
        $this->host = $host;
        if (strpos($host, '/') !== false || strpos($host, '?') !== false) {
            if (strpos($host, 'http') === false) {
                $host = 'https://' . $host;
            }
            $url = parse_url($host);
            $this->host = $url['host'];

            if (isset($url['path'])) {
                $this->setPath($url['path']);
            }

            if (isset($url['query'])) {
                if (!isset($url['path'])) { // for example.org?query=string
                    $this->setPath('/');
                }
                $this->setQuery($url['query']);
            }
        }

        return $this;
    }

    /**
     * Get the request host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set Guzzle config
     *
     * This is a convenient way to pass in the
     * body/query/headers options
     *
     * @param mixed $config
     * @return Authentication
     */
    public function setConfig(array $config)
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    /**
     * Get Authentication config array
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set GET args
     *
     * If setting to a string, you MUST encode using RFC3986
     * {@see http_build_query()}. When passing in a string,
     * this method will re-encode using RFC3986 unless you
     * explicitly pass in false as the second argument.
     *
     * @param array|string $query
     * @param bool $ensure_encoding
     * @return $this
     */
    public function setQuery($query, $ensure_encoding = true)
    {
        if (is_string($query) && $ensure_encoding) {
            $query_args = array();
            parse_str($query, $query_args);
            $query = $this->buildQueryString($query_args);
        }
        $this->config['query'] = $query;
        return $this;
    }

    /**
     * Get request query string
     *
     * The return value will match
     * the type passed in to setQuery().
     *
     * @return string|array
     */
    public function getQuery()
    {
        return isset($this->config['query']) ? $this->config['query'] : '';
    }

    /**
     * Set request body
     *
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->config['body'] = $body;
        return $this;
    }

    /**
     * Get request body
     *
     * @param bool $truncate
     * @return string
     */
    public function getBody($truncate = false)
    {
        if (!$truncate) {
            return isset($this->config['body']) ? $this->config['body'] : '';
        }

        return isset($this->config['body']) ? substr($this->config['body'], 0, $this->max_body_size) : '';
    }

    /**
     * Set request headers
     *
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->config['headers'] = $headers;
        return $this;
    }

    /**
     * Get request headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return isset($this->config['headers']) ? $this->config['headers'] : array();
    }

    /**
     * Set request path
     *
     * @param mixed $path
     * @return $this
     */
    public function setPath($path)
    {
        $url = parse_url($path);

        $this->path = $url['path'];
        if (isset($url['host'])) {
            $this->setHost($url['host']);
        }

        if (isset($url['query'])) {
            $this->setQuery($url['query']);
        }
        return $this;
    }

    /**
     * Get request path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set signing timestamp
     *
     * @param Timestamp|string $timestamp
     * @return $this
     */
    public function setTimestamp($timestamp = null)
    {
        $this->timestamp = $timestamp;
        if ($timestamp === null) {
            $this->timestamp = new Timestamp();
        }
        return $this;
    }

    /**
     * Set signing nonce
     *
     * @param Nonce|string $nonce
     * @return $this
     */
    public function setNonce($nonce = null)
    {
        $this->nonce = $nonce;
        if ($nonce === null) {
            $this->nonce = new Nonce();
        }
        return $this;
    }

    /**
     * Set headers to sign
     *
     * @param array $headers_to_sign
     * @return $this
     */
    public function setHeadersToSign($headers_to_sign)
    {
        $this->headers_to_sign = $headers_to_sign;
        return $this;
    }

    /**
     * Set max body size to sign
     *
     * @param int $max_body_size Size (in bytes)
     * @return $this
     */
    public function setMaxBodySize($max_body_size)
    {
        $this->max_body_size = trim($max_body_size);
        return $this;
    }

    /**
     * Set Akamai EdgeGrid Authentication Tokens/Secret
     *
     * @param string $client_token
     * @param string $client_secret
     * @param string $access_token
     * @return $this
     */
    public function setAuth($client_token, $client_secret, $access_token)
    {
        $this->auth = array(
            'client_token' => trim($client_token),
            'client_secret' => trim($client_secret),
            'access_token' => trim($access_token),
        );
        return $this;
    }

    /**
     * Create instance using environment (prefered) or .edgerc file (fallback)
     * automatically.
     *
     * This method will check in order:
     * - AKAMAI_{SECTION}_* environment variables
     * - if using the "default" section, AKAMAI_* environment variables
     * - the specified (or "default" if none) section in .edgerc
     * - if not using the "default" section, AKAMAI_* environment variables
     *
     * @param string $section
     * @param null $path
     * @return Authentication
     * @throws \Akamai\Open\EdgeGrid\Authentication\Exception\ConfigException
     */
    public static function createInstance($section = 'default', $path = null)
    {
        $previousError = null;

        if (isset($_ENV['AKAMAI_' .strtoupper($section). '_HOST'])
            || (isset($_ENV['AKAMAI_HOST']) && $section === 'default')) {
            try {
                return self::createFromEnv($section);
            } catch (ConfigException $previousError) {
            }
        }

        try {
            return self::createFromEdgeRcFile($section, $path);
        } catch (ConfigException $previousError) {
            try {
                if (isset($_ENV['AKAMAI_HOST']) && $section !== 'default') {
                    return self::createFromEnv();
                }
            } catch (ConfigException $previousError) {
                // fall through to the below throw
            }
        }

        throw new ConfigException('Unable to create instance using environment or .edgerc file', 0, $previousError);
    }

    /**
     * Create instance using environment variables
     *
     * @param string $section
     * @return Authentication
     * @throws \Akamai\Open\EdgeGrid\Authentication\Exception\ConfigException
     */
    public static function createFromEnv($section = 'default')
    {
        $section = strtoupper($section);

        $prefix = isset($_ENV['AKAMAI_' . $section . '_HOST']) ? 'AKAMAI_' . $section . '_' : 'AKAMAI_';

        $vars = array('HOST', 'CLIENT_TOKEN', 'CLIENT_SECRET', 'ACCESS_TOKEN');

        foreach ($vars as $var) {
            if (!isset($_ENV[$prefix . $var])) {
                throw new ConfigException(sprintf(
                    'Environment variable%s %sAKAMAI_%s_%s do%s not exist',
                    $section === 'DEFAULT' ? 's' : '',
                    $section === 'DEFAULT' ? 'AKAMAI_' . $var . ' or ' : '',
                    $section,
                    $var,
                    $section === 'DEFAULT' ? '' : 'es'
                ));
            }
        }

        $auth = new static();
        $auth->setAuth(
            $_ENV[$prefix . 'CLIENT_TOKEN'],
            $_ENV[$prefix . 'CLIENT_SECRET'],
            $_ENV[$prefix . 'ACCESS_TOKEN']
        );

        $auth->setHost($_ENV[$prefix . 'HOST']);

        if (isset($_ENV[$prefix . 'MAX_SIZE'])) {
            $auth->setMaxBodySize($_ENV[$prefix . 'MAX_SIZE']);
        }

        return $auth;
    }

    /**
     * Create instance using an .edgerc configuration file
     *
     * @param string $section
     * @param string|null $path
     *
     * @return Authentication
     * @throws \Akamai\Open\EdgeGrid\Authentication\Exception\ConfigException
     */
    public static function createFromEdgeRcFile($section = 'default', $path = null)
    {
        if ($section === null) {
            $section = 'default';
        }

        $ini = self::parseEdgeRcFile($path);

        if (!isset($ini[$section])) {
            throw new ConfigException("Section \"$section\" does not exist!");
        }

        $auth = new static();
        $auth->setAuth(
            $ini[$section]['client_token'],
            $ini[$section]['client_secret'],
            $ini[$section]['access_token']
        );

        if (isset($ini[$section]['host'])) {
            $auth->setHost($ini[$section]['host']);
        }

        if (isset($ini[$section]['max-size'])) {
            $auth->setMaxBodySize($ini[$section]['max-size']);
        }

        return $auth;
    }

    /**
     * Returns headers in normalized form
     *
     * @return string
     */
    protected function canonicalizeHeaders()
    {
        $canonical = array();
        $headers = array();
        if (isset($this->config['headers'])) {
            $headers = array_map('strtolower', array_keys($this->config['headers']));
            if (count($this->config['headers']) > 0) {
                $headers = array_combine(
                    $headers,
                    array_values($this->config['headers'])
                );
            }
        }

        foreach ($this->headers_to_sign as $key) {
            $key = strtolower($key);
            if (isset($headers[$key])) {
                if (is_array($headers[$key]) && count($headers[$key]) >= 1) {
                    $value = trim($headers[$key][0]);
                } elseif (is_array($headers[$key]) && count($headers[$key]) === 0) {
                    continue;
                } else {
                    $value = trim($headers[$key]);
                }

                if (!empty($value)) {
                    $canonical[$key] = preg_replace('/\s+/', ' ', $value);
                }
            }
        }

        ksort($canonical);
        $serialized_header = '';
        foreach ($canonical as $key => $value) {
            $serialized_header .= $key . ':' . $value . "\t";
        }

        return rtrim($serialized_header);
    }

    /**
     * Returns Base64 encoded HMAC-SHA256 Hash
     *
     * @param string $data
     * @param string $key
     * @return string
     */
    protected function makeBase64HmacSha256($data, $key)
    {
        return base64_encode(hash_hmac('sha256', (string) $data, $key, true));
    }

    /**
     * Returns Base64 encoded SHA256 Hash
     *
     * @param string $data
     * @return string
     */
    protected function makeBase64Sha256($data)
    {
        return base64_encode(hash('sha256', (string) $data, true));
    }

    /**
     * Returns a hash of the HTTP POST body
     *
     * @return string
     */
    protected function makeContentHash()
    {
        if (empty($this->config['body'])) {
            return '';
        } else {
            // Just substr, it'll return as much as it can
            return $this->makeBase64Sha256($this->getBody(true));
        }
    }

    /**
     * Returns a string with all data that will be signed
     *
     * @param string $auth_header
     * @return string
     */
    protected function makeDataToSign($auth_header)
    {
        $query = '';
        if (isset($this->config['query']) && $this->config['query']) {
            $query .= '?';
            if (is_string($this->config['query'])) {
                $query .= $this->config['query'];
            } else {
                $query .= $this->buildQueryString($this->config['query']);
            }
        }

        $data = array(
            strtoupper($this->httpMethod),
            'https',
            $this->host,
            $this->path . $query,
            $this->canonicalizeHeaders(),
            (strtoupper($this->httpMethod) === 'POST') ? $this->makeContentHash() : '',
            $auth_header
        );

        return implode("\t", $data);
    }

    /**
     * Creates a signing key based on the secret and timestamp
     *
     * @return string
     */
    protected function makeSigningKey()
    {
        return $this->makeBase64HmacSha256((string)$this->timestamp, $this->auth['client_secret']);
    }

    /**
     * Returns a signature of the given request, timestamp and auth_header
     *
     * @param string $auth_header
     * @return string
     */
    protected function signRequest($auth_header)
    {
        return $this->makeBase64HmacSha256(
            $this->makeDataToSign($auth_header),
            $this->makeSigningKey()
        );
    }

    /**
     * Parse a .edgerc File
     *
     * @param $path
     * @return array
     * @throws \Akamai\Open\EdgeGrid\Authentication\Exception\ConfigException
     */
    protected static function parseEdgeRcFile($path)
    {
        if ($path === null) {
            if (isset($_SERVER['HOME']) && file_exists($_SERVER['HOME'] . '/.edgerc')) {
                $path = $_SERVER['HOME'] . '/.edgerc';
            } elseif (file_exists('./.edgerc')) {
                $path = './.edgerc';
            }
        }

        $file = !$path ? false : realpath($path);
        if (!$file) {
            throw new ConfigException("Path to .edgerc file \"$path\" does not exist!");
        }

        if (!is_readable($file)) {
            throw new ConfigException('Unable to read .edgerc file!');
        }

        // Handle : assignments in .edgerc files
        $ini = file_get_contents($file);
        $ini = str_replace(':', '=', $ini);

        $ini = parse_ini_string($ini, true, INI_SCANNER_RAW);

        return $ini;
    }

    /**
     * @param $query
     *
     * @return string
     */
    protected function buildQueryString($query)
    {
        if (defined('PHP_QUERY_RFC3986')) {
            return http_build_query($query, null, '&', PHP_QUERY_RFC3986);
        }

        // @codeCoverageIgnoreStart
        return str_replace('+', '%20', http_build_query($query, null, '&'));
        // @codeCoverageIgnoreEnd
    }
}
