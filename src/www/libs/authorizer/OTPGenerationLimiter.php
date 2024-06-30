<?php

namespace libs\authorizer;

class OTPGenerationLimiter extends AThrottleLimiter
{

    public function HasReachedLimit(): bool
    {
        // TODO: Implement HasReachedLimit() method.
        return true;
    }
}