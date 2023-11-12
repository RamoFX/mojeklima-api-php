<?php



namespace App\Resources\Suggestion {

  use App\Resources\Suggestion\DTO\SuggestionInput;
  use RestClientException;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Query;



  readonly class SuggestionController {
    public function __construct(
      protected SuggestionService $suggestionService
    ) {}



    /**
     * @return SuggestionEntity[]
     * @throws RestClientException
     */
    #[Query]
    #[Logged]
    public function suggestions(SuggestionInput $suggestion): array {
      return $this->suggestionService->suggestions($suggestion);
    }
  }
}
