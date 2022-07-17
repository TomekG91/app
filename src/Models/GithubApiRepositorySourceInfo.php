<?php

namespace App\Models;

class GithubApiRepositorySourceInfo
{   
    public $fullName;
    public $url;

    public function __construct($githubApiForkInfoContent)
    {
        $this->fullName = ($githubApiForkInfoContent['source']['full_name']);
        $this->url = ($githubApiForkInfoContent['source']['html_url']);
    }
}
