<?php

namespace HardImpact\OpenCode\Resources;

use HardImpact\OpenCode\Data\Question;
use HardImpact\OpenCode\Requests\Questions\AnswerQuestion;
use HardImpact\OpenCode\Requests\Questions\ListQuestions;
use HardImpact\OpenCode\Requests\Questions\RejectQuestion;
use Saloon\Http\BaseResource;

class QuestionResource extends BaseResource
{
    /** @return Question[] */
    public function list(): array
    {
        return $this->connector->send(new ListQuestions)->dto();
    }

    public function answer(
        string $sessionId,
        string $permissionId,
        string $response = 'once',
        ?string $directory = null,
    ): bool {
        return $this->connector->send(
            new AnswerQuestion($sessionId, $permissionId, $response, $directory)
        )->successful();
    }

    public function reject(
        string $sessionId,
        string $permissionId,
        ?string $directory = null,
    ): bool {
        return $this->connector->send(
            new RejectQuestion($sessionId, $permissionId, $directory)
        )->successful();
    }
}
