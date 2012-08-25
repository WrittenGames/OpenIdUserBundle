<?php

namespace WG\OpenIdUserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class WGOpenIdUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
