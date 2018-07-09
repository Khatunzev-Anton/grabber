<?php

namespace Services\Grab;

interface IGrabService{

    public function getUrl();

    public function parse($lookupElement);

    public function parseWebsite($lookupElement);
}
