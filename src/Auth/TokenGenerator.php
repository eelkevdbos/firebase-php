<?php namespace Firebase\Auth;

use JWT;
use DateTime;
use Firebase\Exception\MissingEncoderException;

class TokenGenerator
{

    /**
     * List of available claim(types)
     *
     * @var array
     */
    protected $availableClaims = array('data', 'notBefore', 'expires', 'debug', 'admin');

    /**
     * List of required claim(types)
     *
     * @var array
     */
    protected $requiredClaims = array('data', 'issuedAt', 'version');

    /**
     * Generator version number
     *
     * @var int
     */
    protected $version = 0;

    /**
     * Firebase secret
     *
     * @var string
     */
    protected $secret;

    /**
     * Supply optional encoder over default JWT
     *
     * @var mixed
     */
    protected $encoder;

    /**
     * Initialize the generator with a firebase secret
     *
     * @param string $secret
     * @param object|null $encoder
     */
    public function __construct($secret, $encoder = null)
    {
        $this->secret = $secret;
        $this->encoder = $encoder;
    }

    /**
     * Generate a JWT token by specifying data and options
     * @param array|null $data
     * @param array $options
     * @return string
     */
    public function generateToken($data, $options = array())
    {
        return $this->encodeToken(
            $this->buildClaims($options + array('data' => $data)),
            $this->secret
        );
    }

    /**
     * Encodes token using a JWT encoder
     * @param array|null $claims
     * @param string $secret
     * @param string $hashMethod
     * @return string
     * @throws \Firebase\Exception\MissingEncoderException
     */
    protected function encodeToken($claims, $secret, $hashMethod = 'HS256')
    {
        //ductyping alternative encoder
        if (!is_null($this->encoder) && method_exists($this->encoder, 'encode')) {
            return $this->encoder->encode($claims, $secret, $hashMethod);
        } else if (method_exists('JWT', 'encode')) {
            return JWT::encode($claims, $secret, $hashMethod);
        } else {
            throw new MissingEncoderException('No JSON Web Token encoder could be found');
        }
    }

    protected function buildClaims($options)
    {
        $claims = array();

        foreach ($this->requiredClaims as $claimKey) {
            list($key, $claim) = $this->buildClaim($claimKey);
            $claims[$key] = $claim;
        }

        foreach (array_intersect($this->availableClaims, array_keys($options)) as $claimKey) {
            list($key, $claim) = $this->buildClaim($claimKey, $options[$claimKey]);
            $claims[$key] = $claim;
        }

        return $claims;
    }

    protected function buildClaim($key, $arg = null)
    {
        $claimBuilder = sprintf('build%sClaim', ucfirst($key));
        return $this->{$claimBuilder}($arg);
    }

    protected function buildNotBeforeClaim($value)
    {
        return array('nbf', $this->getValidTimestamp($value));
    }

    protected function buildExpiresClaim($value)
    {
        return array('exp', $this->getValidTimestamp($value));
    }

    protected function buildDebugClaim($value)
    {
        return array('debug', (bool)$value);
    }

    protected function buildAdminClaim($value)
    {
        return array('admin', (bool)$value);
    }

    protected function buildVersionClaim($value = null)
    {
        return array('v', $value ? : $this->version);
    }

    protected function buildIssuedAtClaim($value = null)
    {
        return array('iat', $value ? : time());
    }

    protected function getValidTimestamp($value)
    {
        switch (gettype($value)) {
            case 'integer':
                return $value;
            case 'object':
            default:
                if ($value instanceof DateTime) {
                    return $value->getTimestamp();
                } else {
                    throw new \UnexpectedValueException('Instance of DateTime required for a valid timestamp');
                }
        }
    }

    /**
     * Tests if data supplied is JSONifiable
     * @param $value
     * @return array
     * @throws \UnexpectedValueException
     */
    protected function buildDataClaim($value)
    {
        json_encode($value);

        if (function_exists('json_last_error') && $errorNumber = json_last_error()) {
            throw new \UnexpectedValueException($this->jsonErrorMessage($errorNumber));
        }

        return array('d', $value);
    }

    /**
     * Returns an error message
     * @param integer $errorNumber
     * @return string
     */
    protected function jsonErrorMessage($errorNumber)
    {
        $messages = array(
            JSON_ERROR_NONE => 'No error has occured',
            JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
            JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
            JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
            JSON_ERROR_SYNTAX => 'Syntax error',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
        );
        return isset($messages[$errorNumber]) ? $messages[$errorNumber] : 'Unknown JSON encoding error';
    }
} 