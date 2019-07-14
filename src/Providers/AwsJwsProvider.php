<?php

namespace GabyQuiles\Auth\Providers;


use GabyQuiles\Auth\Loaders\JwkKeyLoader;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\RawKeyLoader;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\LoadedJWS;

//TODO: Move to its own bundle
class AwsJwsProvider implements JWSProviderInterface
{

    /**
     * @var RawKeyLoader
     */
    private $keyLoader;

    /**
     * @var Signer
     */
    private $signer;

    /**
     * @var int
     */
    private $ttl;

    /**
     * @var int
     */
    private $clockSkew;

    /**
     * @param RawKeyLoader $keyLoader
     * @param string $cryptoEngine
     * @param string $signatureAlgorithm
     * @param int|null $ttl
     * @param int $clockSkew
     *
     * @throws \InvalidArgumentException If the given crypto engine is not supported
     */
    public function __construct(JwkKeyLoader $keyLoader, $ttl, $clockSkew)
    {

        if (null !== $ttl && !is_numeric($ttl)) {
            throw new \InvalidArgumentException(sprintf('The TTL should be a numeric value, got %s instead.', $ttl));
        }

        if (null !== $clockSkew && !is_numeric($clockSkew)) {
            throw new \InvalidArgumentException(sprintf('The clock skew should be a numeric value, got %s instead.', $clockSkew));
        }

        $this->keyLoader = $keyLoader;
        $this->ttl = $ttl;
        $this->clockSkew = $clockSkew;
    }

    /**
     * Creates a new JWS signature from a given payload.
     *
     * @param array $payload
     * @param array $header
     *
     * @return \Lexik\Bundle\JWTAuthenticationBundle\Signature\CreatedJWS
     */
    public function create(array $payload, array $header = [])
    {
        // TODO: Implement create() method.
//        Not Need to implement
    }

    /**
     * Loads an existing JWS signature from a given JWT token.
     *
     * @param string $token
     *
     * @return \Lexik\Bundle\JWTAuthenticationBundle\Signature\LoadedJWS
     */
    public function load($token)
    {
        $jws = (new Parser())->parse((string)$token);

        $payload = [];
        foreach ($jws->getClaims() as $claim) {
            $payload[$claim->getName()] = $claim->getValue();
        }

        return new LoadedJWS($payload, $this->verify($jws), null !== $this->ttl, $jws->getHeaders(), $this->clockSkew);
    }


    private function verify(Token $jwt)
    {
        if (!$jwt->validate(new ValidationData(time() + $this->clockSkew))) {
            return false;
        }

        $this->signer = $this->getSignerForAlgorithm($jwt->getHeader('alg'));
        return $jwt->verify($this->signer, $this->keyLoader->loadKey($jwt->getHeader('kid')));
    }


    private function getSignerForAlgorithm($signatureAlgorithm)
    {
        $signerMap = [
            'HS256' => Signer\Hmac\Sha256::class,
            'HS384' => Signer\Hmac\Sha384::class,
            'HS512' => Signer\Hmac\Sha512::class,
            'RS256' => Signer\Rsa\Sha256::class,
            'RS384' => Signer\Rsa\Sha384::class,
            'RS512' => Signer\Rsa\Sha512::class,
            'EC256' => Signer\Ecdsa\Sha256::class,
            'EC384' => Signer\Ecdsa\Sha384::class,
            'EC512' => Signer\Ecdsa\Sha512::class,
        ];

        if (!isset($signerMap[$signatureAlgorithm])) {
            throw new \InvalidArgumentException(
                sprintf('The algorithm "%s" is not supported by %s', $signatureAlgorithm, __CLASS__)
            );
        }

        $signerClass = $signerMap[$signatureAlgorithm];

        return new $signerClass();
    }
}