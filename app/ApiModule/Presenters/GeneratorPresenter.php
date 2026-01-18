<?php

declare(strict_types=1);

namespace App\ApiModule\Presenters;

use DateTimeZone;
use GuzzleHttp\Client;
use Nette\Application\Attributes\Requires;
use Nette\Application\UI\Presenter;
use Nette\DI\Attributes\Inject;
use Nette\Http\IRequest;
use Nette\Utils\DateTime;
use Nette\Http\Request;
use Nette\Utils\Json;

class GeneratorPresenter extends Presenter
{
    #[Inject]
    public Request $httpRequest;

    #[Inject]
    public Client $client;

    #[Requires(methods: IRequest::Get)]
    public function actionPing(): never
    {
        $this->sendJson(
            [
                'data' => [
                    'message' => 'Pong!',
                ],
                'code' => 200,
            ],
        );
    }

    #[Requires(methods: IRequest::Get)]
    public function actionStatus(): never
    {
        $this->sendJson(
            [
                'data' => [
                    'time' => DateTime::from('now')->setTimezone(new DateTimeZone('Europe/Prague')),
                    'author' => 'Matěj Bureš',
                ],
                'code' => 200,
            ],
        );
    }

    #[Requires(methods: IRequest::Get)] //TODO: post
    public function actionAi(): never
    {
        /*$postData = $this->httpRequest->getRawBody();
        $decodedData = Json::decode($postData, true);
        $data = $decodedData['data'] ?? null;*/
        $url = 'http://ollama:11434/api/version';
        $res = $this->client->request('GET', $url, []);
        $this->sendJson( //TODO:
            [
                'data' => $res->getBody()->getContents(),
            ],
        );
    }
}