<?php

namespace TrustSwiftly\Security;

use TrustSwiftly\Exceptions\RequiredParameterMissing;

class WebhookSignature
{
    /**
     * @param $receivedSignature
     * @param $content
     * @param $signatureSecret
     * @return bool
     * @throws RequiredParameterMissing
     */
    public function verifyWebhookSignature($receivedSignature,$content,$signatureSecret){
        if (empty($receivedSignature)){
            throw new RequiredParameterMissing("Required Parameter received_signature Missing");
        }
        if (empty($content)){
            throw new RequiredParameterMissing("Required Parameter input content Missing");
        }
        if (empty($signatureSecret)){
            throw new RequiredParameterMissing("Required Parameter signature secret Missing");
        }
        $localSignature=hash_hmac(
            'sha256',
            $content,
            $signatureSecret
        );

        return hash_equals($receivedSignature,$localSignature);
    }
}