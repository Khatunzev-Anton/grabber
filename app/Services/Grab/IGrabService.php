<?php

namespace Services\Grab;

interface IGrabService{

    public function getUrl();

    public function parse($relativeUrl);
}
