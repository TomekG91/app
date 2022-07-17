<?php

namespace App\Service;

use App\Models\GithubApiRepositorySourceInfo;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Models\GithubRepositoryInfo;
use Exception;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class RepositoryService
{
    private $client;
    private $githubApiToken;

    public function __construct(HttpClientInterface $client, $githubApiToken)
    {
        $this->client = $client;
        $this->githubApiToken = $githubApiToken;
    }

    public function getRepositoriesInfo($organizationName)
    {
        $response = $this->client->request(
            'GET',
            sprintf('https://api.github.com/orgs/%s/repos', $organizationName),
            ["auth_bearer" => $this->githubApiToken]
        );

        try {
            $responseContent = $response->getContent();
        } catch (Exception $e) {
            return $this->handleGithubApiResponseError($response);
        }

        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer(), new ArrayDenormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $githubApiRepositories = $serializer->deserialize($responseContent, 'App\Models\GithubApiRepository[]', 'json');

        $repositoriesInfo = [];

        foreach ($githubApiRepositories as $githubApiRepository) {
            $repositoryInfo = new GithubRepositoryInfo();

            $repositoryInfo->name = $githubApiRepository->name;
            $repositoryInfo->url = $githubApiRepository->html_url;
            $repositoryInfo->isFork = $githubApiRepository->fork;


            if ($githubApiRepository->fork) {
                $response = $this->client->request(
                    'GET',
                    $githubApiRepository->url,
                    ["auth_bearer" => $this->githubApiToken]
                );

                try {
                    $githubApiForkInfoContent = $response->toArray();
                } catch (Exception $e) {
                    return  $this->handleGithubApiResponseError($response);
                }

                $githubApiRepositorySourceInfo = new GithubApiRepositorySourceInfo($githubApiForkInfoContent);

                $repositoryInfo->sourceName = $githubApiRepositorySourceInfo->fullName;
                $repositoryInfo->sourceUrl = $githubApiRepositorySourceInfo->url;
            }

            $response = $this->client->request(
                'GET',
                $githubApiRepository->contributors_url . "?anon=1&per_page=1",
                ["auth_bearer" => $this->githubApiToken]
            );

            try {
                $responseContent = $response->getContent();
            } catch (Exception $e) {
                return $this->handleGithubApiResponseError($response);
            }

            $headerLinkValue = $this->getValueFromHeaders('link', $response->getHeaders());

            if ($headerLinkValue) {
                try {
                    $contributorsNumber = $this->getLastPageNumberFromLinkHeader($headerLinkValue);
                } catch (\UnexpectedValueException $e) {
                    return [
                        "statusCode" => 500,
                        "data" => [
                            "messageError" => "Blad serwera"
                        ]
                    ];
                }
                $repositoryInfo->contributorsNumber = $contributorsNumber;
            } elseif ($response->getStatusCode() == 204) {
                $repositoryInfo->contributorsNumber = 0;
            } else {
                $repositoryInfo->contributorsNumber = 1;
            }

            $repositoriesInfo[] = $repositoryInfo;
        }

        return [
            "statusCode" => 200,
            "data" => [
                "repositoriesInfo" => $repositoriesInfo
            ]
        ];
    }

    private function handleGithubApiResponseError($response)
    {
        $message = "";
        $statusCode = $response->getStatusCode();
        switch ($statusCode) {
            case 401:
                $message = "Waznosc tokena wygasla";
                break;
            case 403:
                $headerValue = $this->getValueFromHeaders('x-ratelimit-reset', $response->getHeaders(false));
                $githubApiBlockExpirationTime = date("d/m H:m:s", (int)$headerValue);
                $message = sprintf("Ilosc zapytan przekroczona, blokada potrwa do %s", $githubApiBlockExpirationTime);
                break;
            default:
                $message = "Problem z serwerem, sprobuj ponownie";
                break;
        }

        return [
            "statusCode" => $statusCode,
            "data" => [
                "errorMessage" => $message
            ]
        ];
    }

    private function getValueFromHeaders($headerName, $headers)
    {
        return array_key_exists($headerName, $headers) ? $headers[$headerName][0] : null;
    }

    private function getLastPageNumberFromLinkHeader($linkHeader)
    {
        preg_match('/.*&page=(\d*).*rel="last"/', $linkHeader, $match);
        if (!isset($match[1])) {
            throw  new \UnexpectedValueException("Unexpected format of link header");
        }
        return (int)$match[1];
    }
}
