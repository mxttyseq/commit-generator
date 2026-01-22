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
        $url = 'http://ollama:11434/api/generate';
        $diff = <<<'EOD'
diff --git a/app/ApiModule/Presenters/GeneratorPresenter.php b/app/ApiModule/Presenters/GeneratorPresenter.php
index bd322f7..e1726a0 100644
--- a/app/ApiModule/Presenters/GeneratorPresenter.php
+++ b/app/ApiModule/Presenters/GeneratorPresenter.php
@@ -55,8 +55,20 @@ class GeneratorPresenter extends Presenter
         /*$postData = $this->httpRequest->getRawBody();
         $decodedData = Json::decode($postData, true);
         $data = $decodedData['data'] ?? null;*/
-        $url = 'http://ollama:11434/api/version';
-        $res = $this->client->request('GET', $url, []);
+        $url = 'http://ollama:11434/api/generate';
+        $postData = [
+            'json' => [
+                'model' => 'llama3.2:3b',
+                'prompt' => 'Kdo je Mao Zedong? Stručně v málo větách.',
+                'stream' => false,
+            ],
+            'timeout' => 100,
+        ];
+        $res = $this->client->request(
+            'POST',
+            $url,
+            $postData,
+        );
         $this->sendJson( //TODO:
             [
                 'data' => $res->getBody()->getContents(),
diff --git a/app/Bootstrap.php b/app/Bootstrap.php
index 6d6753f..97263be 100644
--- a/app/Bootstrap.php
+++ b/app/Bootstrap.php
@@ -32,7 +32,7 @@ class Bootstrap
 
        public function initializeEnvironment(): void
        {
-               $this->configurator->setDebugMode(false); // enable for your remote IP
+               $this->configurator->setDebugMode(true); // enable for your remote IP
                $this->configurator->enableTracy($this->rootDir . '/log');
 
                $this->configurator->createRobotLoader()
EOD;
        $postData = [
            'json' => [
                'model' => 'llama3.2:3b',
                'prompt' => 'You are an AI that generates **only one git commit message** following the Conventional Commits standard. 

Rules:
1. Use a **logical scope** (module or feature name), **not file paths**. Examples: generator, api, bootstrap.
2. Use only valid Conventional Commit types: feat, fix, chore, docs, refactor, test, style, perf.
3. Keep the summary line 50 characters or less.
4. **Do not include anything else**: no JSON, no code, no quotes, no explanations.
5. Base the commit message strictly on the following git diff.
' . $diff,
                'stream' => false,
            ],
            'timeout' => 300,
        ];
        $res = $this->client->request(
            'POST',
            $url,
            $postData,
        );
        $this->sendJson( //TODO:
            [
                'data' => $res->getBody()->getContents(),
            ],
        );
    }
}