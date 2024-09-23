<?php

namespace App\Service;

class OpenaiApiService
{
    public function prompt(string $prompt, $markdown): string
    {
        try {

            $client = HttpClient::create();
            $response = $client->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $_ENV['CHATGPT_API_KEY'],
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => $prompt
                                ],
                            ]
                        ]
                    ],
                    'max_tokens' => 1000
                ],
            ]);
    
            $data = $response->toArray();

            if ($markdown ) {
    
                $content = $this->markdownProcessor->processMarkdown($data['choices'][0]['message']['content']);
                
                return $content;
            }

            return $data;
    
    
        }
        catch (\Exception $e) {
    
            $email = (new TemplatedEmail())
            ->to($_ENV['MAILER_TO_WEBMASTER'])
            ->from($_ENV['MAILER_TO'])
            ->subject('Erreur lors de l\'envoie de l\'email')
            ->htmlTemplate('emails/error.html.twig')
            ->context([
                'error' => $e->getMessage(),
            ]);
            $mailer->send($email);
    
            return $this->json(
                [
                    "erreur" => "Error",
                    "code_error" => Response::HTTP_FORBIDDEN
                ],
                Response::HTTP_FORBIDDEN
            );
        }
    }
}
