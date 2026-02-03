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
        $prompt = <<<'EOT'
ROLE:
You are a strict, automated Git Commit Message Generator. You are NOT a chat assistant. You do not converse. Your only job is to output a raw commit message based on the provided git diff.

TASK:
Analyze the code changes and generate a commit message strictly following the "Conventional Commits" specification.

STRICT RULES:
1. Format: `<type>(<optional-scope>): <description>`
2. The `description` must be in the IMPERATIVE mood (e.g., "add feature", NOT "added feature").
3. Do NOT end the description with a period.
4. Keep the first line (header) under 50 characters if possible, never over 72.
5. If the changes are complex, add a blank line after the header, followed by a bulleted body explaining "what" and "why" (not "how").
6. Do NOT output markdown code blocks. Output raw text only.
7. Do NOT include any conversational text.

ALLOWED TYPES:
- feat: A new feature
- fix: A bug fix
- docs: Documentation only changes
- style: Changes that do not affect the meaning of the code (white-space, formatting, etc)
- refactor: A code change that neither fixes a bug nor adds a feature
- perf: A code change that improves performance
- test: Adding missing tests or correcting existing tests
- build: Changes that affect the build system or external dependencies
- ci: Changes to our CI configuration files and scripts
- chore: Other changes that don't modify src or test files

EXAMPLE INPUT:
diff --git a/app.py b/app.py
index 83f1..02a1 100644
--- a/app.py
+++ b/app.py
@@ -10,7 +10,7 @@ def login():
-    if user.password == password:
+    if check_password_hash(user.password, password):

EXAMPLE OUTPUT:
fix(auth): use secure password hash verification

Using simple string comparison for passwords created a vulnerability. Replaced with check_password_hash.

INSTRUCTIONS:
Generate the commit message for the following diff. Output NOTHING else.

DIFF:
EOT;
        $postData = [
            'json' => [
                'model' => 'qwen2.5-coder:1.5b',
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