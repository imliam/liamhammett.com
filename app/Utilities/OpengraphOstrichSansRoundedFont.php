<?php

namespace App\Utilities;

use SimonHamp\TheOg\Interfaces\Font;

class OpengraphOstrichSansRoundedFont implements Font
{
    public function path(): string
    {
        return public_path('fonts/ostrich-sans-rounded.ttf');
    }
}
