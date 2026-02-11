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

    #[Requires(methods: [IRequest::Post, IRequest::Options])]
    public function actionAi(): never
    {
        $postData = $this->httpRequest->getRawBody();
        $decodedData = Json::decode($postData, true);
        $data = $decodedData['data'] ?? null;
        $url = 'http://ollama:11434/api/generate';
        $data = str_replace('\n', "\n", $data);
        $prompt = <<<'EOT'
You are a Git Commit Generator.
Rules:
1. If input is a valid git diff -> Output: <type>(<scope>): <description>
2. If input is NOT a diff (short text, random letters, words) -> Output ONLY: ERROR: Invalid diff

EXAMPLES:
Input: diff --git a/a.txt b/a.txt
--- a/a.txt
+++ b/a.txt
@@ -1 +1 @@
-old
+new
Output: feat(core): update text content

Input: hello
Output: ERROR: Invalid diff

Input: i
Output: ERROR: Invalid diff

Input: fixing the bug
Output: ERROR: Invalid diff

TASK:
Analyze the input below and generate output.

INPUT:
EOT;

        $fullPrompt = $prompt . "\n" . trim($data) . "\n\nOutput:";
        $postData = [
            'json' => [
                'model' => 'qwen2.5-coder:1.5b',
                'prompt' => $fullPrompt,
                'stream' => false,
                'options' => [
                    'temperature' => 0.0,
                    'stop' => ["[SYSTEM]", "[INPUT DIFF]", "diff --git"],
                ],
            ],
            'timeout' => 600,
        ];
        $res = $this->client->request(
            'POST',
            $url,
            $postData,
        );
        $this->sendJson(JSON::decode($res->getBody()->getContents()));
    }
}