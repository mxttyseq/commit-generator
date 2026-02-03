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

    #[Requires(methods: [IRequest::Post, IRequest::Options])] //TODO: post
    public function actionAi(): never
    {
        $postData = $this->httpRequest->getRawBody();
        $decodedData = Json::decode($postData, true);
        $data = $decodedData['data'] ?? null;
        $url = 'http://ollama:11434/api/generate';
        $prompt = 'STRICT INPUT VALIDATION:
Check the input text below. Does it look like a git diff?
A valid git diff must contain standard markers such as "diff --git", "index", "--- a/", "+++ b/", or hunk headers like "@@ -".
IF THE INPUT IS NOT A VALID GIT DIFF (e.g., it is random text, conversation, code without context, or empty):
>>> Output strictly the string "null" and nothing else.

If the input IS a valid diff, your task is to generate ONE LINE commit message strictly following the Conventional Commits 1.0.0 standard.

Rules for commit generation:
1. Output ONLY the commit message. No quotes, no markdown, no explanation.
2. Format: <type>(<scope>): <description>
3. Allowed types: feat, fix, refactor, chore, docs, test, perf, build, ci, style
4. Scope:
   - Must be a single short logical area (like api, frontend, auth, core)
   - NEVER include filenames, directories, or file extensions
   - Use "core" if there is no clear scope
5. Description:
   - Must be lowercase
   - Must be imperative mood (e.g., "add feature", "fix bug")
   - Must describe WHAT changed, not HOW
   - Maximum 72 characters

Decision rules based on the diff:
- If the change updates configuration files or endpoints only → type = chore
- If the change fixes incorrect behavior → type = fix
- If the change adds new functionality → type = feat
- If the change only renames or moves things → type = refactor
- If the change only affects documentation → type = docs
- For tests, use type = test
- For performance improvements → perf
- For code formatting/style → style
- For build/CI changes → build / ci

Input to analyze:
';
        $postData = [
            'json' => [
                'model' => 'qwen2.5:0.5b',
                'prompt' => $prompt . $data,
                'stream' => false,
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